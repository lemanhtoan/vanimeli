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
 * Rewardpointsreport Dashboard Left Graph Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Graph extends Mage_Adminhtml_Block_Dashboard_Graph
{
	protected $_google_chart_params = array();
	protected $_is_has_data = false;
	
	protected $_width = '300';
	protected $_height = '200';
	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('rewardpointsreport/dashboard/graph.phtml');
	}
	
	public function isHasData(){
		return $this->_is_has_data;
	}
	
    /**
	 * Get chart url
	 *
	 * @param bool $directUrl
	 * @return string
	 */
	public function getChartUrl($directUrl = true){
		$params = $this->_google_chart_params;
		
		// chart size
		$params['chs'] = $this->getWidth().'x'.$this->getHeight();

		// return the encoded data
		if ($directUrl) {
			$p = array();
			foreach ($params as $name => $value) {
				$p[] = $name . '=' .urlencode($value);
			}
			return self::API_URL . '?' . implode('&', $p);
		} else {
			$gaData = urlencode(base64_encode(serialize($params)));
			$gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
			$params = array('ga' => $gaData, 'h' => $gaHash);
			return $this->getUrl('*/*/tunnel', array('_query' => $params));
		}
	}
}