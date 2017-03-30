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
 * Rewardpointsreport Dashboard Report_Left_Earningdistribution Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Earningdistribution
    extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Graph
{
    
    /**
     * get Earning Action to show On Report
     * 
     * @return array
     */
    protected function _getEarningActions()
    {
        if (!$this->hasData('earn_actions')) {
            $earnAction = array();
            if (Mage::helper('rewardpoints/core')->isModuleEnabled('Magestore_RewardPointsBehavior')) {
                $earnAction = array(
                    'registed'      => Mage::helper('rewardpointsreport')->__('Sign-up'),
                    'newsletter'    => Mage::helper('rewardpointsreport')->__('Newsletter'),
                    'birthday'      => Mage::helper('rewardpointsreport')->__('Birthday'),
                    'review'        => Mage::helper('rewardpointsreport')->__('Review'),
                    'tag'           => Mage::helper('rewardpointsreport')->__('Product Tag'),
                    'fblike'        => Mage::helper('rewardpointsreport')->__('Facebook Like'),
                    // 'tweeting'      => Mage::helper('rewardpointsreport')->__('Tweet'),
                );
            }
            $this->setData('earn_actions', $earnAction);
            Mage::dispatchEvent('rewardpointsreport_block_dashboard_get_earning_actions', array('block' => $this));
        }
        return $this->getData('earn_actions');
    }
    
    /**
     * prepare datas for report chart
     * 
     * @return type
     */
    protected function _getEarningDistribution()
    {
        $collection = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        if ($storeId = $this->getRequest()->getParam('store', 0)) {
            $collection->addFieldToFilter('main_table.store_id', $storeId);
        }
        $actions = array_keys($this->_getEarningActions());
        $earnedActions = implode("','", array_merge($actions, array(
            'admin', 'earning_invoice'
        )));
        $columns = array(
            'total'     => "SUM(IF(action_type = 2, 0, real_point))",
            'admin'     => "SUM(IF(action = 'admin', real_point, 0))",
            'sales'     => "SUM(IF(action = 'earning_invoice', real_point, 0))",
        );
        foreach ($actions as $action) {
            $columns[$action] = "SUM(IF(action = '$action', real_point, 0))";
        }
        $columns['other'] = "SUM(IF(action_type = 1 AND action NOT IN ('$earnedActions'), real_point, 0))";
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns($columns);
        $totals = $collection->getFirstItem();
        if ($totals && is_object($totals)) {
            return $totals->getData();
        }
        return array();
    }

    /**
     * construct prepare google chart params
     */
    public function __construct() {
        $chd = '';
        $chdl = '';
        $i = 0;
        
        $data = $this->_getEarningDistribution();
        $totals = isset($data['total']) ? $data['total'] : 1;
        
        $actionLabels = $this->_getEarningActions();
        $actionLabels['admin'] = Mage::helper('rewardpointsreport')->__('Admin');
        $actionLabels['sales'] = Mage::helper('rewardpointsreport')->__('Order');
        $actionLabels['other'] = Mage::helper('rewardpointsreport')->__('Others');
        
        foreach ($data as $key => $value) {
            if ($key == 'total') continue;
            $i++;
            if ($chdl) {
                $chdl .='|';
            }
            $percent = round(($value * 100 / $totals), 2);
            $chdl .= $percent . '% ' . $actionLabels[$key];
            if ($chd !== '') {
                $chd .=',';
            }
            $chd .= $percent;
        }
        $colors = $this->getArrayColor();
        $chco = '';
        for ($j = 0; $j < $i; $j++) {
            if ($chco)
                $chco .='|';
            $chco .= $colors[$j];
        }
        if (isset($data['total']) && $data['total'])
            $this->_is_has_data = true;
        $this->_google_chart_params = array(
            'cht' => 'p',
            'chdl' => $chdl,
            'chd' => "t:$chd",
            'chco' => $chco,
            'chds' => 'a',
        );
        $this->setHtmlId('left_traffics');
        parent::__construct();
    }

    protected function _prepareData() {
        $this->setDataHelperName('rewardpointsreport/dashboard_leftchart');
    }

    /**
     * colors of chart
     * 
     * @return type
     */
    public function getArrayColor() {
        return array(
            '4da74d', 'cb4b4b', 'afd8f8', 'edc240', '6887B5', '0000FF', 'FFFF00', 'FF00FF', 'FF0000', '00FF00', '0000CD', '008000', '00FFFF', '191970', '4B0082', '7FFF00', '8B008B', '9400D3', 'B22222', 'C71585', 'DAA520', 'DC143C', 'FFD700'
        );
    }

    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent() {
        return $this->__('This graph shows sources that Customers earn points.');
    }
}
