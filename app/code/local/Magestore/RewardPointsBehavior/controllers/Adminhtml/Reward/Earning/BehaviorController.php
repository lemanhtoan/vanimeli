<?php

class Magestore_RewardPointsBehavior_Adminhtml_Reward_Earning_BehaviorController extends Mage_Adminhtml_Controller_Action {

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/earning/behavior');
    }
    
    /**
     * init layout and set active for current menu
     *
     * @return Magestore_RewardPointsBehavior_Adminhtml_BehaviorController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/earning')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_title($this->__('RewardPoints Behavior'))
                ->_title($this->__('Manage customer behavior earning rule'));
        $current = 'rewardpoints';
        $this->getRequest()->setParam('section', $current);
//        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        Mage::getSingleton('adminhtml/config_data')
                ->setSection($current)
                ->setWebsite($website)
                ->setStore($store);

        $configFields = Mage::getSingleton('adminhtml/config');

        $sections = $configFields->getSections($current);
        $section = $sections->$current;
        $hasChildren = $configFields->hasChildren($section, $website, $store);
        if (!$hasChildren && $current) {
            $this->_redirect('*/*/', array('website' => $website, 'store' => $store));
        }
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rewardpointsbehavior/adminhtml_earning_behavior')->initForm())
                ->_addLeft($this->getLayout()->createBlock('rewardpointsbehavior/adminhtml_earning_behavior_tabs'));
        $this->_addJs($this->getLayout()->createBlock('adminhtml/template')->setTemplate('system/config/js.phtml'));
        $this->renderLayout();
    }

    /**
     * save item action
     */
    public function saveAction() {
        /* @var $session Mage_Adminhtml_Model_Session */
        $session = Mage::getSingleton('adminhtml/session');
        $groups = $this->getRequest()->getPost('groups');
        try {
            $this->_saveSection();
            $section = $this->getRequest()->getParam('section');
            $website = $this->getRequest()->getParam('website');
            $store = $this->getRequest()->getParam('store');
            Mage::getModel('adminhtml/config_data')
                    ->setSection($section)
                    ->setWebsite($website)
                    ->setStore($store)
                    ->setGroups($groups)
                    ->save();

            // reinit configuration
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();

            // website and store codes can be used in event implementation, so set them as well
            Mage::dispatchEvent("admin_system_config_changed_section_{$section}", array('website' => $website, 'store' => $store)
            );
            $session->addSuccess(Mage::helper('adminhtml')->__('The configuration has been saved.'));
        } catch (Mage_Core_Exception $e) {
            foreach (explode("\n", $e->getMessage()) as $message) {
                $session->addError($message);
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('adminhtml')->__('An error occurred while saving this configuration:') . ' ' . $e->getMessage());
        }
        $this->_saveState($this->getRequest()->getPost('config_state'));
        $this->_redirect('*/*/', array('_current' => array('section', 'website', 'store')));
    }

    /**
     * get section
     */
    protected function _saveSection() {
        $method = '_save' . uc_words($this->getRequest()->getParam('section'), '');
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * save state
     * @param array $configState
     * @return booleand
     */
    protected function _saveState($configState = array()) {
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }

        return true;
    }

    /**
     * state section
     */
    public function stateAction() {
        if ($this->getRequest()->getParam('isAjax') == 1 && $this->getRequest()->getParam('container') != '' && $this->getRequest()->getParam('value') != '') {

            $configState = array(
                $this->getRequest()->getParam('container') => $this->getRequest()->getParam('value')
            );
            $this->_saveState($configState);
            $this->getResponse()->setBody('success');
        }
    }

}
