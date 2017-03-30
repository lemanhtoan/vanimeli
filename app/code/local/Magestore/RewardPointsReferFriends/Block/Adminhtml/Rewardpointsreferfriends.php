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
 * Rewardpointsreferfriends Adminhtml Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_rewardpointsreferfriends';
        $this->_blockGroup = 'rewardpointsreferfriends';
        $this->_headerText = Mage::helper('rewardpointsreferfriends')->__('Special Offer Management');
        $this->_addButtonLabel = Mage::helper('rewardpointsreferfriends')->__('Add Special Offer');
        parent::__construct();
    }
}