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


class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_CurrentTime extends Mage_Core_Block_Template
{
	protected function _toHtml()
    {
    	$storeId = $this->getRequest()->getParam('store');
        $timestamp = Mage::app()->getLocale()->storeTimeStamp($storeId);
        $is24h = Mage::getSingleton('catalog/product_option_type_date')->is24hTimeFormat();
        $html = '<script type="text/javascript">var storeTimestamp = '. $timestamp .'; var storeTimeFormat24 = '. (int)$is24h .';</script>';
        $format = 'M d, Y '. ($is24h ? 'H:i:s' : 'h:i:s A');
        $html .= '<div class="plcs-current-time">'. $this->__('Current time on store') .'<span>'. date($format, $timestamp) .'</span></div>';
        return $html;
    }
}