<?php
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'website_id','int(10)');
$installer->getConnection()->addColumn($this->getTable('giftcards/giftcards'),'date_end','date');

$attributeId = $installer->getAttribute('catalog_product', 'wts_gc_expired', 'attribute_id');
if(!$attributeId) {
    $installer->addAttribute('catalog_product', 'wts_gc_expired', array(
        'group' => 'General',
        'sort_order' => 200,
        'backend' => '',
        'type' => 'varchar',
        'input' => 'text',
        'label' => 'Gift Card Expiration Date',
        'note' => "Count Day's after Activate Gift Card.",
        'required' =>false,
        'visible' =>true,
        'visible_on_front' => false,
        'apply_to' => Webtex_Giftcards_Model_Product_Type::TYPE_GIFTCARDS_PRODUCT
    ));
}


$this->endSetup();
