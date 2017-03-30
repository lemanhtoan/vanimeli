<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Model_Mysql4_Css extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('nwdrevslider/css', 'id');
    }
}
