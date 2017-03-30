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
 * Rewardpoints Spending Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Adminhtml_Reward_SpendingController extends Mage_Adminhtml_Controller_Action
{
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_RewardPoints_Adminhtml_SpendingController
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('rewardpoints/spending')
            ->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Spending Rates'),
                Mage::helper('adminhtml')->__('Spending Rate')
            );
        return $this;
    }
 
    /**
     * index action
     */
    public function indexAction()
    {
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Spending Rate'));
        $this->_initAction()
            ->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction()
    {
        $rateId     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('rewardpoints/rate')->load($rateId);

        if ($model->getId() || $rateId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('rate_data', $model);

            $this->loadLayout();
            
            $this->_setActiveMenu('rewardpoints/spending');

            $this->_addBreadcrumb(
                Mage::helper('adminhtml')->__('Spending Rates'),
                Mage::helper('adminhtml')->__('Spending Rate')
            );
            $this->_title($this->__('Reward Points'))
                ->_title($this->__('Spending Rate'));
            if ($model->getId()) {
                $this->_title($this->__('Edit Spending Rate #%s', $model->getId()));
            } else {
                $this->_title($this->__('New Spending Rate'));
            }

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('rewardpoints/adminhtml_spending_edit'))
                ->_addLeft($this->getLayout()->createBlock('rewardpoints/adminhtml_spending_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('rewardpoints')->__('Item does not exist')
            );
            $this->_redirect('*/*/');
        }
    }
 
    public function newAction()
    {
        $this->_forward('edit');
    }
 
    /**
     * save item action
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $model = Mage::getModel('rewardpoints/rate');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));
            
            try {
                if (!$model->getDirection()) {
                    $model->setDirection(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY);
                }
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__('Spending rate was successfully saved')
                );
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
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('rewardpoints')->__('Unable to find item to save')
        );
        $this->_redirect('*/*/');
    }
    
    /**
     * delete item action
     */
    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('rewardpoints/rate');
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Spending rate was successfully deleted')
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
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints');
    }
    
    public function massDeleteAction()
    {
        $rateIds = $this->getRequest()->getParam('rate');
        if(!is_array($rateIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Please select rate(s).'));
        } else {
            try {
                foreach ($rateIds as $rateId) {
                    Mage::getModel('rewardpoints/rate')->load($rateId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__('Total of %d record(s) were deleted.', count($rateIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
    
    public function massChangeStatusAction(){
        $rateIds = $this->getRequest()->getParam('rate');
        $status = $this->getRequest()->getParam('status');
        if(!is_array($rateIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpoints')->__('Please select rate(s).'));
        } else {
            try {
                foreach ($rateIds as $rateId) {
                    Mage::getModel('rewardpoints/rate')
                            ->load($rateId)
                            ->setData('status',$status)
                            ->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpoints')->__('Total of %d record(s) were updated.', count($rateIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
}
