<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_Model_System_Config_Source_Page
{

    protected $_options = null;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_getOptions();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = array();
        foreach ($this->_getOptions() as $option) {
            $options[ $option['value'] ] = $option['label'];
        }

        return $options;
    }

    protected function _getOptions()
    {
        $storeId = 0;
        if(is_null($this->_options)) {
            $collection = Mage::getSingleton('cms/page')->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->setOrder('sort_order', 'asc');

            if($storeId) {
                $collection->addStoreFilter($storeId);
            }

            $options = array();
            foreach ($collection as $item) {
                if($item->getIdentifier() == 'home' || $item->getIdentifier() == Mage_Cms_Model_Page::NOROUTE_PAGE_ID) continue;
                $options[] = array('value' => $item->getIdentifier(), 'label' => $item->getTitle());
            }

            $this->_options = $options;
        }

        return $this->_options;
    }

}