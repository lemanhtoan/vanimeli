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
 * Rewardpointsreport Dashboard Left Earntospend Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */

class Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Earntospend extends Magestore_RewardPointsReport_Block_Adminhtml_Dashboard_Left_Graph {

    public function __construct() {
        $totals_earn = Mage::getResourceModel('rewardpointsreport/transaction_collection')
            ->getEarnTotals();
        $totals_spent = Mage::getResourceModel('rewardpointsreport/transaction_collection')
            ->getSpentTotals();
        if ($totals_earn) {
            $spent_percent = round(($totals_spent * 100 /$totals_earn), 2);
        } else {
            $spent_percent = 0;
        }
        $chdl = $spent_percent . '% ' . $this->__('Spent') . '|' . (100 - $spent_percent) . '% ' . $this->__('Not spent');
        $buffer = $spent_percent . ',' . (100 - $spent_percent);
        $chco = 'cb4b4b|4da74d';
        if ($totals_earn)
            $this->_is_has_data = true;
        $this->_google_chart_params = array(
            'cht' => 'p',
            'chdl' => $chdl,
            'chd' => "t:$buffer",
            'chco' => $chco,
            'chdlp' => 'r'
        );
        $this->setHtmlId('left_traffics');
        parent::__construct();
    }

    protected function _prepareData() {
        $this->setDataHelperName('rewardpointsreport/dashboard_leftchart');
    }

    /**
     * get comment content for dashboard
     *
     * @return string
     */
    public function getCommentContent() {
        return $this->__('This graph tells you how many points are spent for every 100 points rewarded.');
    }
}
