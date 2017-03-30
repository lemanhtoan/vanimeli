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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPoints Coupon Helper
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Helper_Coupon extends Mage_Core_Helper_Data {
    /**
     * calculator code
     * @param type $expression
     * @return string
     */
    public function calcCode($expression) {
        if ($this->isExpression($expression)) {
            return preg_replace_callback('#\[([AN]{1,2})\.([0-9]+)\]#', array($this, 'convertExpression'), $expression);
        } else {
            return $expression;
        }
    }
    /**
     * convert expression
     * @param type $param
     * @return string
     */
    public function convertExpression($param) {
        $alphabet = (strpos($param[1], 'A')) === false ? '' : 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $alphabet .= (strpos($param[1], 'N')) === false ? '' : '0123456789';
        return $this->getRandomString($param[2], $alphabet);
    }
    /**
     * validate expression
     * @param type $string
     * @return boolean
     */
    public function isExpression($string) {
        return preg_match('#\[([AN]{1,2})\.([0-9]+)\]#', $string);
    }
    /**
     * get defalut patern
     * @return string
     */
    public function getDefaulPatern() {
        return 'REWARD-[N.4]-[AN.5]-[A.4]';
    }

}