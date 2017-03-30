<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints Account Dashboard Recent Transactions
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Transactions extends Magestore_RewardPoints_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $collection = Mage::getResourceModel('rewardpoints/transaction_collection')
            ->addFieldToFilter('main_table.customer_id', Mage::getSingleton('customer/session')->getCustomerId())
            ->addFieldToFilter('main_table.status', array(
                'neq' => Magestore_RewardPoints_Model_Transaction::STATUS_CANCELED
            ))
            ->addFieldToFilter('main_table.order_id', array('notnull' => true))
            ->addFieldToFilter('main_table.action', array('in' => array('referfriends', 'referfriends_cancel')));
        $collection->getSelect()->joinLeft(
                array('or' => $collection->getTable('sales/order')), 
                'main_table.order_id = or.entity_id',
                array(
                    'firstname'     => 'or.customer_firstname',
                    'lastname'      => 'or.customer_lastname',
                    'email'    => 'or.customer_email',
                    'customer_id' => 'or.customer_id'
                ))
            ->columns(array(
                'earning_points'    => 'SUM(main_table.point_amount)'
            ))
            ->group('or.customer_email')
            ->order('main_table.created_time DESC');
        $this->setCollection($collection);
    }
    /**
      * prepare block's layout
      *
      * @return Magestore_RewardPointsTransfer_Block_Rewardpointstransfer
      */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('page/html_pager', 'rewardpointsreferfriends.transactions')
            ->setCollection($this->getCollection());
        $this->setChild('refertransaction_pager', $pager);
        return $this;
    }
    
    public function getPagerHtml()
    {
        return $this->getChildHtml('refertransaction_pager');
    }
}
