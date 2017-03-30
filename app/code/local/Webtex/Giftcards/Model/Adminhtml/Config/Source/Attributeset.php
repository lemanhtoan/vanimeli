<?php
 
class Webtex_Giftcards_Model_Adminhtml_Config_Source_Attributeset
{

    /**
     * Get available options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')
                      ->addVisibleFilter();
        $data = $collection->getData();
        $entityTypeId = $data[0]['entity_type_id'];
        
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityTypeId);
            
        $attributes = array();
        $attributes[] = array('value' => 0, 'label' => '--Select Attribute Set--');
        foreach($collection as $item){
           $attributes[] = array('value' => $item->getAttributeSetId(), 'label' => $item->getAttributeSetName());
        }
        
        return $attributes;
    }

}
