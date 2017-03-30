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
 * Rewardpointsdashboard Adminhtml Dashboard Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard extends Mage_Adminhtml_Block_Template
{	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('rewardpointsreport/dashboard.phtml');
	}
	
        /**
        * prepare columns for this dashboard
        *
        * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard
        */
	protected function _prepareLayout(){
		$this->setChild('earn_to_spend',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_left_earntospend'));
        $this->setChild('cost_of_loyatlymember',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_left_costloyaltymember'));
        $this->setChild('earning_distribution',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_left_earningdistribution'));
        $this->setChild('average_value_per_point',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_left_averagevalueperpoint'));
        $this->setChild('totals_points_balance',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_left_totalspointsbalance'));
        
        $this->setChild('diagrams',$this->getLayout()->createBlock('rewardpointsreport/adminhtml_dashboard_right_diagrams'));
		
		parent::_prepareLayout();
	}
	
        /**
        * get switch url
        *
        * @return type
        */
	public function getSwitchUrl(){
		if ($url = $this->getData('switch_url'))
			return $url;
		return $this->getUrl('*/*/*', array('_current'=>true, 'period'=>null));
	}
}