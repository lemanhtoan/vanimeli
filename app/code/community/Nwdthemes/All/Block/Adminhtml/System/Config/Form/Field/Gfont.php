<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Block_Adminhtml_System_Config_Form_Field_Gfont extends Mage_Adminhtml_Block_System_Config_Form_Field {

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $htmlId = $element->getHtmlId();
        $html = parent::_getElementHtml($element);
		$html .= '<br/>
		<div id="nwdthemes_gfont_preview' . $htmlId . '" style="font-size:20px; margin-top:5px;">The quick brown fox jumps over the lazy dog</div>
		<script type="text/javascript">new gFontPreview("' . $htmlId. '");</script>';
        return $html;
    }

}
