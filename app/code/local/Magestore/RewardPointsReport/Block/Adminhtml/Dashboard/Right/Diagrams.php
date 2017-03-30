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
 * Rewardpointsdashboard Adminhtml Dashboard Right Averageorderpointearned Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Diagrams extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct(){
        parent::__construct();
        $this->setId('diagram_tab');
        $this->setDestElementId('diagram_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');
    }
    
     /**
     * prepare columns for this dashboard
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Diagrams
     */
    protected function _prepareLayout(){
    	$this->addTab('numberloyaltymember',array(
    		'label'		=> $this->__('Loyal Customers'),
    		'content'	=> $this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_numberloyaltymember')->toHtml(),
    		'active'	=> true,
    	));
    	
    	$this->addTab('earnedspent',array(
    		'label'		=> $this->__('Points Earned and Spent'),
    		'content'	=>$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_earnedspent')->toHtml(),
    	));
        
        $this->addTab('averageorderpointearned',array(
    		'label'		=> $this->__('Avg. order value vs avg number of points earned from orders'),
    		'content'	=>$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_averageorderpointearned')->toHtml(),
    	));
        
        if(Mage::helper('rewardpoints/core')->isModuleEnabled('Magestore_RewardPointsBehavior')){
            $this->addTab('storesignups',array(
                'label'		=> $this->__('Sign-ups'),
                'content'	=>$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_storesignups')->toHtml(),
            ));

            $this->addTab('newslettersubscription',array(
                'label'		=> $this->__('Newsletter Subscription'),
                'content'	=>$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_newslettersubscription')->toHtml(),
            ));

            $this->addTab('productreview',array(
                'label'		=> $this->__('Product Review'),
                'content'	=> $this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_productreview')->toHtml(),
            ));
        }
    	return parent::_prepareLayout();
    }
}