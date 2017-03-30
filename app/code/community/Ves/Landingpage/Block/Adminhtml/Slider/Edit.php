<?php
 /*------------------------------------------------------------------------
  # Ves ContentTab Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Landingpage_Block_Adminhtml_Slider_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
		
       parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup  = 'ves_landingpage';
        $this->_controller  = 'adminhtml_slider';

        $this->_updateButton('save', 'label', Mage::helper('ves_landingpage')->__('Save Record'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_landingpage')->__('Delete Record'));
    
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
                   ->setSwitchUrl($this->getUrl('*/*/*/id/'.Mage::registry('slider_data')->get('slider_id'), array('store'=>null)))
           );
        }

        return parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
          $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
          $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }
    }
    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }

    /**
     * Function Get Header Text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $slider_id = Mage::registry('slider_data')->getData('slider_id');

        if ($slider_id) {
            return Mage::helper('ves_landingpage')->__("Venus Landing Page - Edit slider '%s'", $this->htmlEscape(Mage::registry('slider_data')->getData('caption_1')));
        } else {
            return Mage::helper('ves_landingpage')->__("Venus Landing Page - New slider");
        }
        
    }
}