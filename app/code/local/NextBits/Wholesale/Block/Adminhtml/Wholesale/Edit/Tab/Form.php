<?php

class NextBits_Wholesale_Block_Adminhtml_Wholesale_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('wholesale_form', array('legend'=>Mage::helper('wholesale')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('wholesale')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('wholesale')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('wholesale')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('wholesale')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('wholesale')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('wholesale')->__('Content'),
          'title'     => Mage::helper('wholesale')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getWholesaleData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getWholesaleData());
          Mage::getSingleton('adminhtml/session')->setWholesaleData(null);
      } elseif ( Mage::registry('wholesale_data') ) {
          $form->setValues(Mage::registry('wholesale_data')->getData());
      }
      return parent::_prepareForm();
  }
}