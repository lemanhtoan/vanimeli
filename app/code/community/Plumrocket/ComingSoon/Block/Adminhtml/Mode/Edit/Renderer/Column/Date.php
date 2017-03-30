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


class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Renderer_Column_Date extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

	public function render(Varien_Object $row)
    {
        $date = new Varien_Data_Form_Element_Date(array(
            'name'      => $this->getColumn()->getId(),
            'html_id'      => $this->getColumn()->getId(),
            'label'     => $this->__('Enable Form Fields'),
            'note'      => 'Selected field will be displayed on sign-up form. Please note, "Email" is required field',

            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::helper('comingsoon')->getDateTimeFormat(),
            'time'      => true,
            'value'     => $row->getData($this->getColumn()->getIndex()),
        ));

        $html = $date
        	->setForm($this->getForm())
        	// ->setHtmlId($this->getColumn()->getId())
        	->getElementHtml();
        
        unset($date);

        return $html;
    }

}