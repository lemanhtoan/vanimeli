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
 * RewardPointsRule Earning Catalog Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */

class Magestore_RewardPointsRule_Adminhtml_Reward_Earning_CatalogController extends Mage_Adminhtml_Controller_Action
{

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/earning/catalog');
    }
    
    /**
     * init layout and set active for current menu
     * 
     * @return Magestore_RewardPointsRule_Adminhtml_Earning_CatalogController
     */
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('rewardpoints/earning')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
		return $this;
	}
 
    /**
     * index action
     */
	public function indexAction() {
        if(Mage::getStoreConfig('rewardpointsrule/indexmanagement/flag')){
            Mage::getSingleton('adminhtml/session')->addNotice($this->getDirtyRulesNoticeMessage());
        }
		$this->_title($this->__('Reward Points'))
			->_title($this->__('Catalog Earning Rule'));
		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_catalog'));
        $this->renderLayout();
	}

    /**
     * view and edit item action, if item is new then view blank
     */
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('rewardpointsrule/earning_catalog')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			$model->getConditions()->setJsFormObject('rule_conditions_fieldset');
			Mage::register('rule_data', $model);
			
			$this->_title($this->__('Reward Points'))
				->_title($this->__('Manage rule'));
			if ($model->getId()){
				$this->_title($model->getTitle());
			}else{
				$this->_title($this->__('New rule'));
			}

			$this->loadLayout();
			$this->_setActiveMenu('rewardpointsrule/rule');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule Manager'), Mage::helper('adminhtml')->__('Rule Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rule News'), Mage::helper('adminhtml')->__('Rule News'));
			
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            } 
			
			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);

			$this->_addContent($this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_catalog_edit'))
				->_addLeft($this->getLayout()->createBlock('rewardpointsrule/adminhtml_earning_catalog_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('The item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
    /**
     * new action is create new item
     */
	public function newAction() {
		$this->_forward('edit');
	}
 
    /**
     * save action is save item
     */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()){
			$model = Mage::getModel('rewardpointsrule/earning_catalog')->load($this->getRequest()->getParam('id'));
			$data = $this->_filterDates($data, array('from_date', 'to_date'));
			if (!$data['from_date']) $data['from_date'] = null;
			if (!$data['to_date']) $data['to_date'] = null;
			if (isset($data['rule'])) {
                $rules = $data['rule'];
                if (isset($rules['conditions'])) {
                    $data['conditions'] = $rules['conditions'];
                }
                unset($data['rule']);
            }
			try {
				$model->loadPost($data)
					->setData('from_date',$data['from_date'])
					->setData('to_date',$data['to_date'])
					->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('rewardpointsrule')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
                //add notice message required to apply rule
                Mage::getModel('core/config')->saveConfig('rewardpointsrule/indexmanagement/flag', 1);
                //change status index process
                $process = Mage::getModel('index/process')->getCollection()
                    ->addFieldToFilter('indexer_code', 'rewardpoints_earning_product');
                Mage::getModel('index/process')->load($process->getFirstItem()->getId())
                    ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Unable to find the item to save'));
        $this->_redirect('*/*/');
	}
 
    /**
     * delete action is delete item
     */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('rewardpointsrule/earning_catalog');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rule was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
    
    public function applyRulesAction()
    {
        //Mage::getModel('rewardpointsrule/earning_product')->applyAll();
        $process = Mage::getModel('index/process')->getCollection()
            ->addFieldToFilter('indexer_code', 'rewardpoints_earning_product');           
        $this->getRequest()->setParam('process', $process->getFirstItem()->getId());
        $this->reindexProcess();
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rules were successfully applied'));
        Mage::getModel('core/config')->saveConfig('rewardpointsrule/indexmanagement/flag', 0);
        $this->_redirect('*/*/');
    }
    
    /**
     * Reindex all data what process is responsible
     */
    public function reindexProcess()
    {
        $process = $this->_initProcess();
        if ($process) {
            try {
                Varien_Profiler::start('__INDEX_PROCESS_REINDEX_ALL__');
                $process->reindexEverything();
                Varien_Profiler::stop('__INDEX_PROCESS_REINDEX_ALL__');
                $this->_getSession()->addSuccess(
                    Mage::helper('rewardpointsrule')->__('%s index was rebuilt.', $process->getIndexer()->getName())
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addException($e,
                     Mage::helper('rewardpointsrule')->__('There was a problem with reindexing process.')
                );
            }
        } else {
            $this->_getSession()->addError(
                Mage::helper('rewardpointsrule')->__('Cannot initialize the indexer process.')
            );
        }
    }
    
    /**
     * Initialize process object by request
     * 
     * @return boolean
     */
    protected function _initProcess()
    {
        $processId = $this->getRequest()->getParam('process');
        if ($processId) {
            $process = Mage::getModel('index/process')->load($processId);
            if ($process->getId()) {
                return $process;
            }
        }
        return false;
    }
    /**
     * Get dirty rules notice message
     *
     * @return string
     */
    public function getDirtyRulesNoticeMessage()
    {
        $defaultMessage = Mage::helper('rewardpointsrule')->__('The rule you have added/edited has not been applied on the catalog. Please click on the Reindex Rules in order to see the effect on the catalog.');
        return $defaultMessage;
    }
    
    /**
     * show apply rule popwin
     *
     * @return type
     */
    public function showApplyRuleAction(){        
        $this->loadLayout();
        $html = $this->getLayout()->getBlock('head')->toHtml();
        $html .= $this->getLayout()->getBlock('applyrule')->toHtml();
        $earningProductResource = Mage::getResourceModel('rewardpointsrule/earning_product');    
        $write = $earningProductResource->getWriteAdapter();
        $write->beginTransaction();

        //delete all
        $write->delete($earningProductResource->getTable('rewardpointsrule/earning_product'));
        $write->commit();
        $this->getResponse()->setBody($html);
    }
    
    /**
     * this function is use to update data in table rewardpoints_earning_product
     *          
     * @throws Exception
     */
    public function applyRuleAjaxAction() {   
        //Varien_Profiler::start('__INDEX_PROCESS_REINDEX_ALL__');
        $earningProductResource = Mage::getResourceModel('rewardpointsrule/earning_product');    
        $write = $earningProductResource->getWriteAdapter();
        $write->beginTransaction();

        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_FRONTEND, Mage_Core_Model_App_Area::PART_EVENTS); //Hai.Tran 11/11/2013 fix finalPrice
        $catalog_product = Mage::getResourceModel('catalog/product_collection');
        // Prepare Catalog Product Collection to used for Rules
        $catalog_product->addAttributeToSelect('price')
                ->addAttributeToSelect('special_price')
                ->addAttributeToSelect('cost')
                ->addAttributeToSelect('rewardpoints_spend')
                ->addAttributeToSelect('tax_class_id'); //Hai.Tran 14/11/2013

        $rules = Mage::getResourceModel('rewardpointsrule/earning_catalog_collection')
                ->addFieldToFilter('is_active', 1);
        $rules->getSelect()
                ->where("(from_date IS NULL) OR (DATE(from_date) <= ?)", now(true))
                ->where("(to_date IS NULL) OR (DATE(to_date) >= ?)", now(true));
        foreach ($rules as $rule) {
            $rule->afterLoad();
            $rule->getConditions()->collectValidatedAttributes($catalog_product);
        }
        $pageSize = Mage::getStoreConfig('rewardpoints/rewardpointsrule/applyrule_products');
        $page = $this->getRequest()->getParam('page');        
        if(!isset($page) || $page == null){
            $result = Mage::helper('adminhtml')->__('Rules were applied');
            return $this;
        }
        $catalog_product->setPageSize($pageSize);
        $catalog_product->setCurPage($page);   
        
        $rows = array();
        $errors = 0;
        $numberRows = 0;
        $poductsApplied = 0;
        try {
            foreach ($catalog_product as $product) {
                $datas = Mage::getSingleton('rewardpointsrule/indexer_product')
                        ->getIndexProduct($product);
                foreach ($datas as $data) {
                    $rows[] = $data;
                }
                end($rows);
                $lastKey = key($rows);
                if ($lastKey+1 == 1000) {
                    end($rows);
                    $numberRows = key($rows)+1;
                    $write->insertMultiple($earningProductResource->getTable('rewardpointsrule/earning_product'), $rows);
                    $rows = array();
                }
                $poductsApplied++;
            }
            if (!empty($rows)) {
                end($rows);
                $numberRows = key($rows)+1;
                //$poductsApplied = round($numberRows/count($data));
                $write->insertMultiple($earningProductResource->getTable('rewardpointsrule/earning_product'), $rows);                
            }            
            $write->commit();
        } catch (Exception $e) {
            $write->rollback();
            $errors++;
            throw $e;
        }
        if ($errors && $errors>0){
            $result = $this->__('Applying rule was failed');
            $action = 'failed';
        } else {			
            $result = $this->__('/%s product(s) have been applied successfully',$catalog_product->getSize());
            $action = 'success';
        }        
        $totalProducts = $poductsApplied * $page;
        if($poductsApplied < $pageSize || $totalProducts > $catalog_product->getSize()) {
            $result .= '-complete-'.$poductsApplied;            
        }else{
            $result .= '-continue-'.$poductsApplied;			
        }
        $result .= '-'.$action;
        $this->getResponse()->setBody($result);
         //Varien_Profiler::stop('__INDEX_PROCESS_REINDEX_ALL__');
    }
    
    
    public function massDeleteAction()
    {
        $ruleIds = $this->getRequest()->getParam('rule');
        if(!is_array($ruleIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please select rule(s).'));
        } else {
            try {
                
                foreach ($ruleIds as $ruleId) {
                    Mage::getModel('rewardpointsrule/earning_catalog')->load($ruleId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpointsrule')->__('Total of %d record(s) were deleted.', count($ruleIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
    
    public function massChangeStatusAction(){
        $ruleIds = $this->getRequest()->getParam('rule');
        $status = $this->getRequest()->getParam('status');
        if(!is_array($ruleIds)) {
             Mage::getSingleton('adminhtml/session')->addError(Mage::helper('rewardpointsrule')->__('Please select rule(s).'));
        } else {
            try {
                foreach ($ruleIds as $ruleId) {
                    Mage::getModel('rewardpointsrule/earning_catalog')
                            ->load($ruleId)
                            ->setData('is_active',$status)
                            ->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('rewardpointsrule')->__('Total of %d record(s) were updated.', count($ruleIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
 
        $this->_redirect('*/*/index');
    }
}
