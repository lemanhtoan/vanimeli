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
 * Rewardpointsdashboard Adminhtml Dashboard Right Numberloyaltymember Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Numberloyaltymember extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Graph {

    public function __construct() {
        $this->_google_chart_params = array(
            'cht' => 'lc',
            'chf' => 'bg,s,f4f4f4|c,lg,90,ffffff,0.1,ededed,0',
            'chco' => 'db4814',
            'chxt' => 'x,y,y',
            'chxlexpend' => '|2:||'.$this->__('(Members)'),
            'chxs' => '1,db4814,10,1,lt,db4814,db4814',
        );

        $this->setHtmlId('numberloyaltymember');
        parent::__construct();
    }

     /**
     * prepare data for this dashboard
     *
     * @return Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Right_Numberloyaltymember
     */
    protected function _prepareData() {
        $this->setDataHelperName('rewardpointsreport/dashboard_customer');
        $this->getDataHelper()->setParam('store', $this->getRequest()->getParam('store'));
        $this->setDataRows('num_loyal');
        $this->_axisMaps = array(
            'x' => 'range',
            'y' => 'num_loyal'
        );

        parent::_prepareData();
    }
    
    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent(){
        return $this->__('This graph shows the number of loyal Customers in your store over time.');
    }

}