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
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Earnedspent extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Graph
{
	public function __construct(){
		$this->_google_chart_params = array(
			'cht'  => 'lc',
			'chf'  => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
			'chdl' => $this->__('Earned').'|'.$this->__('Spent'),
			'chco' => '2424ff,ff3300',
            'chxt' => 'x,y,y',
            'chxla'=> '2:|'.$this->__('(Points)'),
            'chxp' => '2,100',
            'chxs' => '1,660066,10,1,lt,660066,660066',
		);
		
		$this->setHtmlId('earnedspent');
        parent::__construct();
    }
    
     /**
     * prepare data for this dashboard
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Storesignups
     */
    protected function _prepareData(){
    	$this->setDataHelperName('rewardpointsreport/dashboard_earnedspent');
    	$this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
    	
    	$this->setDataRows(array('earned', 'spent'));
    	$this->_axisMaps = array(
    		'x'	=> 'range',
    		'y' => 'earned',
            'y' =>  'spent',
            //'y'	=> 'earned_spent',
    	);
    	
    	parent::_prepareData();
    }
    
    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent(){
        return $this->__('This report shows the number of points your Customers earned and spent.');
    }
}