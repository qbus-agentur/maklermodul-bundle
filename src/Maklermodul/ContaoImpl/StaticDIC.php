<?php

/**
 * maklermodul for Contao Open Source CMS
 *
 * Copyright (C) 2017 pdir / digital agentur <develop@pdir.de>
 *
 * @package    maklermodul
 * @link       https://www.maklermodul.de
 * @license    pdir license - All-rights-reserved - commercial extension
 * @author     pdir GmbH <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Namespace
 */
namespace Pdir\MaklermodulBundle\Maklermodul\ContaoImpl;

use Contao\System;
use Pdir\MaklermodulBundle\Maklermodul\ContaoImpl\Domain\Model\Enviroment;
// use Contao\Folder;

class StaticDIC {

    const CONFIG_ROOT = '../../config';
    const FILTER_CSS_MAPPING = 'filter.ini';
    const USER_FILTER_CSS_MAPPING = 'files/makler_modul_mplus/data.filter.ini';

    private static $filterConfig = null;

    public static function getTranslationMap($useCore = true) {
        if (!isset($GLOBALS['TL_LANG']['makler_modul_mplus']['field_keys']['language_loaded'])) {
            if ($useCore) {
                System::loadLanguageFile('makler_modul_mplus');
            } else {
                // could not detect language in index processing so we could not use
                // contao's build in feature: System::loadLanguageFile('makler_modul_mplus');
            	require_once __DIR__ . '/' . self::CONFIG_ROOT . '/../languages/de/makler_modul_mplus.php';
                // include Local configuration file
                if (file_exists(TL_ROOT . '/system/config/langconfig.php'))
                {
                    require_once TL_ROOT . '/system/config/langconfig.php';
                }
            }
        }
        return $GLOBALS['TL_LANG']['makler_modul_mplus'];
    }

    public static function getCssFilterClassMapping() {
        if (self::$filterConfig == null) {
            $fileName = sprintf('%s/%s/%s',
                __DIR__,
                self::CONFIG_ROOT,
                self::FILTER_CSS_MAPPING
            );

            self::$filterConfig = parse_ini_file($fileName);
  			// load user filter css mapping
            if(file_exists(\Environment::get('documentRoot') . self::USER_FILTER_CSS_MAPPING)){
            	$userFilterConf = parse_ini_file(\Environment::get('documentRoot') . self::USER_FILTER_CSS_MAPPING);
            	self::$filterConfig = array_merge(self::$filterConfig, $userFilterConf);
            }
            if (self::$filterConfig === false) {
                throw new \Exception("Could not load filter mapping from $fileName");
            }
        }

        return self::$filterConfig;

    }

    /**
     * @param $fileName
     *
     * @return \Contao\File
     */
    public static function getFileHelper($fileName) {
        return new FileHelper($fileName, false);
    }

    /**
     * @param $folderName
     *
     * @return \Contao\Folder
     */
    public static function getFolderHelper($folderName) {
        return new FileHelper($folderName, true);
    }

}