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


class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Renderer_Field
    extends Mage_Adminhtml_Block_System_Config_Form_Field
    implements Varien_Data_Form_Element_Renderer_Interface
{

    protected $_elementHtml = '';
    protected $_hideLabel = '';

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $element->getValue();
        if(is_array($value) && isset($value['value'])) {
            $element->setValue($value['value']);
        }

        return $element->getElementHtml();
    }

    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $id = $element->getHtmlId();

        $html = '';

        if(!$this->_hideLabel) {
            $html .= '<td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
        }

        //$isDefault = !$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store');
        $isMultiple = $element->getExtType()==='multiple';

        // replace [value] with [inherit]
        $namePrefix = preg_replace('#\[value\](\[\])?$#', '', $element->getName());

        $options = $element->getValues();

        $addInheritCheckbox = false;
        if ($this->_getCanUseWebsiteValue($element)) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Website');
        }
        elseif ($this->_getCanUseDefaultValue($element)) {
            $addInheritCheckbox = true;
            $checkboxLabel = Mage::helper('adminhtml')->__('Use Default');
        }
        elseif (!is_null($element->getScope()) && ($this->getScopeWebsite() || $this->getScopeStore())) {
            return '';
        }

        if ($addInheritCheckbox) {
            $inherit = $element->getInherit()==1 ? 'checked="checked"' : '';
            if ($inherit) {
                $element->setDisabled(true);
            }
        }

        if ($element->getTooltip()) {
            $html .= '<td class="value with-tooltip">';
            $html .= $this->_getElementHtml($element);
            $html .= '<div class="field-tooltip"><div>' . $element->getTooltip() . '</div></div>';
        } else {
            $html .= '<td class="value"'. ($this->_hideLabel? ' colspan="2"' : '') .'>';
            if($this->_elementHtml) {
                $html .= $this->_elementHtml;
            }else{
                $html .= $this->_getElementHtml($element);
            }
        };
        if ($element->getComment()) {
            $html.= '<p class="note" id="note_'. $element->getHtmlId() .'"><span>'.$element->getComment().'</span></p>';
        }elseif ($element->getNote()) {
            $html.= '<p class="note" id="note_'. $element->getHtmlId() .'"><span>'.$element->getNote().'</span></p>';
        }
        $html.= '</td>';

        if(!is_null($element->getScope())) {
            if ($addInheritCheckbox) {

                $defText = $element->getDefaultValue();

                // default value
                $html.= '<td class="use-default">';
                $html.= '<input id="' . $id . '_inherit" name="'
                    . $namePrefix . '[inherit]" type="checkbox" value="1" class="checkbox config-inherit" '
                    . $inherit . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';
                $html.= '<label for="' . $id . '_inherit" class="inherit" title="'
                    . htmlspecialchars($defText) . '">' . $checkboxLabel . '</label>';
                $html.= '</td>';
            }

            $html.= '<td class="scope-label">';
            $html .= $this->_getScopeLabel($element->getScope());
            $html.= '</td>';

            $html.= '<td class="">';
            if ($element->getHint()) {
                $html.= '<div class="hint" >';
                $html.= '<div style="display: none;">' . $element->getHint() . '</div>';
                $html.= '</div>';
            }
            $html.= '</td>';
        }

        return $this->_decorateRowHtml($element, $html);
    }

    public function setHtml($html)
    {
        $this->_elementHtml = $html;
        return $this;
    }

    public function hideLabel($flag = true)
    {
        $this->_hideLabel = (bool)$flag;
        return $this;
    }


    /**
     * Decorate field row html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml($element, $html)
    {
        return '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
    }

    protected function _getScopeLabel($scope)
    {
        $scopes = array(
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE      => Mage::helper('adminhtml')->__('[STORE VIEW]'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE    => Mage::helper('adminhtml')->__('[WEBSITE]'),
            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL     => Mage::helper('adminhtml')->__('[GLOBAL]'),
        );

        if (is_null($scope) || !isset($scopes[$scope])) {
            $scope = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL;
        }

        return $scopes[$scope];
    }

    protected function _getCanUseWebsiteValue($element)
    {
        if($element->getCanUseWebsiteValue()) {
            return true;
        }

        if($this->getScopeStore() && $element->getScope() === Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE) {
            return true;
        }

    }

    protected function _getCanUseDefaultValue($element)
    {
        if($element->getCanUseDefaultValue()) {
            return true;
        }

        if( ($this->getScopeWebsite() && !$this->getScopeStore()
            && ($element->getScope() === Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE
                || $element->getScope() === Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE))
            )
        {
            return true;
        }

    }

    public function getScope()
    {
        $request = Mage::app()->getRequest();
    }

    public function getScopeWebsite()
    {
        return Mage::app()->getRequest()->getParam('website');
    }

    public function getScopeStore()
    {
        return Mage::app()->getRequest()->getParam('store');
    }

}