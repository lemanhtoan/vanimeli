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
 * Rewardpointsreport Dashboard Left TotalPointsBalance Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Totalspointsbalance extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('rewardpointsreport/dashboard/pattern.phtml');
	}
    
    /**
     * get head title for dashboard
     *
     * @return string
     */
    public function getHeadTitle(){
        return Mage::helper('rewardpointsreport')->__('Total points in Customers’ balances');
    }
    
    /**
     * get content for dashboard
     *
     * @return string
     */
    public function getContent(){
        $collection = Mage::getResourceModel('rewardpoints/customer_collection');
        $collection->getSelect()
                    ->columns(array(
                        'totals' => 'SUM(point_balance)',
                        'id' => 'abs(1)',
                    ))->group('id');
        $totals_points_balance = $collection->getFirstItem()->getTotals();
        return '<br><span class="rewards-report-number">' . Mage::helper('rewardpoints/point')->format($totals_points_balance, Mage::app()->getStore()) . '</span><p></p>';
    }
    
    /**
     * get explanation for dashboard
     *
     * @return string
     */
    public function getExplanation(){
        return Mage::helper('rewardpointsreport')->__('This is the number of points currently presented in Customers’ balances.');
    }
}
