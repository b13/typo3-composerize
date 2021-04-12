<?php

declare(strict_types=1);

namespace B13\Typo3Composerize\Utilities;

/**
 * Class which acts like a database for converting a single extension key into a composer-package name.
 * If none found, returns null.
 */
class ExtensionKeyMap
{
    // TODO: Due to performance issues not used atm, loading only a local file see $this->terComposerMap
    //const TER_URL = 'https://extensions.typo3.org/index.php?eID=ter_fe2:extension&action=findAllWithValidComposerName';

    protected array $terComposerMap = [];

    const CORE_EXTENSIONS = [
        'php' => 'php',
        'typo3' => 'typo3/cms-core',
        'extbase' => 'typo3/cms-extbase',
        'belog' => 'typo3/cms-belog',
        'form' => 'typo3/cms-form',
        'install' => 'typo3/cms-install',
        'core' => 'typo3/cms-core',
        'cms' => 'typo3/cms-core',
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

    public function __construct(array $mapData = null)
    {
        if (is_array($mapData)) {
            $this->terComposerMap = $mapData;
        } else {
            $terComposerMap = json_decode(file_get_contents(__DIR__ . '/../../Static/typo3-ter-composer-map.json'), true);
            $this->terComposerMap = (array)($terComposerMap['data'] ?? []);
        }
    }

    public function resolvePackageNameFromExtensionKey(string $extKey): ?string
    {
        if (!empty($this->terComposerMap[$extKey]['composer_name'])) {
            return $this->terComposerMap[$extKey]['composer_name'];
        }

        if (!empty(self::CORE_EXTENSIONS[$extKey])) {
            return self::CORE_EXTENSIONS[$extKey];
        }
        return null;
    }
}
