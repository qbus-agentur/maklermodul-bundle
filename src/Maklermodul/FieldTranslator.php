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
namespace Pdir\MaklermodulBundle\Maklermodul;

class FieldTranslator {
    const FIELD_KEYS_KEY = 'field_keys';
    private $map;

    public function __construct($translationMap) {
        $this->map = $translationMap;
    }

    /**
     * Sorgt für die Übersetzung bei mehrsprachigen Angeboten.
     *
     * @param $key
     * @return mixed
     */
    public function translate($key) {
        $returnValue = $key;

        if (isset($this->map[self::FIELD_KEYS_KEY][$key])) {
            $returnValue = $this->map[self::FIELD_KEYS_KEY][$key];
        }

        return $returnValue;
    }
}