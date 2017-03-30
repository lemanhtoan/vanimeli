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
 * Rewardpointsreferfriends Edit Form Content Tab Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardpointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{
     /**
     * prepare tab form's condion
     *
     * @return Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit_Tab_Form
     */
  protected function _prepareForm()
  {
      if ( Mage::getSingleton('adminhtml/session')->getFormData()){
          $data = Mage::getSingleton('adminhtml/session')->getFormData();
          $model = Mage::getModel('rewardpointsreferfriends/rewardpointsspecialrefer')
          		->load($data['offer_id'])
		  		->setData($data);
          Mage::getSingleton('adminhtml/session')->setFormData(null);
      } elseif ( Mage::registry('offer_data')){
          $model = Mage::registry('offer_data');
          $data = $model->getData();
      }
  	  
      $form = new Varien_Data_Form();
      $form->setHtmlIdPrefix('offer_');
      
      $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('adminhtml/promo_quote/newConditionHtml/form/offer_conditions_fieldset'));
      
      $fieldset = $form->addFieldset('conditions_fieldset', array('legend'=>Mage::helper('rewardpointsreferfriends')->__('Apply the rule only if the following conditions are met (leave blank for all orders)')))->setRenderer($renderer);
      
      $fieldset->addField('conditions','text',array(
      	'name'	=> 'conditions',
      	'label'	=> Mage::helper('rewardpointsreferfriends')->__('Conditions'),
      	'title'	=> Mage::helper('rewardpointsreferfriends')->__('Conditions'),
      	'required'	=> true,
	  ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));
      
      $form->setValues($data);
      $this->setForm($form);
      return parent::_prepareForm();
  }
}