<?php

class NWT_Languagepack_Block_Adminhtml_Languagepack_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('languagepack_form', array('legend'=>Mage::helper('languagepack')->__('Item information')));
     
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('languagepack')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('languagepack')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('languagepack')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('languagepack')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('languagepack')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('languagepack')->__('Content'),
          'title'     => Mage::helper('languagepack')->__('Content'),
          'style'     => 'width:700px; height:500px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getLanguagepackData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getLanguagepackData());
          Mage::getSingleton('adminhtml/session')->setLanguagepackData(null);
      } elseif ( Mage::registry('languagepack_data') ) {
          $form->setValues(Mage::registry('languagepack_data')->getData());
      }
      return parent::_prepareForm();
  }
}