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
 * RewardPointsRule Earning Catalog Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Adminhtml_Reward_Spending_IndividualController extends Mage_Adminhtml_Controller_Action {

    /**
     * xuanbinh
     * fix access denied privilege ACL
     * @return type
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/spending/individual');
    }
    
    /**
     * init layout and set active for current menu
     * 
     * @return Magestore_RewardPointsRule_Adminhtml_Earning_CatalogController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/spending')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Individual Reward Points'), Mage::helper('adminhtml')->__('Individual Reward Points'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_title($this->__('Reward Points'))
                ->_title($this->__('Individual Reward Points'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_individual'));
        $this->renderLayout();
    }

    /**
     * Get specified tab grid
     */
    public function gridAction() {
        $this->loadLayout();
        $this->getResponse()->setBody(
                $this->getLayout()
                        ->createBlock('rewardpointsrule/adminhtml_spending_individual_grid')
                        ->toHtml()
        );
    }

    public function ajaxSaveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $id = isset($data['entity']) ? (int) $data['entity'] : null;
            $point = isset($data['value']) ? $data['value'] : null;
            if(strtolower($point) == 'empty') $point = '';
            $out = array(
                'message' => '',
                'value' => $point
            );
            if($point!='' && (!is_numeric($point) || $point<0)){
                $out['message'] = Mage::helper('rewardpointsrule')->__('Please enter a valid point amount.');
            }else if(!$id) {
                $out['message'] = Mage::helper('rewardpointsrule')->__('Cannot find product to save.');
            } else {
                try {
                    $product = Mage::getModel('catalog/product')->load($id);
                    if ($product && $product->getId()) {
                        $product->setRewardpointsSpend($point)
                                ->save();
                        $out['value'] = Mage::getModel('catalog/product')->load($id)->getRewardpointsSpend();
                    } else {
                        $out['message'] = Mage::helper('rewardpointsrule')->__('This product doesn\'t exists.');
                    }
                } catch (Exception $e) {
                    $out['message'] = $e->getMessage();
                }
            }
            $this->getResponse()->setBody(json_encode($out));
        }
    }
    public function massChangePointAction(){
        $productIds = $this->getRequest()->getParam('product');
        $point = $this->getRequest()->getParam('point');
        if(strtolower($point) == 'empty') $point = '';
        if($point!='' && (!is_numeric($point) || $point < 0)){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please enter a valid point amount.'));
        }else if(!is_array($productIds) || count($productIds) == 0) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please select product(s).'));
        } else {
            try {
                Mage::getResourceModel('catalog/product_action')->updateAttributes($productIds, array('rewardpoints_spend' => $point), 0);
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpointsrule')->__('Update spending point successfully.')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
}
