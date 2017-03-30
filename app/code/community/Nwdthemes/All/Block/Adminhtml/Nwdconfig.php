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

class Nwdthemes_All_Block_Adminhtml_Nwdconfig extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();

        $this->_blockGroup = 'nwdall';
        $this->_controller = 'adminhtml_nwdconfig';
        $this->_headerText = Mage::helper('nwdall')->__('Save / Load Configuration');

        $this->_updateButton('save', 'label', Mage::helper('nwdall')->__('Save to file'));
        $this->_removeButton('back');
        $this->_removeButton('reset');

        $buttonData = array(
            'label' 	=>  Mage::helper('nwdall')->__('Load from file'),
            'onclick'	=> 	"configImport.openDialog('" . $this->getUrl('*/*/loadForm') . "')",
            'class'     =>  'load'
        );
        $this->_addButton('load_config', $buttonData, 0, 100, 'header');
    }
}