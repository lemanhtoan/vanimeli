<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Lifetimecustomer
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('lifetimecustomergrid');
        $this->setDefaultSort('tracking_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

    }

    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }


    protected function _prepareCollection()
    {

        $collection = Mage::getResourceModel('affiliateplus/tracking_collection')
            ->addFieldToFilter('account_id', $this->getRequest()->getParam('id'));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $id = $this->getRequest()->getParam('id');

        $this->addColumn('tracking_id', array(
            'header'    => Mage::helper('affiliateplus')->__('ID'),
            'width'     => '50px',
            'index'     => 'tracking_id',
            'type'  => 'number',
        ));

        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('affiliateplus')->__('Email'),
            'width'     => '250px',
            'index'     => 'customer_email',
            'renderer' => 'affiliateplus/adminhtml_account_renderer_customer',
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('affiliateplus')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('affiliateplus')->__('remove'),
                        'url'       => array('base'=> '*/*/remove/id/'.$id),
                        'field'     => 'tracking_id',
                        'confirm'	=> 'Do you want to remove this customer?'

                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
            ));

        return parent::_prepareColumns();
    }

    //return url
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/lifetimecustomerGrid', array('_current'=>true,'id'=>$this->getRequest()->getParam('id')));

    }

    //return Magestore_Affiliate_Model_Referral
    public function getAccount()
    {
        return Mage::getModel('affiliateplus/account')
            ->load($this->getRequest()->getParam('id'));
    }

}