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
 * Rewardpointsreferfriends Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_Block_Product_Send extends Mage_Sendfriend_Block_Send
{
    /**
     * construct
     * set message default for email
     */
    function _construct() {
        $data = array();
        $data['sender'] = array();
        $data['sender']['message'] = Mage::getBlockSingleton('rewardpointsreferfriends/rewardpointsreferfriends')->getEmailContent();
        $this->setFormData($data);
        parent::_construct();
    }
    /**
     * Get send url
     * @return type
     */
    public function getSendUrl()
    {
        return Mage::getUrl('*/*/send', array(
            'id'     => $this->getProductId(),
            'cat_id' => $this->getCategoryId()
        ));
    }
    /**
     * overide canSend to send email
     * @return boolean
     */
    public function canSend(){
        return true;
    }
    
}