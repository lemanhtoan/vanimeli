<?php

class NextBits_Wholesale_Adminhtml_WholesaleController extends Mage_Adminhtml_Controller_Action
{
	public function createwebsiteAction()
	{		
		$websiteData = Array(
			'name' => 'Wholesale Website' ,
			'code' => 'wholesale_website' ,   
		);

		$websiteModel = Mage::getModel('core/website');
		$websiteModel->setData($websiteData);
		$websiteModel->save();

		$websiteModelUpdate = Mage::getModel('core/website')->getCollection()->getData();
		foreach($websiteModelUpdate as $webModel) {
			if($webModel['code'] == 'wholesale_website') {
				$websiteId = $webModel['website_id'];
			}
		}

		$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
		$groupData = Array(
			'website_id' => $websiteId,
			'name' => 'Wholesale Store',
			'root_category_id' => $rootCategoryId,    
		);

		$groupModel = Mage::getModel('core/store_group');
		$groupModel->setData($groupData);
		$groupModel->save();
		
		$groupModelUpdate = Mage::getModel('core/store_group')->getCollection()->getData();
		foreach($groupModelUpdate as $grpModel) {
			if($grpModel['website_id'] == $websiteId) {
				$group_id = $grpModel['group_id'];
			}
		}

		$storeData = Array
		(
			'group_id' => $group_id,
			'website_id' => $websiteId,
			'name' => 'Wholesale Store View',
			'code' => 'wholesale_store_view',
			'is_active' => 1,	
		);

		$storeModel = Mage::getModel('core/store');
		$storeModel->setData($storeData);
		$storeModel->save();
		
		/* set config */
		$config = new Mage_Core_Model_Config();
		
		$baseURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		
		$config->saveConfig('web/unsecure/base_url', $baseURL, 'default', 0);
		$config->saveConfig('web/unsecure/base_url', $baseURL.'b2b/', 'websites', $websiteId);
		$config->saveConfig('web/unsecure/base_link_url', '{{unsecure_base_url}}', 'default', 0);
		$config->saveConfig('web/unsecure/base_link_url', '{{unsecure_base_url}}', 'websites', $websiteId);
		$config->saveConfig('web/unsecure/base_skin_url', '{{unsecure_base_url}}skin/', 'default', 0);
		$config->saveConfig('web/unsecure/base_skin_url', '{{unsecure_base_url}}../skin/', 'websites', $websiteId);
		$config->saveConfig('web/unsecure/base_media_url', '{{unsecure_base_url}}media/', 'default', 0);
		$config->saveConfig('web/unsecure/base_media_url', '{{unsecure_base_url}}../media/', 'websites', $websiteId);
		$config->saveConfig('web/unsecure/base_js_url', '{{unsecure_base_url}}js/', 'default', 0);
		$config->saveConfig('web/unsecure/base_js_url', '{{unsecure_base_url}}../js/', 'websites', $websiteId);

		$config->saveConfig('web/secure/base_url', $baseURL, 'default', 0);
		$config->saveConfig('web/secure/base_url', $baseURL.'b2b/', 'websites', $websiteId);
		$config->saveConfig('web/secure/base_link_url', '{{secure_base_url}}', 'default', 0);
		$config->saveConfig('web/secure/base_link_url', '{{secure_base_url}}', 'websites', $websiteId);
		$config->saveConfig('web/secure/base_skin_url', '{{secure_base_url}}skin/', 'default', 0);
		$config->saveConfig('web/secure/base_skin_url', '{{secure_base_url}}../skin/', 'websites', $websiteId);
		$config->saveConfig('web/secure/base_media_url', '{{secure_base_url}}media/', 'default', 0);
		$config->saveConfig('web/secure/base_media_url', '{{secure_base_url}}../media/', 'websites', $websiteId);
		$config->saveConfig('web/secure/base_js_url', '{{secure_base_url}}js/', 'default', 0);
		$config->saveConfig('web/secure/base_js_url', '{{secure_base_url}}../js/', 'websites', $websiteId);
		
		/* set price scope website */
		$config->saveConfig('catalog/price/scope', '1', 'default', 0);
		
		/* reindex /clean cache */
		Mage::getConfig()->reinit();
		Mage::app()->reinitStores();
		Mage::getConfig()->cleanCache();
		
		$this->_redirectReferer();
	}
}