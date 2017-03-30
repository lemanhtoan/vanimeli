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

class Nwdthemes_All_Lib_Varien_Data_Form_Element_Googlefont extends Varien_Data_Form_Element_Select {

	/**
	 * Constructor
	 */

    public function __construct($attributes=array()) {
        parent::__construct($attributes);
    }

	/**
	 * Get element html
	 */

	public function getElementHtml() {
        $html = parent::getElementHtml();
		$html .= '<br/>
		<div id="nwdthemes_gfont_preview' . $this->getHtmlId() . '" class="nwdthemes_gfont_preview" style="font-size:20px; margin-top:5px;">The quick brown fox jumps over the lazy dog</div>
		<script type="text/javascript">new gFontPreview("' . $this->getHtmlId() . '");</script>';
        return $html;
	}

}