<?php
declare(strict_types=1);

namespace B13\Typo3Composerize\Utilities;

/**
 * Creates a composer.json out of the extension key and the ext_emconf.php data
 */
class ComposerManifestCreator
{
    protected ExtensionKeyMap $extensionKeyMap;
    const FALLBACK_VENDOR = 'typo3-local';

    public function __construct(ExtensionKeyMap $map = null)
    {
        $this->extensionKeyMap = $map ?? new ExtensionKeyMap();
    }

    public function createComposerManifest(string $extensionKey, array $emConf): array
    {
        $constraints = ['depends', 'suggests', 'conflicts'];
        foreach ($constraints as $constraint) {
            unset($$constraint);
            if (!empty($emConf['constraints'][$constraint])) {
                foreach ($emConf['constraints'][$constraint] as $key => $version) {
                    [$currentKey, $currentVersion] = $this->convertConstraint($key, $version);
                    $$constraint[$currentKey] = $currentVersion;
                }
            }
        }

        return [
            'name' => $this->getPackageName($extensionKey),
            'description' => $emConf['title'] . ' - ' . ($emConf['description'] ?? ''),
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
                    'extension-key' => $extensionKey,
                ]
            ],
            'version' => 'dev-local',
            'autoload' => [
                'classmap' => ['*'],
            ]
        ];
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
        if (empty($versions) || preg_match('/^' . self::FALLBACK_VENDOR . '\/(.*)/', $packageName)) {
            return [$packageName, '*'];
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

        return [$packageName, implode(' || ', $constraint)];
    }

    /**
     * Return the composer package name for the extension key
     * First checks the database, then creates a fallback
     *
     * @param string $extKey
     * @return false|mixed
     */
    public function getPackageName(string $extKey)
    {
        $packageName = $this->extensionKeyMap->resolvePackageNameFromExtensionKey($extKey);
        return $packageName ?? self::getFallbackPackageNameFromExtensionKey($extKey);
    }

    public static function getFallbackPackageNameFromExtensionKey(string $extKey): string
    {
        return self::FALLBACK_VENDOR . '/' . str_replace('_', '-', $extKey);
    }
}
