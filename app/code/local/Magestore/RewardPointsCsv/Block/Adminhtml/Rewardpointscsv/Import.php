<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Import
 */
class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Import extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv_Import constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_blockGroup = 'rewardpointscsv';
        $this->_controller = 'adminhtml_rewardpointscsv';
        $this->_mode = 'import';
        $this->_updateButton('save', 'label', Mage::helper('rewardpointscsv')->__('Import'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');
        $this->_formScripts[] = "
            function importAndPrint(){
                editForm.submit('" . $this->getUrl('*/*/processImport', array(
                    'print' => 'true'
                )) . "');
            }
        ";
    }

    /**
     * @return mixed
     */
    public function getHeaderText() {
        return Mage::helper('rewardpointscsv')->__('Import Points');
    }

}