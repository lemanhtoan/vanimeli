<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_RuleTimeConditions
 */

if (Mage::helper('core')->isModuleEnabled('Amasty_Xcoupon')) {
    class Amasty_RuleTimeConditions_Model_Resource_Rule_Pure extends Amasty_Xcoupon_Model_Salesrule_Mysql4_Rule_Collection {}
} elseif (Mage::helper('core')->isModuleEnabled('Amasty_Coupons')) {
    class Amasty_RuleTimeConditions_Model_Resource_Rule_Pure extends Amasty_Coupons_Model_SalesRule_Mysql4_Rule_Collection {}
} else {
    class Amasty_RuleTimeConditions_Model_Resource_Rule_Pure extends Mage_SalesRule_Model_Resource_Rule_Collection {}
}
