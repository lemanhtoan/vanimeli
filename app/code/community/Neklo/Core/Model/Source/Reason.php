<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Model_Source_Reason
{
    public function toOptionArray()
    {
        $reasonList = array();
        $reasonList[''] = $this->__('Please select');
        $reasonList['Magento v' . Mage::getVersion()] = $this->__('Magento Related Support');
        $reasonList['New Extension'] = $this->__('Request New Extension Development');

        $moduleList = Mage::helper('neklo_core/extension')->getModuleList();
        foreach ($moduleList as $moduleCode => $moduleData) {
            $moduleTitle = $moduleData['name'] . ' v' . $moduleData['version'];
            $reasonList[$moduleCode . ' ' . $moduleData['version']] =  $this->__('%s Support', $moduleTitle);
        }

        $reasonList['other'] = $this->__('Other Reason');
        return $reasonList;
    }

    public function __()
    {
        $args = func_get_args();
        $helper = Mage::helper('neklo_core');
        return call_user_func_array(array($helper, "__"), $args);
    }
}