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
 * RewardPointsReport Mysql4 Transaction Collection Model
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection extends Magestore_RewardPoints_Model_Mysql4_Transaction_Collection
{
    protected $_isGroupSql = false;
    protected $_earnTotals;
    protected $_spentTotals;
    protected $_moneySpentTotals;

    public function getEarnTotals() {
        if (!$this->_earnTotals)
            $this->prepareEarningSpendingTotal();
        return $this->_earnTotals;
    }

    public function getSpentTotals() {
        if (!$this->_spentTotals)
            $this->prepareEarningSpendingTotal();
        return $this->_spentTotals;
    }

    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }
    
    public function addCreateDayFilter($date) {
        $day = date('Y-m-d', $date);
        $this->getSelect()->where('date(created_time) = ?', $day);
        return $this;
    }

    /*
    public function addExpireAfterDaysFilter($dayBefore) {
        $date = Mage::getModel('core/date')->gmtDate();
        $zendDate = new Zend_Date($date);
        $dayAfter = $zendDate->addDay($dayBefore)->toString('YYYY-MM-dd');
        $this->getSelect()->where('date(expiration_date) = ?', $dayAfter);
        return $this;
    }
    */
    
    /**
     * prepare Spend Point, Earn Point, money spent total
     * 
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareEarningSpendingTotal() {
        if ($storeId = Mage::app()->getRequest()->getParam('store'))
            $this->addFieldToFilter('store_id', $storeId);
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->getSelect()
                //->group('action_type')
                ->columns(array(
                    'earned' => 'SUM(IF( action_type = 2, 0, real_point))',
                    'spent' => '-SUM(IF(( action_type = 2 OR (action_type = 0 AND point_amount < 0)), point_amount, 0))',
                ));
        
        if($datas = $this->getFirstItem()){
            $this->_earnTotals  = $datas->getEarned();
            $this->_spentTotals = $datas->getSpent();
        }
        return $this;
    }
    
    
    /**
     * prepare data
     * 
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareCostLoyaltyMember(){
        // Add Store Filter Data
        if ($storeId = Mage::app()->getRequest()->getParam('store'))
            $this->addFieldToFilter('store_id', $storeId);
        $this->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        $this->addFieldToFilter('order_id', array('notnull' => true));
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(
                    'customer_id' => 'customer_id',
                    'order_base_amount' => 'order_base_amount',
                ))->group('order_id')
                ->where('action_type = ?', 2);
        $view = clone $this->getSelect();
        $this->getSelect()->reset()
                ->from(array('main_table' => new Zend_Db_Expr('(' . $view->__toString() . ')')), array())
                ->columns(array(
                    'customer_id' => 'customer_id',
                    'money_spent' => 'SUM(order_base_amount)',
                ))
                ->group('customer_id');
        $customer_number = 0;
        $money_spent_totals = 0;
        foreach ($this as $row){
            $money_spent_totals += $row->getMoneySpent();
            $customer_number++;
        }
        
        return New Varien_Object(array('customer_number'=>$customer_number, 'money_spent_totals'=>$money_spent_totals));
    }
    
    /**
     * prepare Earning Points distribution
     * 
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareEarningdistribution(){
        if ($storeId = Mage::app()->getRequest()->getParam('store'))
            $this->addFieldToFilter('store_id', $storeId);
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addFieldToFilter('action_type', array('in' => array('0', '1'))); //fill only action type is earning
        $this->getSelect()
                ->group('action')
                ->columns(array(
                    'action',
                    'earned' => 'SUM(IF( point_amount > 0, point_amount, 0 ))'
                ));
        return $this;
    }

    /**
     * prepare data for calc average Average value of money per point
     * 
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareAverageValuePerPoint(){
        // Add Store Filter Data
        if ($storeId = Mage::app()->getRequest()->getParam('store'))
            $this->addFieldToFilter('store_id', $storeId);
        $this->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        $this->addFieldToFilter('order_id', array('notnull' => true));
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array(
                    'points_spent' => '-SUM(point_amount)',
                    'order_base_amount' => 'order_base_amount',
                ))->group('order_id')
                ->where('action_type = ?', 2);
        $view = clone $this->getSelect();
        $this->getSelect()->reset()
                ->from(array('main_table' => new Zend_Db_Expr('(' . $view->__toString() . ')')), array())
                ->columns(array(
                    'totals_point_spent' => 'SUM(points_spent)',
                    'totals_money_spent' => 'SUM(order_base_amount)',
                ));
        return $this;
    }
    
    
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false) {
        $dateEnd = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range) {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('rewardpoints/report/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('rewardpoints/report/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int) $startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int) $startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                //$dateEnd->setDay(1);
                //$dateEnd->addMonth(1);
                //$dateEnd->subDay(1);
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }

    protected function _getRangeExpressionForAttribute($range, $attribute) {
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }

    protected function _getRangeExpression($range) {
        switch ($range) {
            case '24h':
                $offset = Mage::getModel('core/date')->getGmtOffset(); // Offset in seconds to UTC 
                $offsetHours = round(abs($offset)/3600); 
                $offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60); 
                $offsetString = ($offset < 0 ? '-' : '+')
                            . ($offsetHours < 10 ? '0' : '') . $offsetHours . ':'
                            . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes;
                $expression = 'DATE_FORMAT(CONVERT_TZ({{attribute}},\'+00:00\',\''.$offsetString.'\'), \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }

        return $expression;
    }

    /**
     * prepare data helper for Point earned / points spend
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareEarnedSpent($range, $customStart, $customEnd) {
        $this->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        
        $this->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'earned' => 'SUM(IF(action_type != 2 and action_type != 0, real_point, 0))',
                'spent'  => '-SUM(IF(action_type = 2, point_amount, 0))',
            ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->getSelect()
            ->columns(array(
                'range' => $this->_getRangeExpressionForAttribute($range, 'main_table.created_time')
            ))
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group('range');
        $this->addFieldToFilter('main_table.created_time', $dateRange);
        return $this;
    }

    /**
     * prepare number loyal members
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareNumberloyaltymember($range, $customStart, $customEnd) {
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addFieldToFilter('action_type', '2');
        $this->getSelect()->columns(array(
            'num_loyal' => 'COUNT(DISTINCT customer_id)',
        ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->getSelect()->columns(array(
                'range' => $this->_getRangeExpressionForAttribute($range, 'created_time')
            ))
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group('range');
        $this->addFieldToFilter('created_time', $dateRange);
        return $this;
    }

    /**
     * prepare Average order size vs average points earned from order
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareAverageorderpointearned($range, $customStart, $customEnd) {
        //$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addFieldToFilter('order_id', array('notnull' => true))
            ->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ));
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'real_points' => 'SUM(real_point)',
                'order_base_amount' => 'order_base_amount',
                'created_time' => 'created_time',
                'store_id' => 'store_id',
                'order_id' => 'order_id',
            ))
            ->group('order_id')
            ->where('action = "earning_invoice"');
        //filter date
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->addFieldToFilter('main_table.created_time', $dateRange);
        $view = clone $this->getSelect();
        $this->getSelect()->reset()
                ->from(array('main_table' => new Zend_Db_Expr('(' . $view->__toString() . ')')), array())
                ->columns(array(
                    'range' => $this->_getRangeExpressionForAttribute($range, 'main_table.created_time'),
                    'point_order_earned' => 'SUM(real_points)/COUNT(order_id)',
                    'size_order_spent' => 'SUM(order_base_amount)/COUNT(order_id)',
                    'store_id' => 'store_id',
                    'number_order' => 'COUNT(order_id)',
                ))
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group('range');
        return $this;
    }
    
    
    /*
     * ----plugin behavior
     */
    
    /**
     * prepare signups size and points earned for that to display chart
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareStoresignups($range, $customStart, $customEnd) {
        $this->addFieldToFilter('action', array('eq' => 'registed'));
        $this->getSelect()
            ->columns(array(
            'signup_points' => 'SUM(point_amount)',
            'signup_amount' => 'COUNT(transaction_id)',
        ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->addFieldToFilter('created_time', $dateRange);
        $this->getSelect()->columns(array('range' => $this->_getRangeExpressionForAttribute($range, 'created_time')))
                ->order('range', Zend_Db_Select::SQL_ASC)
                ->group('range');
        return $this;
    }

    /**
     * prepare report Newsletters
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareNewsletters($range, $customStart, $customEnd) {
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addFieldToFilter('action', array('eq' => 'newsletter'));
        $this->getSelect()->columns(array(
            'newsletter_points' => 'SUM(real_point)',
            'newsletter_amount' => 'COUNT(transaction_id)',
        ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->addFieldToFilter('created_time', $dateRange);
        $this->getSelect()->columns(array('range' => $this->_getRangeExpressionForAttribute($range, 'created_time')))
                ->order('range', Zend_Db_Select::SQL_ASC)
                ->group('range');
        return $this;
    }

    /**
     * prepare data for report product review
     * 
     * @param type $range
     * @param type $customStart
     * @param type $customEnd
     * @return Magestore_RewardPointsReport_Model_Mysql4_Transaction_Collection
     */
    public function prepareProductreview($range, $customStart, $customEnd) {
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $this->addFieldToFilter('action', array('eq' => 'review'));
        $this->getSelect()->columns(array(
            'review_points' => 'SUM(real_point)',
            'review_amount' => 'COUNT(transaction_id)',
        ));
        $dateRange = $this->getDateRange($range, $customStart, $customEnd);
        $this->getSelect()->columns(array('range' => $this->_getRangeExpressionForAttribute($range, 'created_time')))
                ->order('range', Zend_Db_Select::SQL_ASC)
                ->group('range');
        $this->addFieldToFilter('created_time', $dateRange);
        return $this;
    }

    public function addFieldToHavingFilter($field, $condition = null) {
        $field = $this->_getMappedField($field);
        if (strpos($field, 'SUM') === false && strpos($field, 'COUNT') === false) {
            $this->_select->where($this->_getConditionSql($field, $condition), null, Varien_Db_Select::TYPE_CONDITION);
        } else {
            $this->_select->having($this->_getConditionSql($field, $condition), null, Varien_Db_Select::TYPE_CONDITION);
        }
        return $this;
    }
}