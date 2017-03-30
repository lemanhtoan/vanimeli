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
 * @package     Magestore_RewardPointsReport
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReport Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Helper_Dashboard_Averageorderpointearned extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
    /**
    * prepare collection for this dashboard
    *    
    */
    protected function _initCollection(){
            $this->_collection = Mage::getResourceModel('rewardpointsreport/transaction_collection')
                    ->prepareAverageorderpointearned($this->getParam('period'),0,0);

            if ($this->getParam('store'))
                    $this->_collection->addFieldToFilter('main_table.store_id',$this->getParam('store'));

            $this->_collection->load();
    }
}