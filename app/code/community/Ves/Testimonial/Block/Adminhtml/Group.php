<?php
 /*------------------------------------------------------------------------
  # Ves Deals Module 
  # ------------------------------------------------------------------------
  # author:    Ves.Com
  # copyright: Copyright (C) 2012 http://www.ves.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.ves.com
  # Technical Support:  http://www.ves.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_Adminhtml_Group extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
    
        $this->_controller = 'adminhtml_testimonialgroup';
        $this->_blockGroup = 'ves_testimonial';
        $this->_headerText = Mage::helper('ves_testimonial')->__('Testimonial Manager');

        parent::__construct();
        $this->setTemplate('ves_testimonial/group.phtml');
        $helper =  Mage::helper('ves_testimonial/data');
        /*End init meida files*/
        $mediaHelper =  Mage::helper('ves_testimonial/media');  
    }

    public function listAssign(){
      $module = 0;
     // $items = $this->getListProducts();
        $menus = array();
        $menus["grouptabs"] =  array("link"=>$this->getUrl('*/adminhtml_testimonialgroup/index'),"title"=>$this->__("Group Testimonial"));
        $menus["tabs"] =  array("link"=>$this->getUrl('*/*/index'),"title"=>$this->__("Testimonial"));
        
        $this->assign( "menus", $menus);
        $this->assign( "menu_active", "group");
        $this->assign( "module", $module++);
    }

    public function getEffectConfig( $key ){
      return $this->getConfig( $key, "effect_setting" );
    }
    /**
     * get value of the extension's configuration
     *
     * @return string
     */
    function getConfig( $key, $panel='ves_testimonial' ){
      if(isset($this->_config[$key])) {
        return $this->_config[$key];
      } else {
        return Mage::getStoreConfig("ves_testimonial/$panel/$key");
      }
    }

    protected function _prepareLayout() {
  
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_testimonial')->__('Add Record'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
        $this->setChild('grid', $this->getLayout()->createBlock('ves_testimonial/adminhtml_group_grid', 'group.grid'));
        return parent::_prepareLayout();
    }

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }
    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }
    //public function getStoreSwitcherHtml() {
     //   return $this->getChildHtml('store_switcher');
    //}
}