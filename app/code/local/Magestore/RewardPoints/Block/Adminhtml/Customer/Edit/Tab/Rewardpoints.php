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
 * Rewardpoints Tab on Customer Edit Form Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_Rewardpoints
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected $_rewardAccount = null;
    
    /**
     * get Current Reward Account Model
     * 
     * @return Magestore_RewardPoints_Model_Customer
     */
    public function getRewardAccount()
    {
        if (is_null($this->_rewardAccount)) {
            $customerId = $this->getRequest()->getParam('id');
            $this->_rewardAccount = Mage::getModel('rewardpoints/customer')
                ->load($customerId, 'customer_id');
        }
        return $this->_rewardAccount;
    }
    
    /**
     * prepare tab form's information
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Customer_Edit_Tab_Rewardpoints
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rewardpoints_');
        $this->setForm($form);
        
        $fieldset = $form->addFieldset('rewardpoints_form', array(
            'legend' => Mage::helper('rewardpoints')->__('Reward Points Information')
        ));
        
        $fieldset->addField('point_balance', 'note', array(
            'label' => Mage::helper('rewardpoints')->__('Available Points Balance'),
            'title' => Mage::helper('rewardpoints')->__('Available Points Balance'),
            'text'  => '<strong>' . Mage::helper('rewardpoints/point')->format(
                $this->getRewardAccount()->getPointBalance()) . '</strong>',
        ));
        
        $fieldset->addField('holding_point', 'note', array(
            'label' => Mage::helper('rewardpoints')->__('On Hold Points Balance'),
            'title' => Mage::helper('rewardpoints')->__('On Hold Points Balance'),
            'text'  => '<strong>' . Mage::helper('rewardpoints/point')->format(
                $this->getRewardAccount()->getHoldingBalance()) . '</strong>',
        ));
        
        $fieldset->addField('spent_point', 'note', array(
            'label' => Mage::helper('rewardpoints')->__('Spent Points'),
            'title' => Mage::helper('rewardpoints')->__('Spent Points'),
            'text'  => '<strong>' . Mage::helper('rewardpoints/point')->format(
                $this->getRewardAccount()->getSpentBalance()) . '</strong>',
        ));
        
        $fieldset->addField('change_balance', 'text', array(
            'label' => Mage::helper('rewardpoints')->__('Change Balance'),
            'title' => Mage::helper('rewardpoints')->__('Change Balance'),
            'name'  => 'rewardpoints[change_balance]',
            'note'  => Mage::helper('rewardpoints')->__('Add or subtract customer\'s balance. For ex: 99 or -99 points.'),
        ));
        
        $fieldset->addField('change_title', 'textarea', array(
            'label' => Mage::helper('rewardpoints')->__('Change Title'),
            'title' => Mage::helper('rewardpoints')->__('Change Title'),
            'name'  => 'rewardpoints[change_title]',
            'style' => 'height: 5em;'
        ));
        
        $fieldset->addField('expiration_day', 'text', array(
            'label' => Mage::helper('rewardpoints')->__('Points Expire On'),
            'title' => Mage::helper('rewardpoints')->__('Points Expire On'),
            'name'  => 'rewardpoints[expiration_day]',
            'note'  => Mage::helper('rewardpoints')->__('day(s) since the transaction date. If empty or zero, there is no limitation.')
        ));
        
        $fieldset->addField('admin_editing', 'hidden', array(
            'name'  => 'rewardpoints[admin_editing]',
            'value' => 1,
        ));
        
        $fieldset->addField('is_notification', 'checkbox', array(
            'label' => Mage::helper('rewardpoints')->__('Update Points Subscription'),
            'title' => Mage::helper('rewardpoints')->__('Update Points Subscription'),
            'name'  => 'rewardpoints[is_notification]',
            'checked'   => $this->getRewardAccount()->getIsNotification(),
            'value' => 1,
        ));
        
        $fieldset->addField('expire_notification', 'checkbox', array(
            'label' => Mage::helper('rewardpoints')->__('Expire Transaction Subscription'),
            'title' => Mage::helper('rewardpoints')->__('Expire Transaction Subscription'),
            'name'  => 'rewardpoints[expire_notification]',
            'checked'   => $this->getRewardAccount()->getExpireNotification(),
            'value' => 1,
        ));
        
        $fieldset = $form->addFieldset('rewardpoints_history_fieldset', array(
            'legend' => Mage::helper('rewardpoints')->__('Transaction History')
        ))->setRenderer($this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')->setTemplate(
            'rewardpoints/history.phtml'
        ));
        
        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        return Mage::helper('rewardpoints')->__('Reward Points');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return Mage::helper('rewardpoints')->__('Reward Points');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
