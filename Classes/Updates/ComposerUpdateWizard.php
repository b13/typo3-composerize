<?php

namespace B13\Typo3Composerize;

use Composer\Autoload\ClassMapGenerator;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Composer\Plugin\Util\Filesystem;
use \Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Utility\EmConfUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use TYPO3\CMS\PackageManager\Service\ComposerService;

final class ComposerUpdateWizard implements ChattyInterface, UpgradeWizardInterface
{
    /**
     * @var OutputInterface
     */
    protected OutputInterface $output;

    /**
     * @var array
     */
    protected array $classMap;

    /**
     * @var string
     */
    protected string $extBasePath;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var Filesystem */
    private $filesystem;

    public function __construct()
    {
        $this->requestFactory = GeneralUtility::makeInstance(RequestFactoryInterface::class);
        $this->filesystem = GeneralUtility::makeInstance(Filesystem::class);
        $this->extBasePath = Environment::getExtensionsPath();
        $this->classMap = ClassMapGenerator::createMap($this->extBasePath);
    }

    public function getIdentifier(): string
    {
        return 'composerUpdateWizard';
    }

    public function getTitle(): string
    {
        return 'Create composer.json';
    }

    public function getDescription(): string
    {
        return 'Creates a composer.json file for each extension if it does not already exist';
    }

    public function executeUpdate(): bool
    {
        $composerService = new ComposerService();
        $composerService->prepareComposer();
        $composerService->addRepository('typo3-local');

        $finder = Finder::create();
        $finder->directories()->depth(0)->in($this->extBasePath);
        $fs = new SymfonyFilesystem();

        if($finder->hasResults()) {
            foreach ($finder as $folder) {
                $extPath = $folder->getPathname();
                $extKey = $folder->getBasename();

//                TODO: Disable just for now - in metal dev mode
                if($fs->exists($extPath . '/composer.json')) {
                    continue;
                }

                $emConf = EmConfUtility::includeEmConf($folder->getBasename(), $folder->getPathname());

                $constraints = ['depends', 'suggests', 'conflicts'];
                foreach ($constraints as $constraint) {
                    unset($$constraint);
                    if(!empty($emConf['constraints'][$constraint])) {
                        foreach ($emConf['constraints'][$constraint] as $key => $version) {
                            list($key, $version) = $this->convertConstraint($key, $version);
                            $$constraint[$key] = $version;
                        }
                    }
                }
                $packageName = $this->convertToPackageName($extKey);
                $composerJson = [
                    "name" => $packageName,
                    "description" => $emConf['title'] . ' - ' . $emConf['description'],
                    "license" => "GPL-2.0-or-later",
                    "type" => "typo3-cms-extension",
                    "authors" => [
                        [
                            "name" => $emConf['author'],
                            "email" => $emConf['author_email'],
                        ]
                    ],
                    "require" => $depends ?? (object) null,
                    "suggest" => $suggests ?? (object) null,
                    "conflict" => $conflicts ?? (object) null,
                    "extra" => [
                        "typo3/cms" => [
                            "extension-key" => $extKey,
                        ]
                    ],
                    "version" => "dev-local",
                    "autoload" => [
                        'classmap' => $this->getExtensionClassMap($extPath),
                    ]
                ];

                $fs->dumpFile($extPath . DIRECTORY_SEPARATOR . 'composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                $fs->rename($extPath, 'typo3-local/' . $extKey);
                var_dump($packageName);
                $composerService->require([$packageName . ':@dev']);
            }
        }

//        $this->output->writeln('Ext path ' . $extPath);

        return 0;
    }

    /**
     * @param $extPath
     * @return false|string
     */
    public function getExtensionClassMap($extPath): array {
        $classes = preg_grep('/^' . preg_quote($extPath, '/') . '/', $this->classMap);

        $extClasses = [];
        foreach ($classes as $class) {
            $extClasses[] = $this->filesystem->findShortestPath(
                $extPath,
                $class
            );
        }

        return $extClasses;
    }

    public function convertToPackageName($extKey): string {
        return 'typo3-local/' . str_replace('_', '-', $extKey);
    }

    public function convertConstraint($key, $versions) {
        $packageName = $key === 'typo3' ? 'typo3/cms-core' : $this->getPackageName($key);

        $constraint = [];
        foreach (explode('-', $versions) as $version) {
            $explodedVersion = explode('.', $version);
            $constraint[$explodedVersion[0]] = '^' . $explodedVersion[0];
        }
       return [ $packageName, implode(' || ', $constraint)];
    }

    /**
     * Return Packagename if found on packagist.org
     *
     * @param $search
     * @return false|mixed
     */
    public function getPackageName($search) {
        $url = 'https://packagist.org/search.json?q='  . $search . '&type=typo3-cms-extension';
        $response = $this->requestFactory->request($url, 'GET');
        if ($response->getStatusCode() === 200) {
            $content = $response->getBody()->getContents();
            if(empty(json_decode($content, true)['results'][0]['name'])) {
                return false;
            }
            return json_decode($content, true)['results'][0]['name'];
        }

        return false;
    }

    public function updateNecessary(): bool
    {
        return 1;
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }
}
