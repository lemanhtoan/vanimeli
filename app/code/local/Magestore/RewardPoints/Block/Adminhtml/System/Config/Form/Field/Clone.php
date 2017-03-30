<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpoints Config Field Clone Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_System_Config_Form_Field_Clone
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_rewardPointsConfigData;
    protected $_rewardPointsConfigRoot;
    
    protected function _construct()
    {
        parent::_construct();
        $this->_rewardPointsConfigRoot = Mage::getConfig()->getNode(null,
            $this->getScope(),
            $this->getScopeCode()
        );
        $this->_rewardPointsConfigData = Mage::getModel('adminhtml/config_data')
            ->setSection($this->getRequest()->getParam('section', ''))
            ->setWebsite($this->getRequest()->getParam('website', ''))
            ->setStore($this->getRequest()->getParam('store', ''))
            ->load();
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        $scope = $this->getData('scope');
        if (is_null($scope)) {
            if ($this->getRequest()->getParam('store', '')) {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES;
            } elseif ($this->getRequest()->getParam('website', '')) {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_WEBSITES;
            } else {
                $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT;
            }
            $this->setData('scope', $scope);
        }
        return $scope;
    }

    /**
     * @return string
     */
    public function getScopeCode()
    {
        $scope = $this->getData('scope_code');
        if (is_null($scope)) {
            if ($this->getRequest()->getParam('store', '')) {
                $scope = $this->getRequest()->getParam('store', '');
            } elseif ($this->getRequest()->getParam('website', '')) {
                $scope = $this->getRequest()->getParam('website', '');
            } else {
                $scope = '';
            }
            $this->setData('scope_code', $scope);
        }
        return $scope;
    }
    
    /**
     * Enter description here...
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $fieldConfig = $element->getFieldConfig();
        $clonePath  = (string) $fieldConfig->clone_path;
        $clonePaths = explode('/', $clonePath);
        
        // Prepare value for cloned element
        $name       = 'groups['.$clonePaths[1].'][fields]['.$clonePaths[2].'][value]';
        if (isset($this->_rewardPointsConfigData[$clonePath])) {
            $data = $this->_rewardPointsConfigData[$clonePath];
            $inherit = false;
        } else {
            $data = $this->_rewardPointsConfigRoot->descend($clonePath);
            $inherit = true;
        }
        if ($fieldConfig->backend_model) {
            $model = Mage::getModel((string)$fieldConfig->backend_model);
            if (!$model instanceof Mage_Core_Model_Config_Data) {
                Mage::throwException('Invalid config field backend model: '.(string)$fieldConfig->backend_model);
            }
            $model->setPath($clonePath)->setValue($data)->afterLoad();
            $data = $model->getValue();
        }
        
        $element->setName($name)
            ->setValue($data)
            ->setInherit($inherit);
        
        // Render Element to HTML
        $html = parent::render($element);
        
        // Prepare Javascript for cloned element
        $cloneId = $element->getHtmlId();
        $origId  = implode('_', $clonePaths);
        $html .= "<script type='text/javascript'>
Event.observe(window, 'load', function() {
    $('$cloneId').observe('change', function(){
        Form.Element.setValue($('$origId'), Form.Element.getValue($('$cloneId')));
    });
    $('$origId').observe('change', function(){
        Form.Element.setValue($('$cloneId'), Form.Element.getValue($('$origId')));
    });";
        if ($element->getCanUseWebsiteValue() || $element->getCanUseDefaultValue()) {
            $html .= "
    $('{$cloneId}_inherit').observe('click', function(){
        var el = $('{$origId}_inherit');
        el.checked = $('{$cloneId}_inherit').checked;
        toggleValueElements(el, Element.previous(el.parentNode));
    });
    $('{$origId}_inherit').observe('click', function(){
        var el = $('{$cloneId}_inherit');
        el.checked = $('{$origId}_inherit').checked;
        toggleValueElements(el, Element.previous(el.parentNode));
    });";
        }
        $html .= "
});
</script>";
        
        return $html;
    }
}
