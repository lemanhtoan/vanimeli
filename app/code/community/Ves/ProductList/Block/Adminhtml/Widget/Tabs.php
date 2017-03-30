<?php

/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Widget_Tabs extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Ves_ProductList_Block_Adminhtml_Widget_Renderer_Field_Rule
     */
    protected $_ruleRenderer;

    public function __construct()
    {
        $this->addColumn('ruleId', array(
            'type' => 'text',
            'label' => Mage::helper('productlist')->__('Rule'),
            'style' => 'width:120px',
            'renderer' => $this->_getRuleRenderer()
            ));

        $this->addColumn('title', array(
            'type' => 'select',
            'label' => Mage::helper('productlist')->__('Title'),
            'style' => 'width:300px',
            ));

        $this->addColumn('class', array(
            'type' => 'select',
            'label' => Mage::helper('productlist')->__('Class'),
            'style' => 'width:100px',
            ));


        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Tab');
        parent::__construct();
        $this->setTemplate('ves_productlist/widget/field/array.phtml');
    }

        /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
        protected function _prepareArrayRow(Varien_Object $row)
        {
            $row->setData(
                'option_extra_attr_' . $this->_getRuleRenderer()->calcOptionHash($row->getData('ruleId')),
                'selected="selected"'
                );
        }

    /**
     * Retrive rule list
     *
     * @return  Ves_ProductList_Block_Adminhtml_Widget_Renderer_Field_Rule
     */
    protected function _getRuleRenderer(){
        if(!$this->_ruleRenderer){
            $this->_ruleRenderer = Mage::app()->getLayout()->createBlock('productlist/adminhtml_widget_renderer_field_rule','',array('is_render_to_js_template' => true)
                );
            $this->_ruleRenderer->setClass('rule_select');
            $this->_ruleRenderer->setExtraParams('style="width:120px"');
        }
        return $this->_ruleRenderer;
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();

        if($element->getValue()){

            $controller = Mage::app()->getRequest()->getControllerName();
            //Check current page is pagebuilder
            if($controller == 'widget_instance'){
                $value = $element->getValue();
            }else{
                $value = unserialize(base64_decode($element->getValue()));
            }

            if(is_array($value) && count($value)>0){
                foreach ($value as $rowId => $row) {
                    if(is_array($row) && count($row)>0){
                        foreach ($row as $key => $value) {
                            $row[$key] = $this->escapeHtml($value);
                            $row['_id'] = $rowId;
                            $result[$rowId] = new Varien_Object($row);
                            $this->_prepareArrayRow($result[$rowId]);
                        }
                    }
                }
            }
        }

        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }

    /**
     * Get the grid and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
        $this->_arrayRowsCache = null; // doh, the object is used as singleton!
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {

        if (!$this->_isPreparedToRender) {
            $this->_prepareToRender();
            $this->_isPreparedToRender = true;
        }
        if (empty($this->_columns)) {
            throw new Exception('At least one column must be defined.');
        }
        return parent::_toHtml();
    }
}