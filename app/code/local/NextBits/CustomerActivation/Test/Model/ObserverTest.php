<?php
class NextBits_CustomerActivation_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case
{
    
    protected $_model;

    protected function setUp()
    {
        $this->app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
    }

    protected function tearDown()
    {
        $this->setCurrentStore('admin');
        if ($this->_model && $this->_model->getId()) {
            $this->_model->delete();
        }
    }

    public function newCustomerActivationState($email, $storeCode, $groupCode, $activeByDefault, $specificGroups)
    {
        $store = $this->app()->getStore($storeCode);
        $store->setConfig(NextBits_CustomerActivation_Helper_Data::XML_PATH_DEFAULT_STATUS, $activeByDefault);
        $store->setConfig(NextBits_CustomerActivation_Helper_Data::XML_PATH_DEFAULT_STATUS_BY_GROUP, $specificGroups);
        $this->setCurrentStore($store);
        $group = Mage::getModel('customer/group')->load($groupCode, 'customer_group_code');
        $this->_model = Mage::getModel('customer/customer');
        $this->_model->setData(array(
            'store_id' => $store->getId(),
            'website_id' => $store->getWebsiteId(),
            'group_id' => $group->getId(),
            'email' => $email,
        ))->save();
        $this->assertEventDispatchedExactly('customer_save_before', 1);
        $this->assertEventDispatchedExactly('customer_save_after', 1);

        $expected = $this->expected("%s-%d-%d-%d", $storeCode, $group->getId(), $activeByDefault, $specificGroups)
            ->getIsActivated();

        $message = sprintf(
            "Expected new customer %s with group %s in store %s to be %s, but found to be %s\n" .
                "All groups default status: %s, %srequire activation for specific groups)",
            $this->_model->getEmail(), $group->getCode(), $store->getCode(),
            ($expected ? 'activated' : 'inactive'),
            ($expected ? 'inactive' : 'active'),
            ($activeByDefault ? 'active' : 'inactive'),
            ($specificGroups ? '' : "don't ")
        );
        $this->assertEquals($expected, $this->_model->getCustomerActivated(), $message);
    }
}