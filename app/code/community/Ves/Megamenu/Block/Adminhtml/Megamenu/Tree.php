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

class Ves_Megamenu_Block_Adminhtml_Megamenu_Tree extends Mage_Adminhtml_Block_Template {

    var $children = array();

    public function __construct() {
        parent::__construct();
        $this->setTemplate('ves_megamenu/megamenu/tree.phtml');
        $this->setUseAjax(true);
    }

    protected function _prepareLayout() {
        $this->setChild('import_category_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Import Categories'),
                            'onclick' => "importCategories('" . $this->getUrl('*/*/importCategories') . "')",
                            'id' => 'add_subvesmegamenu_button'
                        ))
        );

        $this->setChild('import_menu_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Import Menus'),
                            'onclick' => "importMenus('" . $this->getUrl('*/*/importMenus') . "')",
                            'id' => 'add_importmenu_button'
                        ))
        );

        $this->setChild('delete_category_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Delete Categories'),
                            'onclick' => "deleteCategories('" . $this->getUrl('*/*/deleteCategories') . "', false)",
                            'id' => 'add_subvesmegamenu_button'
                        ))
        );

        $this->setChild('delete_all_button', $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label' => Mage::helper('catalog')->__('Delete All Menu Items'),
                            'onclick' => "deleteAllMenuItems('" . $this->getUrl('*/*/deleteAll') . "', false)",
                            'id' => 'add_subvesmegamenu_button'
                        ))
        );


        $this->setChild('store_switcher', $this->getLayout()->createBlock('adminhtml/store_switcher')
                        ->setSwitchUrl($this->getUrl('*/*/*'))
                        ->setTemplate('ves_megamenu/store/switcher.phtml')
        );

        $this->setChild('store_switcher_noaction', $this->getLayout()->createBlock('adminhtml/store_switcher')
                        ->setSwitchUrl($this->getUrl('*/*/*'))
                        ->setTemplate('ves_megamenu/store/switcher_noaction.phtml')
        );
        return parent::_prepareLayout();
    }

    public function getCloneLink() {
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id){
            return Mage::helper("adminhtml")->getUrl("*/adminhtml_megamenu/clone", array("store_id"=>$store_id));   
        }else{
            return Mage::helper("adminhtml")->getUrl("*/adminhtml_megamenu/clone");
        }
    }
    public function getStore() {
        if ($storeId = (int) $this->getRequest()->getParam('store_id'))
            return $storeId;
        elseif (Mage::getSingleton('admin/session')->getLastViewedStore())
            return Mage::getSingleton('admin/session')->getLastViewedStore();
        return $this->_getDefaultStoreId();
    }

    public function getRoot() {
        $root = Mage::registry('root');
        if (is_null($root)) {
            $storeId = (int) $this->getRequest()->getParam('store');
            $rootIds = null;
            if ($storeId) {
                $rootIds = Mage::getModel('ves_megamenu/megamenu')->getRootId($storeId);
            } else {
                $rootIds = Mage::getModel('ves_megamenu/megamenu')->getRootId(0);
            }
            $root = array();
            foreach ($rootIds as $rootId) {
                $root[] = Mage::getModel('ves_megamenu/megamenu')->load($rootId);
            }

            Mage::register('root', $root);
        }
        return $root;
    }

    protected function _getDefaultStoreId() {
        return Ves_Megamenu_Model_Megamenu::DEFAULT_STORE_ID;
    }

    public function getMegamenuCollection($id = null) {
        $storeId = $this->getStore();
        $storeId = (int)$storeId;
        $collection = $this->getData('megamenu_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('ves_megamenu/megamenu')->getCollection();
            if( $id != null ) {
                $collection->addFieldToFilter("parent_id", $id);
            }
            $collection->addStoreFilter($storeId);
            $collection->addOrder("position", "ASC");
            $this->setData('megamenu_collection', $collection);
        }

        return $collection;
    }
    public function getImportMenusButtonHtml() {
        return $this->getChildHtml('import_menu_button');
    }
    public function getDeleteAllButtonHtml() {
        return $this->getChildHtml('delete_all_button');
    }

    public function getImportCategoriesButtonHtml() {
        return $this->getChildHtml('import_category_button');
    }

    public function getDeleteCategoriesButtonHtml() {
        return $this->getChildHtml('delete_category_button');
    }

    public function getAddSubButtonHtml() {
        return $this->getChildHtml('add_sub_button');
    }

    public function getExpandButtonHtml() {
        return $this->getChildHtml('expand_button');
    }

    public function getCollapseButtonHtml() {
        return $this->getChildHtml('collapse_button');
    }

    public function getStoreSwitcherHtml() {
        return $this->getChildHtml('store_switcher');
    }
    public function getStoreSwitcherNoactionHtml() {
        return $this->getChildHtml('store_switcher_noaction');
    }
    /**
     * whethere parent has menu childrens
     */
    public function hasChild( $id ){
        return isset($this->children[$id]);
    }

    /**
     * get collection of menu childrens by parent ID.
     */
    public function getNodes( $id ){
        return $this->children[$id];
    }

    public function getTreeHtml($id=null) {
        $storeId = $this->getStore();
        $storeId = empty($storeId) ? 0 : $storeId;

        $childs = $this->getMegamenuCollection( $id );
        foreach($childs as $child ){
            if((int)$child->getParentId() > 0) {
                $this->children[$child->getParentId()][] = $child;
            }
            
        }

        $parent = 1 ;
        $output = $this->genTree( $parent, 1, $storeId );
        return $output;
    }

    public function genTree( $parent, $level, $store_id = 0 ){
        if( $this->hasChild($parent) ){
            $data = $this->getNodes( $parent );

            $t = $level == 1?" sortable":"";
            $output = '<ol class="level'.$level. $t.' ">';

            foreach( $data as $menu ){
                if($store_id){
                    $url  = Mage::helper("adminhtml")->getUrl("*/adminhtml_megamenu/index", array("id"=>$menu->getId(), "store_id"=>$store_id));   
                 }else{
                    $url  = Mage::helper("adminhtml")->getUrl("*/adminhtml_megamenu/index", array("id"=>$menu->getId()));
                }
                $class = "";
                $clone_link = "";
                if($menu->getParentId() == 1) {
                    $class = "root_item";
                    //$clone_link = '<a class="clone" rel="id_'.$menu->getId().'" href="javascript:;" onclick="cloneMenu('.$menu->getId().')">C</a> ';
                }
                $output .='<li id="list_'.$menu->getId().'" class="'.$class.'">
                <div><span class="disclose"><span></span></span>'.($menu->getTitle()).' (ID:'.$menu->getId().') '.$clone_link.'<a class="quickedit" rel="id_'.$menu->getId().'" href="'.$url .'">E</a><span class="quickdel" rel="id_'.$menu->getId().'">D</span></div>';
                if($menu['level'] >= 0) {
                    $output .= $this->genTree( $menu->getId(), $level+1, $store_id );
                }
                $output .= '</li>';
            }
            $output .= '</ol>';
            return $output;
        }
        return ;
    }
    /**
     * Retrieve currently edited product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getMegamenu() {
        return Mage::registry('current_megamenu');
    }

}