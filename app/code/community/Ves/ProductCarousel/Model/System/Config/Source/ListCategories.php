<?php
class Ves_ProductCarousel_Model_System_Config_Source_ListCategories {

    static $arr = array();
    static $tmp = array();

    public function toOptionArray() {

        $arr = $this->getTreeCategories();
        return $arr;
    }


    public function getTreeCategories($parentId = 0,$level = 0, $caret = ' _ '){
        $allCats = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('is_active','1')
                    ->addAttributeToSort('position', 'asc'); 
                    if ($parentId) {
                        $allCats->addAttributeToFilter('parent_id',array('eq' => $parentId));
                    }
                    
                    
                    
        $prefix = "";
        if($level) {
            $prefix = "|_";
            for($i=0;$i < $level; $i++) {
                $prefix .= $caret;
            }
        }
        foreach($allCats as $category)
        {
            if(!isset(self::$tmp[$category->getId()])) {
                self::$tmp[$category->getId()] = $category->getId();
                $tmp["value"] = $category->getId();
                $tmp["label"] = $prefix."(ID:".$category->getId().") ".$category->getName();
                $arr[] = $tmp;
                $subcats = $category->getChildren();
                if($subcats != ''){ 
                    $arr = array_merge($arr, $this->getTreeCategories($category->getId(),(int)$level + 1, $caret.' _ '));
                }
            
            }
            
        }
        return isset($arr)?$arr:array();
    }

}