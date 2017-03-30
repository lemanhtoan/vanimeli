<?php
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_Adminhtml_Group_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('group_form');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('ves_testimonial')->__('Group Tabs Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form_section', array(
            'label'     => Mage::helper('ves_testimonial')->__('General Information'),
            'title'     => Mage::helper('ves_testimonial')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('ves_testimonial/adminhtml_group_edit_tab_form')->toHtml(),
        ));
        return parent::_beforeToHtml();
    }
}