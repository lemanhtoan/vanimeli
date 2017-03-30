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
 * Rewardpointscsv Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsCsv
 * @author      Magestore Developer
 */
class Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv extends Mage_Adminhtml_Block_Widget_Grid_Container {

    /**
     * Magestore_RewardPointsCsv_Block_Adminhtml_Rewardpointscsv constructor.
     */
    public function __construct() {
        $this->_controller = 'adminhtml_rewardpointscsv';
        $this->_blockGroup = 'rewardpointscsv';
        $this->_headerText = Mage::helper('rewardpointscsv')->__('Reward Point Balances Information');
        $this->_addButtonLabel = Mage::helper('rewardpointscsv')->__('Import CSV');
        parent::__construct();
        $this->removeButton('add');
        $this->_addButton('import_pointbalance',array(
		'label'		=> Mage::helper('rewardpointscsv')->__('Import Points'),
		'onclick'	=> "setLocation('{$this->getUrl('*/*/import')}')",
		'class'		=> 'add'
	),-1);
    }

}