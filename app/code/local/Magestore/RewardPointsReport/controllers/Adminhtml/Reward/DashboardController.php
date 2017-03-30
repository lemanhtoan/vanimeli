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
 * Rewardpointsreport Adminhtml Dashboard Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Adminhtml_Reward_DashboardController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/reports/dashboard');
    }
    
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('rewardpoints/reports')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Reward System Report'), Mage::helper('adminhtml')->__('Reward System Report'));
		return $this;
	}
 
	public function indexAction() {
		$this->_initAction();
        $this->_title($this->__('Reward System Report'))
			->_title($this->__('Reward System Report'));
        $this->renderLayout();
	}
    
    /**
     * display content for tab right
     * 
     * @return type
     */
    public function ajaxBlockAction(){
    	$output = '';
    	$blockTab = $this->getRequest()->getParam('block');
    	if (in_array($blockTab, array(
    		'adminhtml_dashboard_right_numberloyaltymember',
            'adminhtml_dashboard_right_earnedspent',
            'adminhtml_dashboard_right_averageorderpointearned',
            'adminhtml_dashboard_right_storesignups',
            'adminhtml_dashboard_right_newslettersubscription',
            'adminhtml_dashboard_right_productreview',
    	))){
    		$output = $this->getLayout()->createBlock("rewardpointsreport/$blockTab")->toHtml();
    	}
    	$this->getResponse()->setBody($output);
    }
}