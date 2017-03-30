<?php
class NextBits_HidePrice_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List
{
    public function getLoadedProductCollection()
    {
        $oProductCollection = parent::getLoadedProductCollection();
        Mage::dispatchEvent(
            'nextbits_hideprice_product_list_collection_load_after',
            array(
                'product_collection' => $oProductCollection
            )
        );
        return $oProductCollection;
    }
}