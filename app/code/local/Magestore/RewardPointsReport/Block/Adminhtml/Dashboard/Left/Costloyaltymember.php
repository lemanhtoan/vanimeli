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
 * Rewardpointsreport Dashboard Left Costloyatlymember Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */

class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Costloyaltymember extends Mage_Adminhtml_Block_Dashboard_Bar {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('rewardpointsreport/dashboard/pattern.phtml');
    }

    /**
     * get content for title
     * 
     * @return type
     */
    public function getHeadTitle() {
        return Mage::helper('rewardpointsreport')->__('Your cost for a loyal Customer');
    }

    /**
     * get calc average of cost than render content for Cost of loyal member
     * 
     * @return type
     */
    public function getContent() {
        $collection = Mage::getResourceModel('rewardpointsreport/transaction_collection')->prepareCostLoyaltyMember();
        //$data = $collection->getFirstItem();
        if ($collection->getCustomerNumber()) {
            $average = $collection->getMoneySpentTotals() / $collection->getCustomerNumber();
        } else {
            $average = 0;
        }
        $currency = Mage::getModel('directory/currency')->load(Mage::app()->getStore()->getBaseCurrencyCode());
        return '<br/><span class="rewards-report-number">' . $currency->format($average) . '</span><p></p>';
    }

    /**
     * get comments
     * 
     * @return type
     */
    public function getExplanation() {
        return Mage::helper('rewardpointsreport')->__('This is the average cost of a loyal Customer. A loyal Customer is the one who has spent points on your store.');
    }

}