<?php

/*
 * maklermodul bundle for Contao Open Source CMS
 *
 * Copyright (c) 2018 pdir / digital agentur // pdir GmbH
 *
 * @package    maklermodul-bundle
 * @link       https://www.maklermodul.de
 * @license    proprietary / pdir license - All-rights-reserved - commercial extension
 * @author     Mathias Arzberger <develop@pdir.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Namespace.
 */

namespace Pdir\MaklermodulBundle\Maklermodul\FieldRenderer;

use Pdir\MaklermodulBundle\Maklermodul\Domain\Model\Estate;
use Pdir\MaklermodulBundle\Maklermodul\FieldRenderer;
use Pdir\MaklermodulBundle\Maklermodul\FieldTranslator;

class Number extends FieldRenderer
{
    private $decimalCount = 0;

    public function __construct($key, $value, FieldTranslator $translator, $decimalCount)
    {
        parent::__construct(
            $key,
            $value,
            $translator
        ); // @todo: Change the autogenerated stub
        $this->decimalCount = $decimalCount;
    }

    /**
     * Rückgabe des Wertes als String.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->getSetting('withoutLabel')) {
            $template = $this->getShortTemplate();

            return sprintf($template,
                Estate::sanizeFileName($this->getKey()),
                $this->value(true)
            );
        }

        $template = $this->getLongTemplate();

        return sprintf($template,
            Estate::sanizeFileName($this->getKey()),
            $this->key(true),
            $this->value(true)
        );
    }

    /**
     * Methode für die Rückgabe eines Wertes.
     *
     * @param bool $doNotPrint
     *
     * @return string
     */
    public function value($doNotPrint = false)
    {
        $returnValue = $this->getSetting('prefix');
        $returnValue .= number_format((float) ($this->getValue()), $this->decimalCount, ',', '.');
        $returnValue .= $this->getSetting('append');

        return $returnValue;
    }

    private function getLongTemplate()
    {
        return <<<'EOT'
<div class="field %s">
    <div class="label">%s</div>
    <div class="value-number">%s</div>
</div>
EOT;
    }

    private function getShortTemplate()
    {
        return <<<'EOT'
<div class="field value-number %s">%s</div>
EOT;
    }
}
