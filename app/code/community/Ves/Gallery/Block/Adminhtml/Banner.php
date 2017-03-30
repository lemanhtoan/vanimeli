<?php

class Ves_Gallery_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
		
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'ves_gallery';
        $this->_headerText = Mage::helper('ves_gallery')->__('Gallery Manager');
		
        parent::__construct();

        $this->setTemplate('ves_gallery/banner.phtml');
		
		
    }

    protected function _prepareLayout() {
	
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_gallery')->__('Add Record'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
        /**
         * Display store switcher if system has more one store
         */
        //if (!Mage::app()->isSingleStoreMode()) {
        //    $this->setChild('store_switcher',
        //             $this->getLayout()->createBlock('adminhtml/store_switcher')
        //             ->setUseConfirm(false)
        //             ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
        //     );
        // }
        $this->setChild('grid', $this->getLayout()->createBlock('ves_gallery/adminhtml_banner_grid', 'banner.grid'));
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