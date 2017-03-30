<?php
/**
 * Webtex
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Webtex EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.webtex.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@webtex.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.webtex.com/ for more information
 * or send an email to sales@webtex.com
 *
 * @category   Webtex
 * @package    Webtex_GiftCards
 * @copyright  Copyright (c) 2016 Webtex (http://www.webtexsoftware.com/)
 * @license    http://www.webtexsoftware.com/LICENSE-1.0.html
 */

/**
 * Gift Cards extension
 *
 * @category   Webtex
 * @package    Webtex_GiftCards
 * @author     Webtex Dev Team <dev@webtexsoftware.com>
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('giftcards/giftcards')} MODIFY COLUMN `card_amount` decimal(12,2) ;
ALTER TABLE {$this->getTable('giftcards/giftcards')} MODIFY COLUMN `card_balance` decimal (12,2);
ALTER TABLE {$this->getTable('giftcards/order')} MODIFY COLUMN `discounted` decimal (12,2) ;
");

$installer->endSetup();
