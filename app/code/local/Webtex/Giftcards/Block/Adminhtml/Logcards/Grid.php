<?php
class Webtex_Giftcards_Block_Adminhtml_Logcards_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('logcardsGrid');
        $this->setDefaultSort('card_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setTemplate('webtex/giftcards/logcards/grid.phtml');
        $this->setGroupField('card_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('giftcards/logger')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        
        $this->addColumn('expand_row', array(
            'align'     => 'center',
            'width'     => '5px',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
        ));
        

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('giftcards')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'      => 'number',
            'sortable'  => false,
            'filter'    => false,
        ));
        $this->addColumn('card_id', array(
            'header'    => Mage::helper('giftcards')->__('Card ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'card_id',
            'type'      => 'number',
        ));

        $this->addColumn('card_code', array(
            'header'    => Mage::helper('giftcards')->__('Card Code'),
            'align'     => 'left',
            'index'     => 'card_code',
            'sortable'  => false,
        ));

        $this->addColumn('card_amount', array(
            'header'    => Mage::helper('giftcards')->__('Initial Value'),
            'type'  => 'currency',
            'currency' => 'card_currency',
            'index' => 'card_amount',
            'sortable'  => false,
        ));

        $this->addColumn('card_balance', array(
            'header'    => Mage::helper('giftcards')->__('Current Balance'),
            'type'  => 'currency',
            'currency' => 'card_currency',
            'index' => 'card_balance',
            'sortable'  => false,
        ));

        $this->addColumn('order_id', array(
            'header'    => Mage::helper('giftcards')->__('Order #'),
            'index' => 'order_id',
            'type'  => 'text',
            'sortable'  => false,
        ));


        $this->addColumn('card_action', array(
            'header'    => Mage::helper('giftcards')->__('Action'),
            'index'     => 'card_action',
            'type'      => 'text',
            'sortable'  => false,
        ));

        $this->addColumn('user', array(
            'header'    => Mage::helper('giftcards')->__('Action By'),
            'index' => 'user',
            'sortable'  => false,
        ));

        $this->addColumn('card_comment', array(
            'header'    => Mage::helper('giftcards')->__('Comment'),
            'index'     => 'card_comment',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => false,
        ));

        $this->addColumn('created_time', array(
            'header'    => Mage::helper('giftcards')->__('Atcion Date'),
            'align'     => 'left',
            'index'     => 'created_time',
            'type'      => 'datetime',
            'width'     => '160px',
            'sortable'  => false,
        ));

        $this->addColumn('card_status', array(
            'header'    => Mage::helper('giftcards')->__('Status'),
            'index'     => 'card_status',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('giftcards')->__('Active'),
                '0' => Mage::helper('giftcards')->__('Inactive'),
                '2' => Mage::helper('giftcards')->__('Used'),
                '3' => Mage::helper('giftcards')->__('Expired'),
                '4' => Mage::helper('giftcards')->__('Refunded'),
            ),
            'sortable'  => false,
        ));
        
        return parent::_prepareColumns();
    }
}
