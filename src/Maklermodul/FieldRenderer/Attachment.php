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
namespace Pdir\MaklermodulBundle\Maklermodul\FieldRenderer;

use Contao\Image;
use Contao\ContentMedia;
use Pdir\MaklermodulBundle\Maklermodul\FieldRenderer;
use Pdir\MaklermodulBundle\Maklermodul\FieldTranslator;

class Attachment extends FieldRenderer {

    public function __construct($key, $value, FieldTranslator $translator, $group = 'BILD') {
        parent::__construct(
            $key,
            $value,
            $translator
        );

        if($GLOBALS['TL_CONFIG']['websitePath']) $this->websitePath = $GLOBALS['TL_CONFIG']['websitePath'];

        // set default values for images
        $this->setSetting('group', $group);
        $this->setSetting('location', '');
        $this->setSetting('link', false);
        $this->setSetting('mode', 'crop');
    }

    /**
     * Methode zum Setzen der Bildbreite und -höhe.
     *
     * @param integer $maxWidth
     * @param integer $maxHeight
     * @return $this
     */
    public function size($maxWidth, $maxHeight) {
        $this->setSetting('maxWidth', $maxWidth);
        $this->setSetting('maxHeight', $maxHeight);

        return $this;
    }

    /**
     * Methode zum setzen der Anhang Gruppe
     * @param string $attGroup
     * @return $this;
     */
    public function group($attGroup) {
    	$this->setSetting('group', $attGroup);
    	return $this;
    }

    /**
     * Methode zum setzen der Anhang Location
     * @param string $attLocation
     * @return $this;
     */
    public function location($attLocation) {
    	$this->setSetting('location', $attLocation);
    	return $this;
    }

    /**
     * Methode zum Setzen der Resize Mode.
     *
     * @param string $mode
     * @return $this
     */
    public function mode($mode) {
    	$this->setSetting('mode', $mode);
    	return $this;
    }

    /**
     * Methode um Bilder ohne Link anzuzeigen.
     *
     * @param string $link
     * @return $this
     */
    public function withoutLink($link) {
    	$this->setSetting('link', $link);
    	return $this;
    }

    /**
     * Rückgabe des Wertes als Text.
     *
     * @return string
     */
    public function __toString() {
        return $this->getShortString();
    }

    private function getShortString() {
    	// show only given group
    	if(strpos($this->getSetting('group'), $this->getValueOf('@gruppe')) !== false) {

    		switch ($this->getValueOf('@gruppe'))
    		{
    			case 'DOKUMENTE';
    			case 'FILMLINK';
					// render doc list
					$template = $this->getShortTemplateDoc();
					return sprintf($template,
							$this->getUrlOfPath($this->getValueOf('daten.pfad')),
							substr($this->getValueOf('format'), 1),
							$this->getValueOf('anhangtitel'),
							$this->getValueOf('anhangtitel')
					);
					break;
    			case 'FILM';
    				$template = $this->getShortTemplateMedia();
    				return 'FILM';
    				break;
    			default;
					// render image
			        $renderedThumbnail = $this->getThumbnailString(
			            $this->getValueOf('daten.pfad'),
			            $this->getValueOf('anhangtitel'),
						$this->getValueOf('@location')
			        );

			        if($this->getSetting('link')) return $renderedThumbnail;

			        $template = $this->getShortTemplate();
			        return sprintf($template,
			            $this->getUrlOfPath($this->getValueOf('daten.pfad')),
			            $this->getValueOf('anhangtitel'),
			            $renderedThumbnail
			        );
			        break;
			}

			// fallback for xml data without group definition
			$renderedThumbnail = $this->getThumbnailString(
					$this->getValueOf('daten.pfad'),
					$this->getValueOf('anhangtitel'),
					$this->getValueOf('@location')
			);

			if($this->getSetting('link')) return $renderedThumbnail;

			$template = $this->getShortTemplate();
			return sprintf($template,
					$this->getUrlOfPath($this->getValueOf('daten.pfad')),
					$this->getValueOf('anhangtitel'),
					$renderedThumbnail
			);
    	}
    }

    private function getUrlOfPath($path) {
        if($this->websitePath)
            return $this->websitePath . '/' . str_replace(TL_ROOT, '', $path);
        return str_replace(TL_ROOT, '', $path);
    }

    private function getThumbnailString($path, $alt, $location) {
        try {
            $width = $this->getSetting('maxWidth');
            $height = $this->getSetting('maxHeight');
            $mode = $this->getSetting('mode');

            $url = $path;
            // @todo make it changeable in the config file
            if($location == 'REMOTE') $url = str_replace(".jpg", "_small.jpg", $url);
            if($location == 'EXTERN') {

            	$path = $this->resizeImage($path, $width, $height, $mode);
            	$url = $this->getUrlOfPath($path);
            }
            $template = $this->getThumbnailTemplate(true);
            $result = sprintf($template, $url, $width, $height, $alt);

            return $result;
        } catch (\InvalidArgumentException $e) {
            $template = $this->getThumbnailTemplate();
            $url = $path;
            // @todo make it changeable in the config file
            if($location == 'REMOTE') $url = str_replace(".jpg", "_small.jpg", $url);
            if($location == 'EXTERN')
            	$url = $this->getUrlOfPath($path);

            return sprintf($template, $url, $alt);
        }
    }

    private function resizeImage($orgPath, $maxWidth, $maxHeight, $mode) {
        return \Image::get('/files/maklermodul/data' . $orgPath, $maxWidth, $maxHeight, $mode) ;
    }

    private function getThumbnailTemplate($resized = false) {
        $returnValue =  '<img src="%s"' . PHP_EOL;

        if ($resized) {
            $returnValue .= '        width="%s" height="%s" alt="%s" />';
        } else {
            $returnValue .= '        alt="%s" />';
        }

        return $returnValue;
    }

    private function getShortTemplate() {
        $returnValue =  '<a href="%s"' . PHP_EOL;
        $returnValue .= '   data-lightbox="galerie" title="%s">' . PHP_EOL;
        $returnValue .= '   %s' . PHP_EOL;
        $returnValue .= '</a>' . PHP_EOL;

        return $returnValue;
    }

    private function getValueOf($key) {
        $returnValue = '';
        $value = $this->getValue();
        $key = $this->getFullyQualifiedKey($key);

        if (isset($value[$key])) {
            $returnValue = $value[$key];
        }

        return $returnValue;
    }

    private function getFullyQualifiedKey($key) {
        return $this->getKey() . '.' . $key;
    }

    private function getShortTemplateDoc() {
        $returnValue =  '<li><a href="%s"' . PHP_EOL;
        $returnValue .= '   class="link-icon %s" title="Download %s" target="_blank">' . PHP_EOL;
        $returnValue .= '   %s' . PHP_EOL;
        $returnValue .= '</a></li>' . PHP_EOL;

        return $returnValue;
    }

    private function getShortTemplateMedia() {
    	// @todo implement media template
    }
}
