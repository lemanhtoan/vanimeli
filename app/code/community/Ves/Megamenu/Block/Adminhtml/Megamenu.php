<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Megamenu_Block_Adminhtml_Megamenu extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_megamenu';
        $this->_blockGroup = 'ves_megamenu';
        $this->_headerText = Mage::helper('ves_megamenu')->__('MegaMenu Manager');
        $this->_addButtonLabel = Mage::helper('ves_megamenu')->__('Add MegaMenu');
        parent::__construct();
    }

    protected function _prepareLayout() {
        $this->setChild('add_new_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_megamenu')->__('Add Megamenu'),
                'onclick'   => "setLocation('".$this->getUrl('*/*/add')."')",
                'class'   => 'add'
                ))
        );
        $this->setChild('importcsv',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_megamenu')->__('Import CSV'),
                'onclick'   => 'setLocation(\'' . $this->getImportUrl() .'\')',
                'class'   => 'import'
                ))
        );
        /**
         * Display store switcher if system has more one store
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->setChild('store_switcher',
                   $this->getLayout()->createBlock('adminhtml/store_switcher')
                   ->setUseConfirm(false)
                   ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
           );
       }
        $this->setChild('grid', $this->getLayout()->createBlock('ves_megamenu/adminhtml_megamenu_grid', 'megamenu.grid'));
        return parent::_prepareLayout();
    }

    private function getImportUrl() {
        return $this->getUrl('*/*/uploadCsv');
    } // end

    public function getAddNewButtonHtml() {
        return $this->getChildHtml('add_new_button');
    }

    public function getImportButtonHtml() {
        return $this->getChildHtml('importcsv');
    }

    public function getGridHtml() {
        return $this->getChildHtml('grid');
    }

    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }
}