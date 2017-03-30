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
 * Rewardpointsreport Dashboard Left Averagevalueperpoint Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Averagevalueperpoint extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('rewardpointsreport/dashboard/pattern.phtml');
	}
    
    /**
     * Head title for Your average value per point
     * 
     * @return type
     */
    public function getHeadTitle(){
        return Mage::helper('rewardpointsreport')->__('Average value of 1 point spent');
    }
    
    /**
     * get contents to display
     * 
     * @return type
     */
    public function getContent(){
         $collection = Mage::getResourceModel('rewardpointsreport/transaction_collection')->prepareAverageValuePerPoint();
         $data = $collection->getFirstItem();
         if($data->getTotalsPointSpent()){
             $average = $data->getTotalsMoneySpent()/$data->getTotalsPointSpent();
         }else {
             $average = 0;
         }
         $currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode());
        return '<br><span class="rewards-report-number">' . $currency->format($average) . '</span><p></p>';
    }
    
    /**
     * get comments
     * 
     * @return type
     */
    public function getExplanation(){
        return Mage::helper('rewardpointsreport')->__('This is the average value of 1 point spent. It means that for every 1 point given to Customers, you can get $0.35 in revenue.');
    }
}
