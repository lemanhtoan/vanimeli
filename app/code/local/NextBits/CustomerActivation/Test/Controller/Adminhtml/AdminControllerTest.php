<?php
class NextBits_CustomerActivation_Test_Controller_Adminhtml_AdminControllerTest
    extends NextBits_CustomerActivation_Test_Controller_Adminhtml_AbstractController
{
    protected function _initCustomer($customerId)
    {
        $value = $this->expected("initial-status-%s", $customerId)->getCustomerActivated();
        $attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'customer_activated');
        $con = Mage::getSingleton('core/resource')->getConnection('customer_write');
        $table = $attribute->getBackend()->getTable();

        $where = array();
        $where[] = $con->quoteInto('entity_id = ?', $customerId);
        $where[] = $con->quoteInto('attribute_id = ?', $attribute->getId());

        if (is_null($value)) {
            $con->delete($table, implode(' AND ', $where));

        } else {
            $select = $con->select()->from($table, new Zend_Db_Expr('COUNT(*)'))
                ->where('entity_id = ?', $customerId)
                ->where('attribute_id = ?', $attribute->getId());
            $exists = $con->fetchOne($select);

            if ($exists) {
                $con->update($table, array('value' => $value), implode(' AND ', $where));

            } else {
                $con->insert($table, array(
                    'entity_id' => $customerId,
                    'entity_type_id' => $attribute->getEntity()->getId(),
                    'attribute_id' => $attribute->getId(),
                    'value' => $value
                ));
            }
        }
    }
    public function assertInitialStatus($testCustomerIds)
    {
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $testCustomerIds))
            ->addAttributeToSelect('customer_activated');

        foreach ($testCustomerIds as $customerId) {
            $customer = $customers->getItemById($customerId);
            $expectedStatus = $this->expected("initial-status-%s", $customerId)->getCustomerActivated();
            $realStatus = $customer->getData('customer_activated');
            $message = sprintf("Expected customer %d to initially be %s but found to be %s",
                $customer->getId(),
                ($expectedStatus ? 'activated' : 'deactivated'),
                ($realStatus ? 'activated' : 'deactivated')
            );

            $this->assertEquals($expectedStatus, $realStatus, $message);
        }
    }
    public function massActivation($testCustomerIds, $postCustomerIds, $status)
    {
        $this->_initCustomer(3);

        $this->assertInitialStatus($testCustomerIds);

        $params = array(
            '_store' => 'admin',
            '_query' => array(
                'customer' => $postCustomerIds,
                'customer_activated' => (int) $status
            )
        );

        $this->dispatch('customeractivation/admin/massActivation', $params);

        $expectations = $this->expected(
            "%s-%s-%d", implode(',', $testCustomerIds), implode(',', $postCustomerIds), $status
        );

        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $testCustomerIds))
            ->addAttributeToSelect('customer_activated');

        foreach ($testCustomerIds as $customerId) {
            $customer = $customers->getItemById($customerId);
            $expectedStatus = $expectations->getData('customer' . $customerId);
            $realStatus = $customer->getData('customer_activated');
            $message = sprintf("Expected customer %d to be %s but found to be %s",
                $customer->getId(),
                ($expectedStatus ? 'activated' : 'deactivated'),
                ($realStatus ? 'activated' : 'deactivated')
            );

            $this->assertEquals($expectedStatus, $realStatus, $message);
        }
    }
}