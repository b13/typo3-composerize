<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Utilities;

use Composer\Autoload\ClassMapGenerator;
use Composer\Util\Filesystem;
use Symfony\Component\Finder\Finder;

class ComposerConvertUtility
{
    protected ComposerManifestCreator $manifestCreator;
    protected string $docRoot;
    protected array $folders;

    protected Filesystem $filesystem;

    public function __construct(string $docRoot, $folders = ['typo3conf/ext/', 'typo3/sysext/'])
    {
        $this->manifestCreator = new ComposerManifestCreator();
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

        $composerJson = $this->manifestCreator->createComposerManifest($extKey, $emConf);
        $composerJson['autoload'] = [
            'classmap' => $this->getExtensionClassMap($extPath)
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
