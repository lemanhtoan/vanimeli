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
 * RewardPoints Image Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Image extends Magestore_RewardPoints_Block_Template
{
    protected $_rewardPointsHtml = null;
    protected $_rewardAnchorHtml = null;
    
    /**
     * prepare block's layout
     *
     * @return Magestore_RewardPoints_Block_Image
     */
    public function _prepareLayout()
    {
        $this->setTemplate('rewardpoints/image.phtml');
        return parent::_prepareLayout();
    }
    
    /**
     * Render block HTML, depend on mode of name showed
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getIsAnchorMode()) {
            if (is_null($this->_rewardAnchorHtml)) {
                $html = parent::_toHtml();
                if ($html) {
                    $this->_rewardAnchorHtml = $html;
                } else {
                    $this->_rewardAnchorHtml = '';
                }
            }
            return $this->_rewardAnchorHtml;
        } else {
            if (is_null($this->_rewardPointsHtml)) {
                $html = parent::_toHtml();
                if ($html) {
                    $this->_rewardPointsHtml = $html;
                } else {
                    $this->_rewardPointsHtml = '';
                }
            }
            return $this->_rewardPointsHtml;
        }
    }
    
    /**
     * get Policy Link for reward points system
     * 
     * @return string
     */
    public function getPolicyUrl()
    {
        return Mage::helper('rewardpoints/policy')->getPolicyUrl();
    }
}
