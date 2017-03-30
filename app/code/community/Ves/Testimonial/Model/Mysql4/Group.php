<?php
/******************************************************
 * @package Ves map module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Testimonial_Model_Mysql4_Group extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the group_id refers to the key field in your database table.
        $this->_init('ves_testimonial/group', 'group_id');
    }
    
    
    /**
     * Assign page to store views
     *
     * @param Mage_Core_Model_Abstract $object
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $condition = $this->_getWriteAdapter()->quoteInto('group_id = ?', $object->getId());
        // process faq item to store relation
        $this->_getWriteAdapter()->delete($this->getTable('ves_testimonial/group_store'), $condition);
        $stores = (array) $object->getData('stores');

        if($stores){
            foreach ((array) $object->getData('stores') as $store) {
                $storeArray = array ();
                $storeArray['group_id'] = $object->getId();
                $storeArray['store_id'] = $store;
                $this->_getWriteAdapter()->insert(
                    $this->getTable('ves_testimonial/group_store'), $storeArray);
            }   
        }else{
            $storeArray = array ();
            $storeArray['group_id'] = $object->getId();
            $storeArray['store_id'] = $object->getStoreId();
            $this->_getWriteAdapter()->insert(
                    $this->getTable('ves_testimonial/group_store'), $storeArray);
            
        }
        
        return parent::_afterSave($object);
    }
    
}// End Class