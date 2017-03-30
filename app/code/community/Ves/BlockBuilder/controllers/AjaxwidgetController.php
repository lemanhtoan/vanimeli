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
 * @package    Ves_FAQ
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves Tempcp Extension
 *
 * @category   Ves
 * @package    Ves_Tempcp
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_BlockBuilder_AjaxwidgetController extends Mage_Core_Controller_Front_Action
{

    public function genAction() {
        $widget_shortcode = $this->getRequest()->getPost('shortcode');
        $widget_shortcode = str_replace(" ","+", $widget_shortcode);
        $widget_shortcode = base64_decode($widget_shortcode);
        
        $html = "";
        $status = false;
        if($widget_shortcode) {
            $html = $this->_renderWidgetShortcode($widget_shortcode);
            $status = true;
        }
        $data = array();
        $data['html'] = $html;
        $data['status'] = $status;

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
    }

    private function _renderWidgetShortcode( $shortcode = "") {
        if($shortcode) {
            $processor = Mage::helper('cms')->getPageTemplateProcessor();
            return $processor->filter($shortcode);
        }
        return;
    }
}