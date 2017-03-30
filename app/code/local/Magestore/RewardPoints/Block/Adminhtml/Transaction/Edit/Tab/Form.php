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
 * Rewardpoints Transaction Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare tab form's information
     *
     * @return Magestore_RewardPoints_Block_Adminhtml_Transaction_Edit_Tab_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        
        if (Mage::getSingleton('adminhtml/session')->getRewardPointsData()) {
            $model = new Varien_Object(Mage::getSingleton('adminhtml/session')->getRewardPointsData());
            Mage::getSingleton('adminhtml/session')->setRewardPointsData(null);
        } elseif (Mage::registry('transaction_data')) {
            $model = Mage::registry('transaction_data');
        }
        $fieldset = $form->addFieldset('rewardpoints_form', array(
            'legend'=>Mage::helper('rewardpoints')->__('Transaction Information')
        ));
        
        if ($model->getId()) {
            $fieldset->addField('title', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Transaction Title'),
                'text'      => $model->getTitleHtml(),
            ));
            $fieldset->addField('customer_email', 'note', array(
                        'label'     => Mage::helper('rewardpoints')->__('Customer Email'),
                        'text'      => sprintf('<a target="_blank" href="%s">%s</a>',
                                $this->getUrl('adminhtml/customer/edit', array('id' => $model->getCustomerId())),
                                $model->getCustomerEmail()
                            ),
            ));
            
            Mage::dispatchEvent('rewardpoints_transaction_view_detail', array(
                'fieldset' => $fieldset,'modeltransaction' => $model
            ));
            
            try {
                $actionLabel = $model->getActionInstance()->getActionLabel();
            } catch (Exception $e) {
                Mage::logException($e);
                $actionLabel = '';
            }
            $fieldset->addField('action', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Action'),
                'text'      => $actionLabel,
            ));
            
            $statusHash = $model->getStatusHash();
            $fieldset->addField('status', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Status'),
                'text'      => isset($statusHash[$model->getStatus()])
                    ? '<strong>' . $statusHash[$model->getStatus()] . '</strong>' : '',
            ));
            
            $fieldset->addField('point_amount', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Points'),
                'text'      => '<strong>' . Mage::helper('rewardpoints/point')->format(
                        $model->getPointAmount(),
                        $model->getStoreId()
                    ) . '</strong>',
            ));
            
            $fieldset->addField('point_used', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Point Used'),
                'text'      => Mage::helper('rewardpoints/point')->format(
                        $model->getPointUsed(),
                        $model->getStoreId()
                    ),
            ));
            
            $fieldset->addField('created_time', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Created time'),
                'text'      => $this->formatTime($model->getCreatedTime(),
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                        true
                    ),
            ));
            
            $fieldset->addField('updated_time', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Updated At'),
                'text'      => $this->formatTime($model->getUpdatedTime(),
                        Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                        true
                    ),
            ));
            
            if ($model->getExpirationDate()) {
                $fieldset->addField('expiration_date', 'note', array(
                    'label'     => Mage::helper('rewardpoints')->__('Expire On'),
                    'text'      => '<strong>' . $this->formatTime($model->getExpirationDate(),
                            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM,
                            true
                        ) . '</strong>',
                ));
            }
            
            $fieldset->addField('store_id', 'note', array(
                'label'     => Mage::helper('rewardpoints')->__('Store View'),
                'text'      => Mage::app()->getStore($model->getStoreId())->getName(),
            ));
            
            return parent::_prepareForm();
        }
        
        $fieldset->addField('customer_email', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Customer'),
            'title'     => Mage::helper('rewardpoints')->__('Customer'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'customer_email',
            'readonly'  => true,
            'after_element_html' => '</td><td class="label"><a href="javascript:showSelectCustomer()" title="'
                . Mage::helper('rewardpoints')->__('Select') . '">'
                . Mage::helper('rewardpoints')->__('Select') . '</a>'
                . '<script type="text/javascript">
                    function showSelectCustomer() {
                        new Ajax.Request("'
                    . $this->getUrl('*/*/customer',array('_current'=>true))
                    . '", {
                            parameters: {form_key: FORM_KEY, selected_customer_id: $("customer_id").value || 0},
                            evalScripts: true,
                            onSuccess: function(transport) {
                                TINY.box.show("");
                                $("tinycontent").update(transport.responseText);
                            }
                        });
                    }
                </script>'
        ));
        
        $fieldset->addField('customer_id', 'hidden', array('name'  => 'customer_id'));
        
        $fieldset->addField('point_amount', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Points'),
            'title'     => Mage::helper('rewardpoints')->__('Points'),
            'name'      => 'point_amount',
            'required'  => true,
        ));
        
        $fieldset->addField('title', 'textarea', array(
            'label'     => Mage::helper('rewardpoints')->__('Transaction Title'),
            'title'     => Mage::helper('rewardpoints')->__('Transaction Title'),
            'name'      => 'title',
            'style'     => 'height: 5em;'
        ));
        
        $fieldset->addField('expiration_day', 'text', array(
            'label'     => Mage::helper('rewardpoints')->__('Points expire after'),
            'title'     => Mage::helper('rewardpoints')->__('Points expire after'),
            'name'      => 'expiration_day',
            'note'      => Mage::helper('rewardpoints')->__('day(s) since the transaction date. If empty or zero, there is no limitation.')
        ));
        
        $form->setValues($model->getData());
        return parent::_prepareForm();
    }
}
