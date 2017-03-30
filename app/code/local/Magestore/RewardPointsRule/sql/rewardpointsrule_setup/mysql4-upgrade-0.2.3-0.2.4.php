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
 * @package     Magestore_RewardPointsRule
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Magestore_RewardPointsRule_Model_Mysql4_Setup */
$installer = $this;

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

$setup->addAttribute('catalog_product', 'rewardpoints_earn', array(
    'type'                       => 'int',
    'input'                      => 'text',
    'label'                      => 'Number of points earned',
    'required'                   => false,
    'frontend_class'            => 'validate-digits',
    'sort_order'                 => 10,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => false,
    'group'                      => 'General',
    'user_defined'               => true,
));
$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($setup->getAttributeId('catalog_product', 'rewardpoints_earn'));
$attribute->addData(array(
    'apply_to' => array('simple', 'configurable', 'virtual', 'downloadable'),
    'is_used_for_promo_rules' => 1,
    'used_in_product_listing' => 1,
))->save();

$installer->endSetup();
