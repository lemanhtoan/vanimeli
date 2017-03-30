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
 * RewardPointsReferFriends Model Total Earning
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */

class Magestore_RewardPointsReferFriends_Model_Total_Pdf_Earning extends Mage_Sales_Model_Order_Pdf_Total_Default
{
        public function getTotalsForDisplay() {
            parent::getTotalsForDisplay();
            $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
            if ($this->getAmountPrefix()) {
                $amount = $this->getAmountPrefix().$amount;
            }
            $fontsize = $this->getFontSize() ? $this->getFontSize(): 7;
            $totals = array(array(
                'label'=>'Offer Discount',
                'amount'=>$amount,
                'font_size'=>$fontsize,
            ));
            return $totals;
        }
}