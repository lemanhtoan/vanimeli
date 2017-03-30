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
 * Rewardpoints Report Grid Container With Filter Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Block_Adminhtml_Grid_Container extends Mage_Adminhtml_Block_Template
{
    /**
     * get input date format
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
    }
    
    /**
     * get current filter value by name
     * 
     * @param string $filterName
     * @return mixed
     */
    public function getFilter($filterName)
    {
        $filter = Mage::app()->getRequest()->getParam('filter');
        $data   = Mage::helper('adminhtml')->prepareFilterString($filter);
        return isset($data[$filterName]) ? $data[$filterName] : null;
    }
    
    /**
     * get js grid object name
     * 
     * @return string
     */
    public function getJsObjectName()
    {
        $gridBlock = $this->getChild('grid_content');
        return $gridBlock ? $gridBlock->getJsObjectName() : '';
    }
}
