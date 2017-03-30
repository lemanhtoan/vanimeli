<?php
class Magestore_RewardPointsRule_Block_Cart_Info extends Mage_Core_Block_Template
{
	const XML_PATH_ENABLE_DESCRIPTION    = 'rewardpoints/rewardpointsrule/earning_description_view';
	
	/**
	 * show description rules in shopping cart page
	 * 
	 * @return boolean
	 */
	public function getCanShowDescription($store = null)
	{
		return Mage::getStoreConfigFlag(self::XML_PATH_ENABLE_DESCRIPTION, $store);
	}
	
	
	public function getCatalogRules(){
		return Mage::registry('rp_catalog_rules');
	}
	
	public function getShoppingCartRules(){
		return Mage::registry('rp_shoppingcart_rules');
	}
}