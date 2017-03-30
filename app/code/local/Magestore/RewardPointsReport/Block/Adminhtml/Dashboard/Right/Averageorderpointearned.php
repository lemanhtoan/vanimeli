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
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Averageorderpointearned extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Graph
{
	public function __construct(){
        $currency = Mage::app()->getLocale()->currency(
            Mage::app()->getStore()->getBaseCurrencyCode()
        )->getSymbol();
		$this->_google_chart_params = array(
			'cht'  => 'lc',
			'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
			'chdl' => $this->__('Avg. Points Per Order').'|'.$this->__('Order Value(%s)',$currency),
            'chxla' => '2:|'.$this->__('(Points)').'|4:|'.$this->__('(%s)',$currency),
            'chxp' => '2,100|4,100',
			'chco' => '2424ff,db4814',
            'chxt' => 'x,y,y,r,r',
            'chxs' => '1,2424ff,10,1,lt,2424ff,2424ff|3N'.$currency.'*f2zs*,db4814,10,1,lt,db4814,db4814',
		);
        $this->_width = '650';
		$this->_encoding = 't';
		$this->setHtmlId('averageorderpointearned');
        parent::__construct();
    }
    
     /**
     * prepare data for this dashboard
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Averageorderpointearned
     */
    protected function _prepareData(){
    	$this->setDataHelperName('rewardpointsreport/dashboard_averageorderpointearned');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	$this->setDataRows(array('point_order_earned','size_order_spent'));
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    	);
    	parent::_prepareData();
        $this->mapAxisRange(1, 'point_order_earned');
        $this->mapAxisRange(3, 'size_order_spent');
    }
    
    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent(){
        return $this->__('This report shows you the average order value and the number of points earned from orders.<br> You will be able to see a correlation between the avg. number of points given to Customers per order and the avg. order value. What you will expect to see is an increase in the order value as you increase the number of points given to Customers for their orders.');
    }
}