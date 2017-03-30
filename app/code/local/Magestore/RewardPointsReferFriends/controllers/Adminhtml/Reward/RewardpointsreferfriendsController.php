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
class Magestore_RewardPointsReferFriends_Adminhtml_Reward_RewardpointsreferfriendsController extends Mage_Adminhtml_Controller_Action {

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
     * view and edit item action
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $store = $this->getRequest()->getParam('store');
        $model = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->setStoreId($store)
                ->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            $model->getConditions()->setJsFormObject('offer_conditions_fieldset');
            $model->getActions()->setJsFormObject('offer_actions_fieldset');
            Mage::register('offer_data', $model);

            $this->_title($this->__('Special Offer'))
                    ->_title($this->__('Manage Special Offer'));
            if ($model->getId()) {
                $this->_title($model->getTitle());
            } else {
                $this->_title($this->__('New offer'));
            }

            $this->loadLayout();
            $this->_setActiveMenu('rewardpointsreferfriends/rewardpointsreferfriends');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Offer Manager'), Mage::helper('adminhtml')->__('Offer Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Offer News'), Mage::helper('adminhtml')->__('Offer News'));

            $this->getLayout()->getBlock('head')
                    ->setCanLoadExtJs(true)
                    ->setCanLoadRulesJs(true);
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
		$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }   

            $this->_addContent($this->getLayout()->createBlock('rewardpointsreferfriends/adminhtml_rewardpointsreferfriends_edit'))
                    ->_addLeft($this->getLayout()->createBlock('rewardpointsreferfriends/adminhtml_rewardpointsreferfriends_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsreferfriends')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->load($this->getRequest()->getParam('id'));
            $store = $this->getRequest()->getParam('store', 0);
            //prepare data
            $data = $this->_filterDates($data, array('from_date', 'to_date'));
            if (!$data['from_date'])
                $data['from_date'] = null;
            if (!$data['to_date'])
                $data['to_date'] = null;
            if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                if (isset($rules['actions'])) {
                    $data['actions'] = $rules['actions'];
                }
                unset($data['rule']);
            }
            //date and categories
            $model->setStoreId($store);
            $model->loadPost($data)->setData('from_date', $data['from_date'])->setData('to_date', $data['to_date']);
            try {
//                $data['conditions'] = $data['rule']['conditions'];
                $model->setData($data);
                $model->setId($this->getRequest()->getParam('id'));
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpointsreferfriends')->__('Special Offer was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsreferfriends')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Item was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        $rewardpointsreferfriendsIds = $this->getRequest()->getParam('rewardpointsreferfriends');
        if (!is_array($rewardpointsreferfriendsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($rewardpointsreferfriendsIds as $rewardpointsreferfriendsId) {
                    $rewardpointsreferfriends = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')->load($rewardpointsreferfriendsId);
                    $rewardpointsreferfriends->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($rewardpointsreferfriendsIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        $rewardpointsreferfriendsIds = $this->getRequest()->getParam('rewardpointsreferfriends');
        if (!is_array($rewardpointsreferfriendsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($rewardpointsreferfriendsIds as $rewardpointsreferfriendsId) {
                    Mage::getSingleton('rewardpointsreferfriends/rewardpointsspecialrefer')
                            ->load($rewardpointsreferfriendsId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($rewardpointsreferfriendsIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/specialrefer');
    }

}
