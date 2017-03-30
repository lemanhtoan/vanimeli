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
class Ves_ProductList_Adminhtml_ProductlistController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()
		->_setActiveMenu('productlist/items')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rules Manager'));

		return $this;
	}

	public function newConditionHtmlAction()
	{
		$id = $this->getRequest()->getParam('id');
		$typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
		$type = $typeArr[0];

		$model = Mage::getModel($type)
		->setId($id)
		->setType($type)
		->setRule(Mage::getModel('productlist/rule'))
		->setPrefix('conditions');
		if (!empty($typeArr[1])) {
			$model->setAttribute($typeArr[1]);
		}

		if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
			$model->setJsFormObject($this->getRequest()->getParam('form'));
			$html = $model->asHtmlRecursive();
		} else {
			$html = '';
		}
		$this->getResponse()->setBody($html);
	}

	public function indexAction()
	{
		$this->_title($this->__("Ves Product List"));
        $this->_title($this->__("Manager Rules"));
		$this->_initAction()
		->renderLayout();
	}

	public function editAction()
	{
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('productlist/rule')->load($id);

		if ($model->getId() || $id == 0)
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data))
			{
				$model->addData($data);
			}
			$model->getConditions()->setJsFormObject('rule_conditions_fieldset');

			Mage::register('productlist_data', $model);

			$this->_title($this->__("Ves Product List"));

			if($rule_title = $model->getTitle()) {
				$this->_title($this->__("Edit Rule '%s'", $model->getTitle()));
			} else {
				$this->_title($this->__("Add New Rule"));
			}
        	

			$this->loadLayout();
			$this->_setActiveMenu('productlist/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Manager Rules'), Mage::helper('adminhtml')->__('Manager Rules'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules'), Mage::helper('adminhtml')->__('Rules'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true)->setCanLoadRulesJs(true);
			if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
				$this->getLayout()->getBlock('head')
				->setCanLoadTinyMce(true)
				->addItem('js','tiny_mce/tiny_mce.js')
				->addItem('js','mage/adminhtml/wysiwyg/tiny_mce/setup.js')
				->addItem('js','mage/adminhtml/variables.js')
				->addItem('js','mage/adminhtml/wysiwyg/widget.js')
				->addJs('mage/adminhtml/browser.js')
				->addJs('prototype/window.js')
				->addJs('lib/FABridge.js')
				->addJs('lib/flex.js')
				->addJs('mage/adminhtml/flexuploader.js')
				->addItem('js_css','prototype/windows/themes/default.css')
				->addCss('lib/prototype/windows/themes/magento.css');
			}

			$this->_addContent($this->getLayout()->createBlock('productlist/adminhtml_rule_edit'))
			->_addLeft($this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tabs'));

			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productlist')->__('Rule does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function newAction()
	{

		$this->_forward('edit');
	}

	/**
     * Grid Action
     * Display list of products related to current category
     *
     * @return void
     */
    public function gridAction()
    {
        $id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('productlist/rule')->load($id);

        if ($activeTabId = (string) $this->getRequest()->getParam('active_tab_id')) {
            Mage::getSingleton('admin/session')->setActiveTabId($activeTabId);
        }

        Mage::register('productlist_data', $model);
        Mage::getSingleton('cms/wysiwyg_config')->setStoreId($this->getRequest()->getParam('store'));

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('productlist/adminhtml_rule_edit_tab_product', 'rule.product.grid')
                ->toHtml()
        );
    }

	public function saveAction()
	{
		$rule_id = $this->getRequest()->getParam('id');
		if ($this->getRequest()->getParam("duplicate") && $rule_id) {
			$current_rule = Mage::getModel('productlist/rule')->load($rule_id);
			try {
				$rule_clone = Mage::getModel('productlist/rule');
				$rule_clone_data = $current_rule->getData();
				$rule_clone_data['identifier'] = $rule_clone_data['identifier'].'-'.time();
				unset($rule_clone_data['rule_id']);
				$rule_clone->setData($rule_clone_data);
				$rule_clone->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productlist')->__('Rule was successfully duplicated'));
				$this->_redirect('*/*/edit', array('id' => $rule_clone->getId()));
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($block_data);
			}
		}

		if ($data = $this->getRequest()->getPost())
		{
			$data = $this->_filterDates($data,array('date_from','date_to'));

			if(isset($_FILES['image']['name']) && $_FILES['image']['name'] != '')
			{
				$p = Mage::helper('productlist')->uploadFiles('image');
				$data['image'] = 'productlist'.$p['file'];
			}
			elseif (isset($data['image']['delete']) AND $data['image']['delete'] == 1)
			{
				Mage::helper('productlist')->removeFile($data['image']['value']);
				$data['image'] = '';
			}
			else
			{
				$data['image'] = isset($data['image'])?$data['image']['value']:"";
			}

			if(isset($_FILES['thumbnail']['name']) && $_FILES['thumbnail']['name'] != '')
			{
				$p = Mage::helper('productlist')->uploadFiles('thumbnail');
				$data['thumbnail'] = 'productlist'.$p['file'];
			}
			elseif (isset($data['thumbnail']['delete']) AND $data['thumbnail']['delete'] == 1)
			{
				Mage::helper('productlist')->removeFile($data['thumbnail']['value']);
				$data['thumbnail'] = '';
			}
			else
			{
				$data['thumbnail'] = isset($data['thumbnail'])?$data['thumbnail']['value']:"";
			}

			if(isset($data['available_sort_by'])) {
				$data['available_sort_by'] = is_array($data['available_sort_by'])?implode(",", $data['available_sort_by']): $data['available_sort_by'];
			}

			$model = Mage::getModel('productlist/rule');

			try
			{
				$request = $this->getRequest();

				$data['conditions'] = $data['rule']['conditions'];
				unset($data['rule']);

				if ($model->getCreated == NULL || $model->getModified() == NULL) {
					$model->setCreated(now())
					->setModified(now());
				} else {
					$model->setModified(now());
				}

				$model->loadPost($data);
				Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

				// set entered data if was error when we do save
				$model->addData($data)->setId($this->getRequest()->getParam('id'));

				Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productlist')->__('Rule was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError(
					Mage::helper('productlist')->__('An error occurred while saving the rule data. Please review the log and try again.')
					);
				Mage::logException($e);
				Mage::getSingleton('adminhtml/session')->setPageData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productlist')->__('Unable to find item to save'));
		$this->_redirect('*/*/');
	}

	public function deleteAction() {

		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$rule = Mage::getModel('productlist/rule');

				$rule->load($this->getRequest()->getParam('id'));

				$rule->delete();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This Rule Deleted Done'));
				$this->_redirect('*/*/');

			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

	public function massDeleteAction()
	{
		$rules = $this->getRequest()->getParam('productlist');

		if(!is_array($rules))
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
		}
		else
		{
			try
			{
				foreach ($rules as $_ruleId)
				{
					$productlist = Mage::getModel('productlist/rule')->load($_ruleId);
					$productlist->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(
					Mage::helper('adminhtml')->__(
						'Total of %d record(s) were successfully deleted', count($rules)
						)
					);
			}
			catch (Exception $e)
			{
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function massStatusAction()
	{
		$productlistIds = $this->getRequest()->getParam('productlist');
		if(!is_array($productlistIds))
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		}
		else
		{
			try
			{
				foreach ($productlistIds as $productlistId)
				{
					$productlist = Mage::getModel('productlist/rule')
					->load($productlistId)
					->setStatus($this->getRequest()->getParam('status'))
					->setIsMassupdate(true)
					->save();
				}
				$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($productlistIds))
					);
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError($e->getMessage());
			}
		}
		$this->_redirect('*/*/index');
	}

	public function exportCsvAction()
	{
		$fileName   = 'ves_productlist_rule.csv';
		$content    = $this->getLayout()->createBlock('productlist/adminhtml_rule_exportGrid')
		->getCsv();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function exportXmlAction()
	{
		$fileName   = 'ves_productlist_rule.xml';
		$content    = $this->getLayout()->createBlock('productlist/adminhtml_rule_exportGrid')
		->getXml();

		$this->_sendUploadResponse($fileName, $content);
	}

	public function uploadCsvAction() {
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('productlist/adminhtml_rule_upload');
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}

	public function importCsvAction(){
		$profile = $this->getRequest()->getParam('file');
		$sub_folder = $this->getRequest()->getParam('subfolder');

		$filepath = Mage::helper("productlist")->getUploadedFile();

		if ($filepath != null) {
			try {
				$stores = Mage::helper("productlist")->getAllStores();
          // import into model
				Mage::getModel('productlist/import_rule')->process($filepath, $stores);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('CSV Imported Successfully'));
				$this->_redirect('*/*/index');

			} catch (Exception $e) {
				Mage::logException($e);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured importing CSV.'));
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } // end if
        }else{
        	$this->_redirect('*/*/index');
        }
    }

    public function applyRulesAction(){
    	$rules = Mage::getModel('productlist/rule')->getCollection();
    	foreach ($rules as $_rule) {
    		try{
    			$_rule->save();
    		}catch(Exception $e){
    			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured while apply rules.'));
    			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    			$this->_redirect('*/*/index');
    		}
    	}
    	Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('Apply rules successfully'));
    	$this->_redirect('*/*/index');
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
    	$response = $this->getResponse();
    	$response->setHeader('HTTP/1.1 200 OK','');
    	$response->setHeader('Pragma', 'public', true);
    	$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
    	$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
    	$response->setHeader('Last-Modified', date('r'));
    	$response->setHeader('Accept-Ranges', 'bytes');
    	$response->setHeader('Content-Length', strlen($content));
    	$response->setHeader('Content-type', $contentType);
    	$response->setBody($content);
    	$response->sendResponse();
    	die;
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'new':
            case 'add':
            case 'edit':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/productlist/add');
                break;
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/productlist/save');
                break;
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/productlist/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/productlist/items');
                break;
        }
    }
}