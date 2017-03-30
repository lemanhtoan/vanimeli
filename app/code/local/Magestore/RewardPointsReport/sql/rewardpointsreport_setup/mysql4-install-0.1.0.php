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
 * @package     Magestore_RewardPointsReport
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/** @var $installer Magestore_RewardPoints_Model_Mysql4_Setup */
$installer = $this;

$installer->startSetup();

$installer->copyRewardPointsConfig(array(
    'report/ytd_start'  => 'report/ytd_start',
    'report/mtd_start'  => 'report/mtd_start',
));

$installer->endSetup();
