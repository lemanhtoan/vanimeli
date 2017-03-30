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
class Ves_ProductList_IndexController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if( !Mage::getStoreConfigFlag('productlist/general_setting/show') ) {
			$this->norouteAction();
		}
	}

	protected function _initRule()
	{
		$ruleId = (int) $this->getRequest()->getParam('id', false);
		if (!$ruleId) {
			return false;
		}
		$rule = Mage::getModel('productlist/rule')
		->setStoreId(Mage::app()->getStore()->getId())
		->load($ruleId);
		return $this;
	}

	public function indexAction()
	{
		if ($this->_initRule()) {
			$this->loadLayout();
			$this->renderLayout();
		}
		elseif (!$this->getResponse()->isRedirect()) {
			$this->_forward('noRoute');
		}
	}
}