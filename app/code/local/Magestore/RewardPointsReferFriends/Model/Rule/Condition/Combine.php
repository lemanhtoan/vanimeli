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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardPointsReferFriends Model Rule Condition Combile
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function _construct()
    {
        parent::_construct();
        $this->setType('rewardpointsreferfriends/rule_condition_combine');
    }
    /**
     * get child select options
     * @return type
     */
    public function getNewChildSelectOptions(){
    	$orderAttributes = Mage::getModel('rewardpointsreferfriends/rule_condition_order')
    		->loadAttributeOptions()
    		->getAttributeOption();
    	$attributes = array();
    	foreach ($orderAttributes as $attribute => $label){
    		$attributes[] = array(
    			'value'	=> 'rewardpointsreferfriends/rule_condition_order|'.$attribute,
    			'label'	=> $label
    		);
    	}
    	$conditions = parent::getNewChildSelectOptions();
    	$conditions = array_merge_recursive($conditions,array(
    		array(
    			'value'	=> 'salesrule/rule_condition_product_found',
    			'label'	=> Mage::helper('rewardpointsreferfriends')->__('Product attribute combination'),
    		),
    		array(
    			'value'	=> 'salesrule/rule_condition_product_subselect',
    			'label'	=> Mage::helper('rewardpointsreferfriends')->__('Products subselection'),
    		),
    		array(
    			'value'	=> 'rewardpointsreferfriends/rule_condition_combine',
    			'label'	=> Mage::helper('rewardpointsreferfriends')->__('Conditions combination'),
    		),
    		array(
    			'value'	=> $attributes,
    			'label'	=> Mage::helper('rewardpointsreferfriends')->__('Cart Order Attribute'),
    		),
    	));
    	
    	$additional = new Varien_Object();
    	Mage::dispatchEvent('rewardpointsreferfriends_rule_condition_combine', array('additional' => $additional));
    	if ($additionalConditions = $additional->getConditions()){
    		$conditions = array_merge_recursive($conditions,$additionalConditions);
    	}
    	
    	return $conditions;
    }
}
