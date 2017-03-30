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


class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Renderer_ImageGallery
    extends Mage_Adminhtml_Block_Widget_Grid
    implements Varien_Data_Form_Element_Renderer_Interface
{
    protected $_element;
    protected $_containerFieldId = null;
    protected $_rowKey = null;

    // ******************************************
    // *                                        *
    // *           Grid functions               *
    // *                                        *
    // ******************************************
    public function __construct()
    {
        parent::__construct();
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setMessageBlockVisibility(false);
    }

    public function addColumn($columnId, $column)
    {
        if (!$this->getContainerFieldId() || !$this->getRowKey()) {
            throw new Exception(Mage::helper('adminhtml')->__('Container Field Id and Row Key must be set.'));
        } elseif (is_array($column)) {
            $column['sortable'] = false;

            $this->_columns[$columnId] = $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_imageGallery_column')
                ->setData($column)
                ->setGrid($this);
        } else {
            throw new Exception(Mage::helper('adminhtml')->__('Wrong column format.'));
        }

        $this->_columns[$columnId]->setId($columnId);
        $this->_lastColumnId = $columnId;
        return $this;
    }

    public function canDisplayContainer()
    {
        return false;
    }

    protected function _prepareLayout()
    {
        $this->setMessagesBlock($this->getLayout()->createBlock('core/messages'));
        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }

    public function setArray($array)
    {
        $collection = new Varien_Data_Collection();
        foreach ($array as $item) {
            if (! $item instanceof Varien_Object) {
                $item = new Varien_Object($item);
            }
            $collection->addItem($item);
        }
        $this->setCollection($collection);
        return $this;
    }

    public function getRowKey()
    {
        return $this->_rowKey;
    }

    public function setRowKey($key)
    {
        $this->_rowKey = $key;
        return $this;
    }

    public function getContainerFieldId()
    {
        return $this->_containerFieldId;
    }

    public function setContainerFieldId($name)
    {
        $this->_containerFieldId = $name;
        return $this;
    }

    // ******************************************
    // *                                        *
    // *           Render functions             *
    // *                                        *
    // ******************************************

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_field')
            ->setHtml('<div id="'. $element->getHtmlId() .'"><span>'. $element->getLabel() .'</span>' . $this->toHtml() . '</div>')
            ->hideLabel()
            ->render($element);

        /*return '
            <tr>
                <!-- <td class="label">' . $element->getLabelHtml() . '</td> -->
                <td class="value" colspan="2"><div id="'. $element->getHtmlId() .'"><span>'. $element->getLabel() .'</span>' . $this->toHtml() . '</div></td>
            </tr>';*/
    }

    public function setElement(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this;
    }

    public function getElement()
    {
        return $this->_element;
    }
}