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
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsRule Earning Catalog Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Block_Adminhtml_Earning_Catalog extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_earning_catalog';
        $this->_blockGroup = 'rewardpointsrule';
        $this->_headerText = Mage::helper('rewardpointsrule')->__('Catalog Earning Rule Manager');
        //$this->_applyRuleLabel = Mage::helper('rewardpointsrule')->__('Apply Rules');

        $this->_addButtonLabel = Mage::helper('rewardpointsrule')->__('Add Rule');
        
        $this->_addButton('applyRuleLabel', array(
            'label' => Mage::helper('rewardpointsrule')->__('Reindex Rules'),
            'onclick' => 'applyRules(\'' . $this->linkApply() . '\')',               
            'class' => '',
        ));
        

        parent::__construct();
    }

    /**
     * get link show apply rule popup
     *
     * @return type
     */
    public function linkApply() {
        $link = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/reward_earning_catalog/showApplyRule', array(
            'website' => $this->getRequest()->getParam('website'),                
        ));
        return $link;
    }

    /**
     * get image loading
     *
     * @return type
     */
    public function getImageLink() {
        return $this->getSkinUrl('images/transfer-ajax-loader.gif');
    }

    /**
     * get image stop applying
     *
     * @return type
     */
    public function getImageLinkStop() {
        return $this->getSkinUrl('images/ajax-loader.jpg');
    }

     /**
     * check rules were appiled
     *
     * @return boolean
     */
    public function checkDataImport() {

       if(Mage::getStoreConfig('rewardpointsrule/indexmanagement/flag')){
            return true;
       }
        return false;
    }

    /**
     * get applying rule link
     *
     * @return type
     */
    public function actionApplly() {
        $link = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/reward_earning_catalog/applyRuleAjax', array(
            'website' => $this->getWebsiteId(),            
        ));
        return $link;
    }

}
