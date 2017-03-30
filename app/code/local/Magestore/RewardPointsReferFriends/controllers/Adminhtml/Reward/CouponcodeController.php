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
 * Rewardpointsreferfriends Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Adminhtml_Reward_CouponcodeController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_RewardPointsReferFriends_Adminhtml_RewardpointsreferfriendsController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/specialrefer')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Special Offer Manager'), Mage::helper('adminhtml')->__('Special Offer Manager')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * print coupon code
     */
    public function newAction() {
        $this->_forward('edit');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/specialrefer');
    }

    public function massPrintAction() {
        $giftvoucherIds = $this->getRequest()->getParam('rewardpointsreferfriends');
        if ($giftvoucherIds && is_string($giftvoucherIds))
            $giftvoucherIds = explode(',', $giftvoucherIds);
        if (!is_array($giftvoucherIds) || !count($giftvoucherIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Customer(s)'));
            $this->_redirect('*/*/index');
        } else {
            $pdf = Mage::getModel('rewardpointsreferfriends/pdf_couponcode')->getPdf($giftvoucherIds);
            $this->_prepareDownloadResponse('couponcode_' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s') . '.pdf', $pdf->render(), 'application/pdf');
        }
    }

}
