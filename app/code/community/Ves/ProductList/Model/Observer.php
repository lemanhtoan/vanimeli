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
class Ves_ProductList_Model_Observer  extends Varien_Object
{

	public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }

	public function initControllerRouters($observer){
		if($this->isAdmin()) {
            return;
        }


			$request = $observer->getEvent()->getFront()->getRequest();
			if (!Mage::app()->isInstalled()) return;

			$identifier = trim($request->getPathInfo(), '/');
			$condition = new Varien_Object(array(
				'identifier' => $identifier,
				'continue'   => true
				));
			Mage::dispatchEvent('productlist_controller_router_match_before', array(
				'router'    => $this,
				'condition' => $condition
				));
			$identifier = $condition->getIdentifier();
			$identifier = trim($identifier, "/");

			if ($condition->getRedirectUrl()) {
				Mage::app()->getFrontController()->getResponse()
				->setRedirect($condition->getRedirectUrl())
				->sendResponse();
				$request->setDispatched(true);
				return true;
			}

			if (!$condition->getContinue()) return false;
			if($identifier) {
				$identifier = str_replace('.html', '', $identifier);
				$idarray = explode('/',$identifier);
				$rule = Mage::getModel('productlist/rule')->getCollection()
				->addFieldToFilter('identifier',$identifier)
				->addStatusFilter()
				->addDateFilter()
				->addStoreFilter()
				->addCustomerGroupFilter()
				->getFirstItem();

				$data = $rule->getData();
				$show = Mage::getStoreConfig('productlist/general_setting/show');
				if (empty($data) || !$show) {
					return false;
				}
				$request->setModuleName('productlist')
				->setControllerName('rule')
				->setActionName('view')
				->setParam('id',$rule->getId());

				$request->setAlias(
					Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,$identifier.'.html'
					);
				return true;
			}
			return false;
	}

	public function applyAllRule($observer){
		$rules = Mage::getModel('productlist/rule')->getCollection();
		foreach ($rules as $_rule) {
			$_rule->save();
		}
		return $this;
	}

	public function applyAllRuleByCronTab(){
		$rules = Mage::getModel('productlist/rule')->getCollection();
		foreach ($rules as $_rule) {
			$_rule->save();
		}
		return $this;
	}
}