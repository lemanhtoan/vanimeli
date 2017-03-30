<?php
class NextBits_CustomerActivation_Model_Resource_Customer
    extends Mage_Eav_Model_Entity_Abstract
{
   
    protected function _construct()
    {
        $this->setType('customer');
        $this->setConnection('customer_read', 'customer_write');
        return parent::_construct();
    }

    public function massSetActivationStatus(array $customerIds, $value)
    {
        $customerIds = $this->_getValidCustomerIds($customerIds);
        $changeIds = array();
        if ($customerIds) {
            $attribute = $this->getAttribute('customer_activated');
            $table = $attribute->getBackend()->getTable();
            
            $select = $this->getReadConnection()->select()
                ->from($table, 'entity_id')
                ->where('entity_id IN (?)', $customerIds)
                ->where('attribute_id = ?', $attribute->getId())
                ->where('value = ?', $value);
            $noChangeIds = $this->_getReadAdapter()->fetchCol($select);
            
            $changeIds = array_diff($customerIds, $noChangeIds);
            $select = $this->_getReadAdapter()->select()
                ->from($table, 'entity_id')
                ->where('entity_id IN (?)', $changeIds)
                ->where('attribute_id = ?', $attribute->getId());

            $updateIds = $this->_getReadAdapter()->fetchCol($select);
            $insertIds = array_diff($changeIds, $updateIds);

            if ($updateIds) {
                $cond = $this->_getWriteAdapter()->quoteInto('entity_type_id = ?', $this->getEntityType()->getId());
                $cond .= $this->_getWriteAdapter()->quoteInto(' AND attribute_id = ?', $attribute->getId());
                $cond .= $this->_getWriteAdapter()->quoteInto(' AND entity_id IN (?)', $updateIds);
                $this->_getWriteAdapter()->update($table, array('value' => $value), $cond);
            }
            if ($insertIds) {
                $rows = array();
                foreach ($insertIds as $customerId) {
                    $rows[] = array(
                        'entity_type_id' => $this->getEntityType()->getId(),
                        'attribute_id' => $attribute->getId(),
                        'entity_id' => $customerId,
                        'value' => $value
                    );
                }
                $this->_getWriteAdapter()->insertMultiple($table, $rows);
            }
        }
        return $changeIds;
    }
    protected function _getValidCustomerIds(array $customerIds)
    {
        $column = $this->getEntityIdField();
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable(), $column)
            ->where($column . ' IN (?)', $customerIds);
        return $this->_getReadAdapter()->fetchCol($select);
    }
}