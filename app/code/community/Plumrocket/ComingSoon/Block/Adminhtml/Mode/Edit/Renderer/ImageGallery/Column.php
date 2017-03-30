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


class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Renderer_ImageGallery_Column
    extends Mage_Adminhtml_Block_Widget_Grid_Column
{
    protected $_rowKeyValue = null;

	public function getId() 
	{
		return sprintf('%s[%s][%s]',
            $this->getGrid()->getContainerFieldId(),
            $this->_rowKeyValue,
            parent::getId()
        );
	}

    public function getRowField(Varien_Object $row)
    {
        if (!is_null($this->getGrid()->getRowKey())) {
            $this->_rowKeyValue = $row->getData($this->getGrid()->getRowKey());
        }
        if (!$this->_rowKeyValue) {
            return '';
        }
        return parent::getRowField($row);
    }

	public function getFieldName()
	{
		return $this->getId();
	}

	public function getHtmlName()
	{
		return $this->getId();
	}

	public function getName()
	{
		return $this->getId();
	}
}
