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
 * @package     Magestore_RewardPointsBehavior
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * create rewardpointsbehavior table
 * Save current points earned behavior
 */
$installer->run("
UPDATE {$this->getTable('rewardpoints_transaction')} set `action`='registed' where `action`='initialize' ;

");
$installer->copyRewardPointsConfig(array(
    'earn/initialize' => 'group_signandnews/signing_up',
    'earn/newsletter' => 'group_signandnews/newsletter',
    'earn/review' => 'group_review_product/review',
    'earn/review_limit' => 'group_review_product/review_limit',
    'earn/tag' => 'group_tagging_product/tag',
    'earn/tag_limit' => 'group_tagging_product/tag_limit',
    'earn/poll' => 'group_talk_poll/poll',
    'earn/poll_limit' => 'group_talk_poll/poll_limit',
));
$installer->endSetup();

