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
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Spending Grid Renderer Money Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Spending_Renderer_Money
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * @param Varien_Object $row
     * @return mixed
     */
    public function render(Varien_Object $row)
    {
        if ($row->getDirection() == Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY) {
            return Mage::app()->getStore()->getBaseCurrency()->format($row->getMoney());
        } else {
            $result = new Varien_Object(array(
                'value' => ''
            ));
            Mage::dispatchEvent('rewardpoints_adminhtml_spending_rate_grid_renderer', array(
                'row'   => $row,
                'result'=> $result
            ));
            return $result->getData('value');
        }
    }
}
