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

class Nwdthemes_All_Block_Adminhtml_Load extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_blockGroup = 'nwdall';
        $this->_controller = 'adminhtml_load';
        $this->_headerText = Mage::helper('nwdall')->__('Load Configuration');

        $this->_updateButton('save', 'label', Mage::helper('nwdall')->__('Load'));
        $this->_updateButton('save', 'onclick', 'loadForm.submit(); return false;');
        $this->_removeButton('back');
        $this->_removeButton('reset');
    }
}