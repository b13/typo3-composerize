<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Utilities;

use Composer\Autoload\ClassMapGenerator;
use Composer\Util\Filesystem;
use Symfony\Component\Finder\Finder;

class ComposerConvertUtility
{
    // TODO: Due to performance issues not used atm, loading only a local file see $this->terComposerMap
    //const TER_URL = 'https://extensions.typo3.org/index.php?eID=ter_fe2:extension&action=findAllWithValidComposerName';

    const CORE_EXTENSIONS = [
        'php' => 'php',
        'typo3' => 'typo3/cms-core',
        'extbase' => 'typo3/cms-extbase',
        'belog' => 'typo3/cms-belog',
        'form' => 'typo3/cms-form',
        'install' => 'typo3/cms-install',
        'core' => 'typo3/cms-core',
        'frontend' => 'typo3/cms-frontend',
        'felogin' => 'typo3/cms-felogin',
        'setup' => 'typo3/cms-setup',
        'impexp' => 'typo3/cms-impexp',
        'fluid_styled_content' => 'typo3/cms-fluid-styled-content',
        'backend' => 'typo3/cms-backend',
        'fluid' => 'typo3/cms-fluid',
        'tstemplate' => 'typo3/cms-tstemplate',
        'info' => 'typo3/cms-info',
        'dashboard' => 'typo3/cms-dashboard',
        'extensionmanager' => 'typo3/cms-extensionmanager',
        'filelist' => 'typo3/cms-filelist',
        't3editor' => 'typo3/cms-t3editor',
        'lowlevel' => 'typo3/cms-lowlevel',
        'beuser' => 'typo3/cms-beuser',
        'rte_ckeditor' => 'typo3/cms-rte-ckeditor',
        'seo' => 'typo3/cms-seo',
        'viewpage' => 'typo3/cms-viewpage',
        'sys_note' => 'typo3/cms-sys-note',
        'recordlist' => 'typo3/cms-recordlist',
        'workspaces' => 'typo3/cms-workspaces',
        'adminpanel' => 'typo3/cms-adminpanel',
        'filemetadata' => 'typo3/cms-filemetadata',
        'indexed_search' => 'typo3/cms-indexed-search',
        'linkvalidator' => 'typo3/cms-linkvalidator',
        'opendocs' => 'typo3/cms-opendocs',
        'recycler' => 'typo3/cms-recycler',
        'redirects' => 'typo3/cms-redirects',
        'reports' => 'typo3/cms-reports',
        'scheduler' => 'typo3/cms-scheduler',
    ];

    protected array $terComposerMap = [];
    protected string $docRoot;
    protected array $folders;

    protected Filesystem $filesystem;

    public function __construct(string $docRoot, $folders = ['typo3conf/ext/', 'typo3/sysext/'])
    {
        $this->terComposerMap = json_decode(file_get_contents(__DIR__ . '/../../Static/typo3-ter-composer-map.json'), true);
        $this->docRoot = $docRoot;
        $this->folders = $folders;
        $this->filesystem = new Filesystem();
    }

    /**
     * Validate extensions for composer compatibility
     *
     * @param array $checkExtensions
     * @param string[] $folders
     * @return array
     */
    public function validateExtensions(array $checkExtensions): array
    {
        $allExtensions = $this->getExtensions();

        $extensions = [];
        if ($allExtensions->hasResults()) {
            foreach ($allExtensions as $folder) {
                if (!empty($checkExtensions) && !in_array($folder->getFilename(), $checkExtensions)) {
                    continue;
                }

                $composerFinder = Finder::create();
                $composerFinder->files()->depth(0)->in($folder->getPathname())->name('composer.json');
                $folderName = $folder->getFilename();

                $composerPresent = $composerFinder->hasResults();
                $extensionKey = false;
                $packageName = false;

                foreach ($composerFinder as $composerJson) {
                    $json = json_decode($composerJson->getContents(), true);
                    if (!empty($json['extra']['typo3/cms']['extension-key'])) {
                        $extensionKey = $json['extra']['typo3/cms']['extension-key'];
                    } else {
                        $extensionKey = false;
                    }

                    $packageName = !empty($json['name']) ? $json['name'] : false;
                }

                $extensions[] = [
                    'ext-key' => $folderName,
                    'path' => $folder->getPathname(),
                    'composer-json' => $composerPresent,
                    'extra-extension-key' => $extensionKey,
                    'package-name' => $packageName,
                ];
            }
        }

        return $extensions;
    }

    /**
     * @param string $extPath
     * @param string $resultFilename
     * @return string
     */
    public function convertEmconfToComposer(string $extPath, $resultFilename = 'composer.json'): string
    {
        $extKey = basename($extPath);
        $emConf = $this->loadEmConf($extKey, $extPath);

        $constraints = ['depends', 'suggests', 'conflicts'];
        foreach ($constraints as $constraint) {
            unset($$constraint);
            if (!empty($emConf['constraints'][$constraint])) {
                foreach ($emConf['constraints'][$constraint] as $key => $version) {
                    list($currentKey, $currentVersion) = $this->convertConstraint($key, $version);
                    $$constraint[$currentKey] = $currentVersion;
                }
            }
        }

        $packageName = $this->getPackageName($extKey);
        $composerJson = [
            'name' => $packageName,
            'description' => $emConf['title'] . ' - ' . $emConf['description'],
            'license' => 'GPL-2.0-or-later',
            'type' => 'typo3-cms-extension',
            'authors' => [
                [
                    'name' => $emConf['author'] ?? '',
                    'email' => $emConf['author_email'] ?? 'no-email@given.com',
                ]
            ],
            'require' => $depends ?? (object)null,
            'suggest' => $suggests ?? (object)null,
            'conflict' => $conflicts ?? (object)null,
            'extra' => [
                'typo3/cms' => [
                    'extension-key' => $extKey,
                ]
            ],
            'version' => 'dev-local',
            'autoload' => [
                'classmap' => $this->getExtensionClassMap($extPath),
            ]
        ];

        $this->filesystem->filePutContentsIfModified($extPath . '/' . $resultFilename, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $extPath . '/' . $resultFilename;
    }

    /**
     * Returns $EM_CONF array
     *
     * @param string $extensionKey
     * @param string $absolutePath
     * @return array|false
     */
    public function loadEmConf(string $extensionKey, string $absolutePath)
    {
        $_EXTKEY = $extensionKey;
        $path = rtrim($absolutePath, '/') . '/ext_emconf.php';
        $EM_CONF = null;
        if (!empty($absolutePath) && file_exists($path)) {
            include $path;
            if (is_array($EM_CONF[$_EXTKEY])) {
                return $EM_CONF[$_EXTKEY];
            }
        }
        return false;
    }

    /**
     * Return Packagename
     *
     * @param string $extKey
     * @return false|mixed
     */
    public function getPackageName(string $extKey)
    {
        if (!empty($this->terComposerMap['data'][$extKey]['composer_name'])) {
            return $this->terComposerMap['data'][$extKey]['composer_name'];
        }

        if (!empty(self::CORE_EXTENSIONS[$extKey])) {
            return self::CORE_EXTENSIONS[$extKey];
        }

        return self::convertToPackageName($extKey);
    }

    /**
     * @param string $extKey
     * @param string $versions
     * @return array
     */
    public function convertConstraint(string $extKey, string $versions): array
    {
        $packageName = $this->getPackageName($extKey);

        // Set * if package is empty or a local package
        if (empty($versions) || preg_match('/^typo3-local\/(.*)/', $packageName)) {
            return [ $packageName, '*'];
        }

        // TODO: Good or Bad? ... alternative would be `*` on all
        $versionNumbers = [];
        foreach (explode('-', $versions) as $version) {
            $explodedVersion = explode('.', trim($version));
            $versionNumbers[] = $explodedVersion[0];
        }

        $constraint = [];
        if (count($versionNumbers) === 2) {
            foreach (range($versionNumbers[0], $versionNumbers[1]) as $version) {
                $constraint[] = '~' . $version;
            }
        } else {
            $constraint[] = '~' . $versionNumbers[0];
        }

        return [ $packageName, implode(' || ', $constraint)];
    }

    /**
     * @return Finder
     */
    public function getExtensions(): Finder
    {
        $finder = Finder::create();
        $finder->directories()->depth(0);

        foreach ($this->folders as $folder) {
            $finder->in($this->docRoot . '/' . $folder);
        }

        return $finder;
    }

    /**
     * @param string $extKey
     * @return string
     */
    public static function convertToPackageName(string $extKey): string
    {
        return 'typo3-local/' . str_replace('_', '-', $extKey);
    }

    public function setExtensionKey($path, $extKey, $resultFilename = 'composer.json'): void
    {
        $jsonPath = $path . '/composer.json';
        $json = json_decode(file_get_contents($jsonPath), true);
        $json['extra']['typo3/cms']['extension-key'] = $extKey;

        $this->filesystem->filePutContentsIfModified($path . '/' . $resultFilename, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }

    /**
     * @param string $extPath
     * @return array
     */
    public function getExtensionClassMap(string $extPath): array
    {
        $classMap = ClassMapGenerator::createMap($extPath);
        $path = realpath($extPath);

        $extClasses = [];
        foreach ($classMap as $class) {
            $extClasses[] = $this->filesystem->findShortestPath(
                $path,
                $class
            );
        }

        return $extClasses;
    }
}
