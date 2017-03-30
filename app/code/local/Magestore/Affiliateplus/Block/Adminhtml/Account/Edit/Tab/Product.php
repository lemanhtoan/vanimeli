<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Product extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        parent::__construct();
        $this->setId('productgrid');
        $this->setDefaultSort('product_id');
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column) {
        return parent::_addColumnFilterToCollection($column);
    }

    //return category collection filtered by store
    protected function _prepareCollection() {
        $accountId = $this->getRequest()->getParam('id');
        $collection = Mage::getModel('affiliateplus/transaction')->getCollection();

        //event to join other transaction
        Mage::dispatchEvent('affiliateplus_adminhtml_join_transaction_other_table', array('collection' => $collection));

        $collection->addFieldToFilter('account_id', $accountId);

        if ($storeId = $this->getRequest()->getParam('store'))
            $collection->addFieldToFilter('store_id', $storeId);

        $collection->getSelect()
                ->columns(array('customer_email' => 'if (main_table.customer_email="", "N/A", main_table.customer_email)'))
                ->columns(array('order_number' => 'if (main_table.order_number="", "N/A", main_table.order_number)'))
                ->columns(array('order_item_names' => 'if (main_table.order_item_names IS NULL, "N/A", main_table.order_item_names)'))
        ;

        $this->setCollection($collection);

        //event to join other transaction
        Mage::dispatchEvent('affiliateplus_adminhtml_after_set_transaction_collection', array('grid' => $this, 'account_id' => $accountId, 'store' => $storeId));

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_products', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_products',
            'values'            => $this->_getSelectedProducts(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('affiliateplus')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('affiliateplus')->__('Name'),
            'index'     => 'name'
        ));
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('affiliateplus')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('product_status', array(
            'header'    => Mage::helper('affiliateplus')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('affiliateplus')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('affiliateplus')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('affiliateplus')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));

//        $this->addColumn('position', array(
//            'header'            => Mage::helper('affiliateplus')->__('Sort Order'),
//            'name'              => 'position',
//            'index'             => 'position',
//            'width'             => '80px',
//            'width'             => 100,
//            'editable'          => true,
//            'filter'            => false,
//        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/productGrid', array('_current' => true,'id'=>$this->getRequest()->getParam('id')));
    }

    public function getRowUrl($row) {
        $id = $row->getTransactionId();
        return $this->getUrl('adminhtml/affiliateplus_transaction/view', array(
                    'id' => $id,
        ));
    }

    protected function _getStore() {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

}
