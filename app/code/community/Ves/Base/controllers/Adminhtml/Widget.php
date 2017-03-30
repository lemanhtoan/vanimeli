<?php
/**
 * Widgets management controller
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */

require_once(Mage::getModuleDir('controllers','Mage_Widget').DS.'Adminhtml'.DS.'WidgetController.php');
//require_once 'Mage/Widget/controllers/Adminhtml/WidgetController.php';

class Ves_Base_Adminhtml_WidgetController extends Mage_Widget_Adminhtml_WidgetController
{
    /**
     * Wisywyg widget plugin main page
     */
    public function indexAction()
    {
        $is_venus_widget = $this->getRequest()->getParam('ves_widget');
        if($is_venus_widget) {
            // save extra params for widgets insertion form
            $skipped = $this->getRequest()->getParam('skip_widgets');
            $skipped = Mage::getSingleton('widget/widget_config')->decodeWidgetsFromQuery($skipped);

            Mage::register('skip_widgets', $skipped);

            $this->loadLayout('empty')->renderLayout();
        } else {
            parent::indexAction();
        }
       
    }

    /**
     * Ajax responder for loading plugin options form
     */
    public function loadOptionsAction()
    {
        $is_venus_widget = $this->getRequest()->getParam('ves_widget');
        if($is_venus_widget) {
            try {
                $this->loadLayout('empty');
                if ($paramsJson = $this->getRequest()->getParam('widget')) {
                    $request = Mage::helper('core')->jsonDecode($paramsJson);
                    if (is_array($request)) {
                        $optionsBlock = $this->getLayout()->getBlock('wysiwyg_widget.options');
                        if (isset($request['widget_type'])) {
                            $optionsBlock->setWidgetType($request['widget_type']);
                        }
                        if (isset($request['values'])) {
                            $optionsBlock->setWidgetValues($request['values']);
                        }
                    }
                    $this->renderLayout();
                }
            } catch (Mage_Core_Exception $e) {
                $result = array('error' => true, 'message' => $e->getMessage());
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        } else {
            parent::loadOptionsAction();
        }
        
    }

    /**
     * Format widget pseudo-code for inserting into wysiwyg editor
     */
    public function buildWidgetAction()
    {
        $is_venus_widget = $this->getRequest()->getPost('ves_widget');
        if($is_venus_widget) {
            $type = $this->getRequest()->getPost('widget_type');
            $params = $this->getRequest()->getPost('parameters', array());

            if ('ves_base/widget_alert' == $type) {
                $params['html'] = base64_encode($params['html']);
            }

            if ('ves_base/widget_facebook' == $type) {
                $params['custom_css'] = base64_encode($params['custom_css']);
            }
            
            $asIs = $this->getRequest()->getPost('as_is');
            $html = Mage::getSingleton('widget/widget')->getWidgetDeclaration($type, $params, $asIs);
            $this->getResponse()->setBody($html);
        } else {
            parent::buildWidgetAction();
        }
    }
    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'buildWidget':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/base/buildwidgets');
                break;
            case 'loadOptions':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/base/loadoptions');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/base/widgets');
                break;
        }
    }
}
