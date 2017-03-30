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
 * RewardPointsRule Catalog Spending Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsRule_Adminhtml_Reward_Spending_CatalogController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/spending/catalog');
    }
    /**
     * init layout and set active for current menu
     * 
     * @return Magestore_RewardPointsRule_Adminhtml_Spending_CatalogController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/spending')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_title($this->__('Reward Points Rule'))
                ->_title($this->__('Catalog Spending Rule'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_catalog'));
        $this->renderLayout();
    }

    /**
     * view and edit item action, if item is new then view blank
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('rewardpointsrule/spending_catalog')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
            Mage::register('rule_data', $model);

            $this->_title($this->__('Reward Points Rule'))
                    ->_title($this->__('Manage rule'));
            if ($model->getId()) {
                $this->_title($model->getTitle());
            } else {
                $this->_title($this->__('New rule'));
            }

            $this->loadLayout();
            $this->_setActiveMenu('rewardpointsrule/rule');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule News'), Mage::helper('adminhtml')->__('Rule News'));

            $this->getLayout()->getBlock('head')
                    ->setCanLoadExtJs(true)
                    ->setCanLoadRulesJs(true);

            $this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_catalog_edit'))
                    ->_addLeft($this->getLayout()->createBlock('rewardpointsrule/adminhtml_spending_catalog_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('The item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * new action is create new item
     */
    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save action is save item
     */
    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('rewardpointsrule/spending_catalog')->load($this->getRequest()->getParam('id'));
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
                unset($data['rule']);
            }
            try {
                $model->loadPost($data)
                        ->setData('from_date', $data['from_date'])
                        ->setData('to_date', $data['to_date'])
                        ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpointsrule')->__('Rule was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Unable to find the item to save'));
        $this->_redirect('*/*/');
    }

    /**
     * delete action is delete item
     */
    public function deleteAction() {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('rewardpointsrule/spending_catalog');
                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rule was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $ruleIds = $this->getRequest()->getParam('rule');
        if(!is_array($ruleIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please select rule(s).'));
        } else {
            try {
                
                foreach ($ruleIds as $ruleId) {
                    Mage::getModel('rewardpointsrule/spending_catalog')->load($ruleId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpointsrule')->__('Total of %d record(s) were deleted.', count($ruleIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
    
    public function massChangeStatusAction(){
        $ruleIds = $this->getRequest()->getParam('rule');
        $status = $this->getRequest()->getParam('status');
        if(!is_array($ruleIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please select rule(s).'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    Mage::getModel('rewardpointsrule/spending_catalog')
                            ->load($ruleId)
                            ->setData('is_active',$status)
                            ->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpointsrule')->__('Total of %d record(s) were updated.', count($ruleIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
}
