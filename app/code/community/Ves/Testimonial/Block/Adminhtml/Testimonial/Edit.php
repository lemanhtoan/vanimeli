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

class Ves_Testimonial_Block_Adminhtml_Testimonial_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup  = 'ves_testimonial';
        $this->_controller  = 'adminhtml_testimonial';

        $this->_updateButton('save', 'label', Mage::helper('ves_testimonial')->__('Save Record'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_testimonial')->__('Delete Record'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    protected function _prepareLayout() {
         /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                   $this->getLayout()->createBlock('adminhtml/store_switcher')
                   ->setUseConfirm(false)
                   ->setSwitchUrl($this->getUrl('*/*/*/id/'.Mage::registry('testimonial_data')->getId(), array('store'=>null)))
           );
        }

        return parent::_prepareLayout();
    }
    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }
    public function getHeaderText()
    {
        if( Mage::registry('testimonial_data')->getId() ) {
    			return Mage::helper('ves_testimonial')->__("Edit Record");
    		}else {
    			return Mage::helper('ves_testimonial')->__("Add New Testimonial");
    		}
	}
}