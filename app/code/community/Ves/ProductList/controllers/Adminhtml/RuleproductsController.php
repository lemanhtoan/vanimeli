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
class Ves_ProductList_Adminhtml_RuleproductsController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()
		->_setActiveMenu('productlist/items')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('Rules Manager'), Mage::helper('adminhtml')->__('Rules Manager'));

		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('productlist/rule')->load($id);

		Mage::register('productlist_data', $model);

		return $this;
	}



	public function indexAction()
	{
		$this->_initAction();

		$rule_model = Mage::registry('productlist_data');

		$this->_title($this->__("Ves Product List"));
		if($title = $rule_model->getTitle()) {
			$this->_title($this->__("Manager Products Of Rule '%s'", $title));
		} else {
			$this->_title($this->__("Manager Rule Products"));
		}
        

		$this->renderLayout();
	}

	public function savePositionAction()
	{
		$productlistIds = $this->getRequest()->getParam('productlist');
		$positions = $this->getRequest()->getParam('positions');
		$rule_id = $this->getRequest()->getParam('id');
		$return = array();
		if(!is_array($productlistIds))
		{
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			$return['error'] = $this->__('Please select item(s)');
		}
		else
		{
			try
			{
				$model  = Mage::getModel('productlist/rule')->load($rule_id);
				
				foreach ($productlistIds as $key => $product_id)
				{
					if(isset($positions[$key])) {
						$model->updateProductPosition( $product_id , $positions[$key]);
					}
				}
				$this->_getSession()->addSuccess(
					$this->__('Total of %d record(s) were successfully updated', count($productlistIds))
				);
				$return['success'] = $this->__('Total of %d record(s) were successfully updated', count($productlistIds));
			}
			catch (Exception $e)
			{
				$this->_getSession()->addError($e->getMessage());
				$return['error'] = $e->getMessage();
			}
		}
		echo Mage::helper('core')->jsonEncode( $return );
		//$this->_redirect('*/*/index');
		return;
	}

	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
       return Mage::getSingleton('admin/session')->isAllowed('vesextensions/productlist/ruleproducts');
    }

}