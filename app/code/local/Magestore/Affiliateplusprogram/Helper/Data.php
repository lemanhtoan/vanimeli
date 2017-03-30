<?php

class Magestore_Affiliateplusprogram_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_cache = array();

    public function getProgramOptions() {
        if (!isset($this->_cache['program_options'])) {
            $options[0] = $this->__('Affiliate Program');
            $programCollection = Mage::getResourceModel('affiliateplusprogram/program_collection');
            foreach ($programCollection as $program) {
                $options[$program->getId()] = $program->getName();
            }
            $this->_cache['program_options'] = $options;
        }
        return $this->_cache['program_options'];
    }

    public function getProgramOptionArray() {
        if (!isset($this->_cache['program_option_array'])) {
            $optionArray = array();
            foreach ($this->getProgramOptions() as $value => $label) {
                $optionArray[] = array(
                    'value' => $value,
                    'label' => $label,
                );
            }
            $this->_cache['program_option_array'] = $optionArray;
        }
        return $this->_cache['program_option_array'];
    }

    public function getJoinedProgramIds() {
        if (!isset($this->_cache['joined_program_ids'])) {
            $joinedPrograms = array(0);
            $joinedColection = Mage::getResourceModel('affiliateplusprogram/account_collection')
                    ->addFieldToFilter('account_id', Mage::helper('affiliateplus/account')->getAccount()->getId());
            foreach ($joinedColection as $item)
                $joinedPrograms[] = $item->getProgramId();
            $this->_cache['joined_program_ids'] = $joinedPrograms;
        }
        return $this->_cache['joined_program_ids'];
    }

    public function getProgramProductIds($programId, $storeId = null) {
        if (is_null($storeId))
            $storeId = Mage::app()->getStore()->getId();

        $cacheKey = 'program_' . $programId . '_product_ids_in_store_' . $storeId;
        if (isset($this->_cache[$cacheKey]))
            return $this->_cache[$cacheKey];

        $productIds = array();
        $categoryCollection = Mage::getResourceModel('affiliateplusprogram/category_collection')
                ->addFieldToFilter('program_id', $programId)
                ->addFieldToFilter('store_id', $storeId);
        if ($categoryCollection->getSize() == 0)
            $categoryCollection = Mage::getResourceModel('affiliateplusprogram/category_collection')
                    ->addFieldToFilter('program_id', $programId)
                    ->addFieldToFilter('store_id', 0);
        $categoryIds = array();
        foreach ($categoryCollection as $category)
            $categoryIds[] = $category->getCategoryId();
        if (count($categoryIds)) {
            $productCollection = Mage::getResourceModel('catalog/product_collection');
            $productCollection->getSelect()
                    ->join(
                            array('c' => $productCollection->getTable('catalog/category_product_index')), 'e.entity_id = c.product_id', array()
                    )->where('c.category_id IN (' . implode(',', $categoryIds) . ')')
                    ->group('e.entity_id');
            $productIds = $productCollection->getAllIds();
        }

        $this->_cache[$cacheKey] = $productIds;
        return $this->_cache[$cacheKey];
    }

    /* public function getProgramProductIds($programId){
      $cacheKey = 'program_'.$programId.'_product_ids';
      if (isset($this->_cache[$cacheKey])) return $this->_cache[$cacheKey];

      $productIds = array();
      $this->refreshProgramProductIds($programId);

      $productCollection = Mage::getResourceModel('affiliateplusprogram/product_collection')
      ->addFieldToFilter('program_id',$programId);
      foreach ($productCollection as $product)
      $productIds[] = $product->getProductId();

      $this->_cache[$cacheKey] = array_unique($productIds);
      return $this->_cache[$cacheKey];
      }

      public function refreshProgramProductIds($programId){
      if (isset($this->_cache['refresh_program_product_ids'])) return $this;

      $program = Mage::getModel('affiliateplusprogram/program')->load($programId);
      if ($program->getId()){
      if ($program->getIsProcess()){
      $this->_cache['refresh_program_product_ids'] = true;
      return $this;
      }
      } else {
      $this->_cache['refresh_program_product_ids'] = true;
      return $this;
      }

      $productCollection = Mage::getResourceModel('catalog/product_collection')
      ->addAttributeToSelect('*');
      $productIds = array();
      foreach ($productCollection as $product)
      if ($program->validateItem($product))
      $productIds[] = $product->getId();
      try {
      Mage::getModel('affiliateplusprogram/product')
      ->setProgramId($program->getId())
      ->setProductIds($productIds)
      ->saveAllProducts();
      $program->setProgramIsProcessed();
      } catch (Exception $e){}

      $this->_cache['refresh_program_product_ids'] = true;
      return $this;
      } */

    public function initProgram($accountId, $order = null) {
        if (isset($this->_cache["init_programs_$accountId"]))
            return $this;
        /*$joinedPrograms = Mage::getResourceModel('affiliateplusprogram/account_collection')
                ->addFieldToFilter('account_id', $accountId)
                ->setOrder('joined', 'DESC');*/
       
        /* Changed By Adam to sort program by priority and joined 22/07/2014*/
        $resource = Mage::getModel('core/resource');
        $joinedPrograms = Mage::getModel('affiliateplusprogram/program')->getCollection();
        
        /**
         * - Lay theo priority cao hon
         * - Neu 2 program co cung priority thi se lay theo ngay sau cung ma affiliate join vao program
         * - Neu join cung ngay, cung priority thi se lay theo program_id cao hon
         */
        $joinedPrograms->getSelect()
                    ->join(array('affiliateplusprogram_account'=>$resource->getTableName('affiliateplusprogram/account')), 'affiliateplusprogram_account.program_id = main_table.program_id', array('program_id'=>'program_id', 'account_id'=>'account_id', 'joined'=>'joined'))
                    ->where('affiliateplusprogram_account.account_id = ?',$accountId)
                    ->order('main_table.priority DESC')
                    ->order('affiliateplusprogram_account.joined DESC')
                    ->order('main_table.program_id DESC')
                    ;
        /* End code*/
                
        $programs = array();
        $quote = null;
        if ($order)
            $quote = $order;
        else {
            /* hainh update 25-04-2014 */
            if (Mage::getSingleton('checkout/session')->hasQuote())
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
            /* edit By Jack 24/09/2014 */
            else
                $quote = Mage::getSingleton('adminhtml/session_quote')->getQuote();
            /* end Edit */
            /* end update */
        }
        /*Changed By Adam to customize (27/08/2016): create transaction from an existed order*/
        $isAdmin = Mage::app()->getStore()->isAdmin();
        foreach ($joinedPrograms as $joinedProgram) {
            if($isAdmin) {
                $program = Mage::getModel('affiliateplusprogram/program')
                    ->load($joinedProgram->getProgramId());
            } else {
                /* Edit By Jack */
                $storeId = Mage::app()->getStore()->getId();
                if (!$storeId)
                    $storeId = Mage::getSingleton('adminhtml/session_quote')->getStoreId();
                $program = Mage::getModel('affiliateplusprogram/program')
                    ->setStoreId($storeId)
                    ->load($joinedProgram->getProgramId());
            }
            if ($program->validateOrder($quote))
                $programs[] = $program;
            /* Edit By Jack */
        }
        $this->_cache["init_programs_$accountId"] = $programs;
        return $this;
    }

    public function getProgramByItemAccount($itemProduct, $account) {
        if (is_object($account))
            $accountId = $account->getId();
        else
            $accountId = $account;
        if (!isset($this->_cache["init_programs_$accountId"]))
            $this->initProgram($accountId);
        $programs = $this->_cache["init_programs_$accountId"];
        if (count($programs))
            foreach ($programs as $program)
                if ($program->validateItem($itemProduct))
                    return $program;
        return null;
    }

    public function getProgramByProductAccount($product, $account) {
        return $this->getProgramByItemAccount($product, $account);
    }

    public function multilevelIsActive() {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array) $modules;
        if (isset($modulesArray['Magestore_Affiliatepluslevel']) && is_object($modulesArray['Magestore_Affiliatepluslevel']))
            return $modulesArray['Magestore_Affiliatepluslevel']->is('active');
        return false;
    }

    public function getStandardCommissionPercent() {
        $storeId = Mage::app()->getStore()->getId();
        $perCommissions = Mage::getStoreConfig('affiliateplus/multilevel/commission_percentage', $storeId);
        $arrPerCommissions = explode(',', $perCommissions);
        return $arrPerCommissions[0];
    }

    /* hainh edit 28-04-2014 */

    public function isPluginEnabled() {
        // Changed By Adam 28/07/2014
        if(!Mage::helper('affiliateplus')->isAffiliateModuleEnabled()) return false;
        $check = Mage::getStoreConfig('affiliateplus/program/enable');
        return $check;
    }

   public function isModuleDisabled($store = null) {
        if (Mage::helper('affiliateplus/account')->accountNotLogin())
            return TRUE;
        $check = Mage::getStoreConfig('affiliateplus/program/enable', $store);
        return !$check;
    }

    public function showDefault() {
        return Mage::getStoreConfig("affiliateplus/program/show_default");
    }

    /* end update */
    
    
    /**
     * @author Changed By Adam 01/08/2014
     * Kiem tra item co nam trong program co priority cao hon khong
     * return true or false
     */    
    public function checkItemInHigherPriorityProgram($accountId, $item, $priority) {
        if (!isset($this->_cache["init_programs_$accountId"]))
            $this->initProgram($accountId);
        $programs = $this->_cache["init_programs_$accountId"];
        if(count($programs) > 1) 
            foreach($programs as $program) {
                if(($program->getPriority() > $priority) && $program->validateItem($item)) {
                    return true;
                }
            }
        return false;
    }
    /* edit By Jack */
    public function getProgramByMaxPriority($accountId){
        $programs = Mage::getModel('affiliateplusprogram/program')->getCollection()
                   ->setOrder('priority','DESC');
        foreach($programs as $program){
            if($program->getPriority() > 0 && $program->getStatus() == 1)
                return $program;
        }
        $programsJoined = Mage::getModel('affiliateplusprogram/joined')->getCollection()
                          ->addFieldToFilter('account_id',$accountId)
                          ->setOrder('id','DESC');
        foreach($programsJoined as $programJoined){
            $programData = Mage::getModel('affiliateplusprogram/program')->load($programJoined->getProgramId());
            if($programData->getStatus() == 1)
                return $programData;
        }
        return null;
    }
    /* end Edit */
}
