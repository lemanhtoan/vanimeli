<?php
class NextBits_CustomerActivation_Model_Adminhtml_System_Config_Source_Customer_Group_Multiselect
{
    protected $_options;
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')
                ->setRealGroupsFilter()
                ->loadData()->toOptionArray();
        }
        return $this->_options;
    }
}