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


class Plumrocket_ComingSoon_Block_System_Config_CurrentIp extends Mage_Adminhtml_Block_System_Config_Form_Field
{ 
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $ip = Mage::helper('core/http')->getRemoteAddr();
        return '<div id="'. $element->getHtmlId() .'" class="checkboxes" style="border: 1px solid #ccc; padding: 5px; background-color: #fdfdfd;">' . $ip . '</div>';
    }
}
