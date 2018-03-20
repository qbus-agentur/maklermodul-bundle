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
namespace Pdir\MaklermodulBundle\Module;

class MaklermodulSetup extends \BackendModule
{
    /**
     * Maklermodul version
     */
    const VERSION = '2.0.1';

    /**
     * Extension mode
     * @var boolean
     */
    const MODE = 'DEMO';

	/**
	 * API Url
	 * @var string
	 */
	static $apiUrl = 'https://pdir.de/api/maklermodul/';

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_maklermodul_setup';

    /**
     * Storage directory path
     * @var string
     */
    protected $storageDirectoryPath;

    /**
     * Demo data filename
     * @var string
     */
    protected $demoDataFilename = 'data/demo-data.zip';

    /**
     * Debug message Array
     * @var array
     */
    public $debugMessages;

    /**
    * Generate the module
    * @throws \Exception
    */
    protected function compile()
    {
        $this->storageDirectoryPath = \Config::get('uploadPath') . '/maklermodul/';

		// $className = '/vendor/pdir/maklermodul-bundle/src/Resources/contao/Classes/Helper.php';
		$strDomain = \Environment::get('httpHost');

		/* @todo empty cache folder from backend */
        $files = \Files::getInstance();

		switch (\Input::get('act')) {
            case 'emptyDataFolder':
                $files->rrdir($this->storageDirectoryPath.'data', true);
                $this->debugMessages[] = array($GLOBALS['TL_LANG']['MOD']['maklerSetup']['message']['emptyFolder'], 'info');
                break;
            case 'emptyUploadFolder':
                $files->rrdir($this->storageDirectoryPath.'upload', true);
                $this->debugMessages[] = array($GLOBALS['TL_LANG']['MOD']['maklerSetup']['message']['emptyFolder'], 'info');
                break;
            case 'emptyTmpFolder':
                $files->rrdir($this->storageDirectoryPath.'org', true);
                $this->debugMessages[] = array($GLOBALS['TL_LANG']['MOD']['maklerSetup']['message']['emptyFolder'], 'info');
                break;
            case 'downloadDemoData':
                $this->downloadDemoData();
                $this->debugMessages[] = array('Demo Daten wurden heruntergeladen.', 'info');
                break;
            default:
                $this->Template->base = $this->Environment->base;
                $this->Template->version = self::VERSION;
                $this->Template->storageDirectoryPath = $this->storageDirectoryPath;
		}

		$this->Template->extMode = static::MODE;
		$this->Template->extModeTxt = static::MODE=='FULL' ? 'Vollversion' : 'Demo';
		$this->Template->version = self::VERSION;
		$this->Template->hostname = gethostname();
		$this->Template->ip = \Environment::get('server');
		$this->Template->domain = $strDomain;

        $this->Template->params = $this->configParameters; // for debug
        $this->Template->debugMessages = $this->getDebugMessages();

		// email body
		$this->Template->emailBody = $this->getEmailBody();
    }

	protected function getEmailBody()
	{
		$arrSearch = array(':IP:', ':HOST:', ':DOMAIN:', '<br>');
		$arrReplace = array($this->Template->ip, $this->Template->hostname, $this->Template->domain, '%0d%0a');
		return str_replace( $arrSearch, $arrReplace, $GLOBALS['TL_LANG']['MAKLERMODUL']['emailBody'] );
	}

    protected function downloadDemoData()
    {
        $strFile = $this->storageDirectoryPath . $this->demoDataFilename;

        try
        {
            \File::putContent($strFile, file_get_contents('https://pdir.de/api/data/maklermodul/demo-data.zip'));
        }
        catch (\Exception $e)
        {
            \Message::addError($e->getMessage());
        }

        $this->unzipDemoData();
    }

    protected function unzipDemoData()
    {
        $objArchive = new \ZipReader($this->storageDirectoryPath . $this->demoDataFilename);

        // Extract all files
        while ($objArchive->next())
        {
            // Extract the files
            try
            {
                \File::putContent($this->storageDirectoryPath . 'data/' . $objArchive->file_name, $objArchive->unzip());
            }
            catch (\Exception $e)
            {
                \Message::addError($e->getMessage());
            }
        }

        return;
    }

    public function debug($message)
    {
        if(!is_array($message))
        {
            $this->debugMessages[] = array($message, 'info');
            return;
        }

        if (!defined('CRONJOB') OR CRONJOB == false) {
            $this->debugMessages[] = $message;
        }
        return;
    }

    public function getDebugMessages()
    {
        return $this->debugMessages;
    }
}
