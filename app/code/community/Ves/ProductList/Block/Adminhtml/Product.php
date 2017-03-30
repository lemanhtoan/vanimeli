<?php

/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct() {
        $this->_controller = 'adminhtml_product';
        $this->_blockGroup = 'productlist';
        $this->_headerText = Mage::helper('productlist')->__("Manager Rule Products");

        parent::__construct();
    }

    protected function _prepareLayout() {
        $this->_removeButton('add');
        $id     = $this->getRequest()->getParam('id');
        $this->_addButton('back', array('label' => Mage::helper('catalog')->__('Back'), 'onclick' => "setLocation('{$this->getUrl('*/productlist/index') }')", 'class' => 'back'));

        $this->_addButton('save_position', array('label' => Mage::helper('productlist')->__('Save Position'), 'onclick' => "savePosition('{$this->getUrl('*/*/savePosition', array('id'=>$id)) }')", 'class' => 'save'));

        return parent::_prepareLayout();
    }


}