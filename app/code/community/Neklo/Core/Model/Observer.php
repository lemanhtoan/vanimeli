<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Model_Observer
{
    public function checkUpdate(Varien_Event_Observer $observer)
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            Mage::getModel('neklo_core/feed')->checkUpdate();
        }
    }

    public function sortModuleTabList(Varien_Event_Observer $observer)
    {
        $configContainerBlock = $observer->getBlock();
        if (!$configContainerBlock instanceof Mage_Adminhtml_Block_System_Config_Tabs) {
            return null;
        }
        $tabList = $configContainerBlock->getTabs();
        foreach ($tabList as $tab) {
            if ($tab->getId() !== 'neklo') {
                continue;
            }

            $sectionList = $tab->getSections();
            if (!$sectionList || !$sectionList->getSize()) {
                continue;
            }

            $sectionListArray = $sectionList->toArray();
            $sectionListArray = $sectionListArray['items'];
            usort($sectionListArray, array($this, '_sort'));

            $tab->getSections()->clear();
            foreach ($sectionListArray as $_section) {
                $section = new Varien_Object($_section);
                $section->setId($_section['id']);
                $tab->getSections()->addItem($section);
            }
        }
    }

    protected function _sort($a, $b)
    {
        if ($a['id'] == 'neklo_core') {
            return 1;
        }
        if ($b['id'] == 'neklo_core') {
            return -1;
        }
        return strcasecmp($a['label'], $b['label']);
    }
}