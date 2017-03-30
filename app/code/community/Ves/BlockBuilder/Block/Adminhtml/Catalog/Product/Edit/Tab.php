<?php
 
class Ves_BlockBuilder_Block_Adminhtml_Catalog_Product_Edit_Tab extends Mage_Adminhtml_Block_Widget 
implements Mage_Adminhtml_Block_Widget_Tab_Interface {
 
    /**
     * Initialize block
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setProductId($this->getRequest()->getParam('id'));
        $this->setTemplate('ves_blockbuilder/catalog/product/tab.phtml');
        $this->setId('product_layout_builder_profile');
    }

    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Ves Product Layout Builder');
    }
    
    /**
     * Retrieve the title used by this tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Click here to choose product layout builder profile');
    }
     
    /**
     * Determines whether to display the tab
     * Add logic here to decide whether you want the tab to display
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }
     
    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }


    /**
     * Returns the current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }
    /**
     * Get Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getCurrentProfileId()
    {
        $product_id = $this->getProductId();
        $store = $this->getRequest()->getParam('store');
        $collection = Mage::getModel("ves_blockbuilder/block")->getCollection();
        $collection->addFieldToFilter("block_type", "product");
        $collection->addProductIdFilter($product_id, $store);


        $item = $collection->getFirstItem();
        if($item && $item->getId()) {
            return $item->getId();
        }
        return;
    }

    /**
     * Get Product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            if ($product = Mage::registry('product')) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = Mage::getSingleton('catalog/product');
            }
        }

        return $this->_productInstance;
    }


    public function getProductLayoutProfiles() {
        $collection = Mage::getModel("ves_blockbuilder/block")->getCollection();
        $collection->addFieldToFilter("block_type", "product");

        return $collection;
    }
 
}