<?php

class Magestore_Affiliateplusprogram_Model_Program extends Mage_Rule_Model_Rule {

    protected $_store_id = null;

    /** Thanhpv - add $_eventPrefix,$_eventObject (2012-10-11) */
    protected $_eventPrefix = 'affiliateplus_program';
    protected $_eventObject = 'affiliateplus_program';

    public function setStoreId($value) {
        $this->_store_id = $value;
        return $this;
    }

    public function getStoreId() {
        return $this->_store_id;
    }

    public function _construct() {
        parent::_construct();
        $this->_init('affiliateplusprogram/program');
    }

    public function getStoreAttributes() {
        $storeAttribute = new Varien_Object(array(
            'store_attribute' => array(
                'name',
                'affiliate_type',
                'status',
                'description',
                'commission_type',
                'commission',
                'sec_commission',
                'sec_commission_type',
                'secondary_commission',
                'discount_type',
                'discount',
                'sec_discount',
                'sec_discount_type',
                'secondary_discount',
                'customer_group_ids',
                'show_in_welcome',
                'use_tier_config',
                'max_level',
                'tier_commission',
                'use_sec_tier',
                'sec_tier_commission',
            )
        ));
        /** Thanhpv - add even $this->_eventPrefix . '_get_store_attributes' (2012-10-11) */
        Mage::dispatchEvent($this->_eventPrefix . '_get_store_attributes', array(
            $this->_eventObject => $this,
            'attributes' => $storeAttribute,
        ));
        return $storeAttribute->getStoreAttribute();
    }

    public function getTotalAttributes() {
        return array(
            'total_sales_amount',
                //'total_clicks',
                //'total_unique_clicks'
        );
    }

    /**
     * load data for model
     *
     * @param mixed $id
     * @param string $field
     * @return Magestore_Affiliateplusprogram_Model_Program
     */
    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId())
            $this->loadStoreValue();
        if (is_string($this->getData('tier_commission')))
            $this->setData('tier_commission', unserialize($this->getData('tier_commission')));
        if (is_string($this->getData('sec_tier_commission')))
            $this->setData('sec_tier_commission', unserialize($this->getData('sec_tier_commission')));
        return $this;
    }

    /**
     * function model value in store
     *
     * @param int $storeId
     * @return Magestore_Affiliateplusprogram_Model_Program
     */
    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreId();
        if (!$storeId)
            return $this;
        $storeValues = Mage::getModel('affiliateplusprogram/value')->getCollection()
                ->addFieldToFilter('program_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);

        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        foreach ($this->getStoreAttributes() as $attribute)
            if (!$this->getData($attribute . '_in_store'))
                $this->setData($attribute . '_default', true);

        foreach ($this->getTotalAttributes() as $attribute)
            if (!$this->getData($attribute . '_in_store')) {
                $this->setData($attribute . '_in_store', true);
                $this->setData($attribute, 0.000000000001);
            }

        return $this;
    }

    protected function _beforeSave() {
        $defaultProgram = Mage::getModel('affiliateplusprogram/program')->load($this->getId());
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                if ($defaultProgram->getId())
                    $this->setData($attribute, $defaultProgram->getData($attribute));
            }
            if ($this->getId()) {
                $totalAttributes = $this->getTotalAttributes();
                foreach ($totalAttributes as $attribute) {
                    $attributeValue = Mage::getModel('affiliateplusprogram/value')
                            ->loadAttributeValue($this->getId(), $storeId, $attribute);
                    if ($delta = ($this->getData($attribute) - $attributeValue->getValue())) {
                        try {
                            $attributeValue->setValue($this->getData($attribute));
                            $attributeValue->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                    $this->setData($attribute, $defaultProgram->getData($attribute) + $delta);
                }
            }
        }
        if (is_array($this->getData('tier_commission')))
            $this->setData('tier_commission', serialize($this->getData('tier_commission')));
        if (is_array($this->getData('tier_commission_value')))
            $this->setData('tier_commission_value', serialize($this->getData('tier_commission_value')));
        if (is_array($this->getData('sec_tier_commission')))
            $this->setData('sec_tier_commission', serialize($this->getData('sec_tier_commission')));
        if (is_array($this->getData('sec_tier_commission_value')))
            $this->setData('sec_tier_commission_value', serialize($this->getData('sec_tier_commission_value')));
        parent::_beforeSave();
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
        }
        return $this;
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('affiliateplusprogram/value')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        if (is_string($this->getData('tier_commission')))
            $this->setData('tier_commission', unserialize($this->getData('tier_commission')));
        if (is_string($this->getData('sec_tier_commission')))
            $this->setData('sec_tier_commission', unserialize($this->getData('sec_tier_commission')));
        return parent::_afterSave();
    }

    public function getAccountIds() {
        $accountCollection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                ->addFieldToFilter('program_id', $this->getId());
        $accountIds = array();
        foreach ($accountCollection as $account)
            $accountIds[] = $account->getAccountId();
        return $accountIds;
    }

    public function isAvailable() {
        if (!$this->getId() || !$this->getStatus())
            return false;
        if ($this->getValidFrom())
            if (strtotime($this->getValidFrom()) > time())
                return false;
        if ($this->getValidTo())
            if (strtotime($this->getValidTo()) < strtotime(now(true)))
                return false;

//        Changed by Adam (09/05/2016): Fix issue: can't detect program if customer (buyer) can't belong to the customer group in Discount section
//        if ($groupIds = $this->getCustomerGroupIds()) {
//            if (is_string($groupIds))
//                $groupIds = explode(',', $groupIds);
//            if (isset($groupIds[0]) && $groupIds[0] == 'Array') {
//                return true;
//            }
//            if (!in_array(Mage::getSingleton('customer/session')->getCustomerGroupId(), $groupIds)) {
//                return false;
//            }
//        }
        return true;
    }
    
    /**
     * Added By Adam (09/05/2016)
     * Fix issue: can't detect program if customer (buyer) can't belong to the customer group in Discount section
     * @return boolean
     */
    public function checkCustomerGroupForDiscount(){
        if ($groupIds = $this->getCustomerGroupIds()) {
            if (is_string($groupIds))
                $groupIds = explode(',', $groupIds);
            if (isset($groupIds[0]) && $groupIds[0] == 'Array') {
                return true;
            }
            if (!in_array(Mage::getSingleton('customer/session')->getCustomerGroupId(), $groupIds)) {
                return false;
            }
        }
        return true;
    }

    public function getConditionsInstance() {
        return Mage::getModel('salesrule/rule_condition_combine');
    }

    public function getActionsInstance() {
        return Mage::getModel('salesrule/rule_condition_product_combine');
    }

    public function loadPost(array $rule) {
        $arr = $this->_convertFlatToRecursive($rule);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions(array())->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['actions'])) {
            $this->getActions()->setActions(array())->loadArray($arr['actions'][1], 'actions');
        }
        return $this;
    }

    public function validateOrder($order) {
        if (!$this->isAvailable())
            return false;
        //hainh edit this line 29-04-2014
        if (($order) && (!$order->getQuote())) {
            $order->setQuote($order);
        }
        return $this->validate($order);
    }

    //add by Jack and Jacob
    public function getAllProgramsByItems($productId) {
        $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
        $account = '';
        foreach ($affiliateInfo as $info) {
            if ($info['account']) {
                $account = $info['account'];
                break;
            }
        }
        
        $accountId = $account ? $account->getAccountId() : '';

        // if(Mage::getSingleton('customer/session')->getId())
        // $customerId = Mage::getSingleton('customer/session')->getId();
        // else if(Mage::getSingleton('adminhtml/session_quote')->getCustomer())
        // $customerId = Mage::getSingleton('adminhtml/session_quote')->getCustomer()->getId();
        // if(isset($customerId) && $customerId)
        // $accountId = Mage::getModel('affiliateplus/account')->getCollection()
        // ->addFieldToFilter('customer_id',$customerId)->getFirstItem()->getId(); 

        $programs = Mage::getModel('affiliateplusprogram/program')->getCollection();
        $resource = Mage::getModel('core/resource');
        if ($accountId) {
            $programs->getSelect()
                    ->join(array('affiliateplusprogram_account' => $resource->getTableName('affiliateplusprogram/account')), 'affiliateplusprogram_account.program_id = main_table.program_id', array('program_id' => 'program_id', 'account_id' => 'account_id', 'joined' => 'joined'))
                    ->where('affiliateplusprogram_account.account_id = ?', $accountId)
                    ->order('main_table.priority DESC')
                    ->order('affiliateplusprogram_account.joined DESC')
                    ->order('main_table.program_id DESC')
            ;
        }
        // else{
        // $programs->getSelect()
        // ->order('main_table.priority DESC')
        // ->order('main_table.program_id DESC')
        // ;
        // }
        $programByItems = array();
        foreach ($programs as $program) {
            if (in_array($productId, Mage::helper('affiliateplusprogram')->getProgramProductIds($program->getId()))) {
                if ($program->getStatus() == 1)
                    $programByItems[] = $program->getId();
            }
        }
        return $programByItems;
    }

    //
    public function validateItem($item) {
        if (!$this->isAvailable())
            return false;
        if ($item instanceof Mage_Catalog_Model_Product) {
            $_item = Mage::getModel('sales/quote_item')->setProduct($item);
            $item = $_item;
        }
        //jack
        if ($item->getProduct())
            $productId = $item->getProduct()->getId();
        else
            $productId = $item->getProductId();
        //
        $programByItems = $this->getAllProgramsByItems($productId);
        if (!in_array($productId, Mage::helper('affiliateplusprogram')->getProgramProductIds($this->getId()))) {
            if ($parentItem = $item->getParentItem()) {
                if (!in_array($parentItem->getProduct()->getId(), Mage::helper('affiliateplusprogram')->getProgramProductIds($this->getId())))
                    return false;
            } else {
                return false;
            }
        }
        //add by Jack
        /*
          Khi dung coupon thi khong can phai kiem tra
         */
        $session = Mage::getSingleton('checkout/session');
        $isUseCoupon = $session->getAffiliateCouponCode();
        if (!isset($isUseCoupon)) {
            // Changed By Adam 07/05/2015: 1 san pham nam trong 2 program khac nhau. affiliate chi join vao program co priority thap hon => ko chay duoc cho program co do uu tien thap hon do code cua jack. Khong thay discount o frontend
            if (Mage::app()->getStore()->isAdmin() && $this->getId() != $programByItems[0] && count($programByItems) > 1) {
                return false;
            }
        }
        //
        return $this->getActions()->validate($item);
    }

    public function setProgramIsProcessed() {
        $this->getResource()->setProgramIsProcessed($this);
        return $this;
    }

    public function orgSave() {
        /**
         * Direct deleted items to delete method
         */
        if ($this->isDeleted()) {
            return $this->delete();
        }
        if (version_compare(Mage::getVersion(), '1.4.1.1') > 0) {
            if (!$this->_hasModelChanged()) {
                return $this;
            }
        } else {
            if (!$this->hasDataChanges())
                return $this;
        }
        $this->_getResource()->beginTransaction();
        $dataCommited = false;
        try {
            $this->_orgBeforeSave();
            if ($this->_dataSaveAllowed) {
                $this->_getResource()->save($this);
                $this->_afterSave();
            }
            $this->_getResource()->addCommitCallback(array($this, 'afterCommitCallback'))
                    ->commit();
            $this->_hasDataChanges = false;
            $dataCommited = true;
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            $this->_hasDataChanges = true;
            throw $e;
        }
        if ($dataCommited) {
            $this->_afterSaveCommit();
        }
        return $this;
    }

    protected function _orgBeforeSave() {
        $defaultProgram = Mage::getModel('affiliateplusprogram/program')->load($this->getId());
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                if ($defaultProgram->getId())
                    $this->setData($attribute, $defaultProgram->getData($attribute));
            }
            if ($this->getId()) {
                $totalAttributes = $this->getTotalAttributes();
                foreach ($totalAttributes as $attribute) {
                    $attributeValue = Mage::getModel('affiliateplusprogram/value')
                            ->loadAttributeValue($this->getId(), $storeId, $attribute);
                    if ($delta = ($this->getData($attribute) - $attributeValue->getValue())) {
                        try {
                            $attributeValue->setValue($this->getData($attribute));
                            $attributeValue->save();
                        } catch (Exception $e) {
                            
                        }
                    }
                    $this->setData($attribute, $defaultProgram->getData($attribute) + $delta);
                }
            }
        }
        if (is_array($this->getData('tier_commission')))
            $this->setData('tier_commission', serialize($this->getData('tier_commission')));
        if (is_array($this->getData('tier_commission_value')))
            $this->setData('tier_commission_value', serialize($this->getData('tier_commission_value')));
        if (is_array($this->getData('sec_tier_commission')))
            $this->setData('sec_tier_commission', serialize($this->getData('sec_tier_commission')));
        if (is_array($this->getData('sec_tier_commission_value')))
            $this->setData('sec_tier_commission_value', serialize($this->getData('sec_tier_commission_value')));
        Mage_Core_Model_Abstract::_beforeSave();
        if (is_array($this->getCustomerGroupIds())) {
            $this->setCustomerGroupIds(join(',', $this->getCustomerGroupIds()));
        }
        return $this;
    }

}
