<?php
class Ves_Gallery_Model_System_Config_Source_ListCategories {

    public function toOptionArray() {

        $category = Mage::getModel('catalog/category');
        $tree = $category->getTreeModel();
        $tree->load();
        $ids = $tree->getCollection()->getAllIds();
        $arr = array();
        if ($ids) {
            foreach ($ids as $id) {
                $cat = Mage::getModel('catalog/category');
                $cat->load($id);
                $tmp = array();
                $tmp["value"] = $id;
                $tmp["label"] = $cat->getName();
                $arr[] = $tmp;
            }
        }
        return $arr;
    }

}