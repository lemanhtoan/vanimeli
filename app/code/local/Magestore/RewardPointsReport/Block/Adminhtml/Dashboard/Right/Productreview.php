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
 * Rewardpointsdashboard Adminhtml Dashboard Right Productreview Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Productreview extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Graph
{
	public function __construct(){
		$this->_google_chart_params = array(
			'cht'  => 'lc',
			'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
			'chdl' => $this->__('Points').'|'.$this->__('Reviews'),
			'chco' => '2424ff,db4814',
            'chxt' => 'x,y,y,r,r',
            'chxla' => '2:|'.$this->__('(Points)').'|4:|'.$this->__('(Reviews)'),
            'chxp' => '2,100|4,100',
            'chxs' => '1,2424ff,10,1,lt,2424ff,2424ff|3,db4814,10,1,lt,db4814,db4814',
		);
		
        $this->_encoding = 't';
		$this->setHtmlId('productreview');
        parent::__construct();
    }
    
    /**
     * prepare data for this dashboard
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Productreview
     */
    protected function _prepareData(){
    	$this->setDataHelperName('rewardpointsreport/dashboard_productreview');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	
    	$this->setDataRows(array('review_points','review_amount'));
    	$this->_axisMaps = array('x'=> 'range');
    	parent::_prepareData();
        $this->mapAxisRange(1, 'review_points');
        $this->mapAxisRange(3, 'review_amount');
    }
    
    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent(){
        return $this->__('This report shows the number of points rewarded for Customers’ product reviews vs. the number of reviews.');
    }
}