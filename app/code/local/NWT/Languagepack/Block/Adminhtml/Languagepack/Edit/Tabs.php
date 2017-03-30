<?php

class NWT_Languagepack_Block_Adminhtml_Languagepack_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('languagepack_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('languagepack')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('languagepack')->__('Item Information'),
          'title'     => Mage::helper('languagepack')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('languagepack/adminhtml_languagepack_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}