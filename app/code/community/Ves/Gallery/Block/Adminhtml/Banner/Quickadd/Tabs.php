<?php

class Ves_Gallery_Block_Adminhtml_Banner_Quickadd_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	
 
	
    public function __construct()
    {
        parent::__construct();
        $this->setId('slider_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ves_gallery')->__('Banner Information'));
		 
        parent::__construct();
        $this->setTemplate('ves_gallery/quickadd/form.phtml');
 
	
    }

    protected function _beforeToHtml()
    {
      
		$this->addTab('general_section', array(
            'label'     => Mage::helper('ves_gallery')->__('General Information'),
            'title'     => Mage::helper('ves_gallery')->__('General Information'),
             'content'   => $this->getLayout()->createBlock('ves_gallery/adminhtml_banner_quickadd_tab_form')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }
}