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
class Ves_ProductCarousel_Block_System_Config_Form_Field_WysiwygEditor extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element) {

		$controller = Mage::app()->getRequest()->getControllerName();
		//Check current page is pagebuilder
		if($controller == 'widget_instance'){
			$value = $element->getValue();
		}else{
			$value = base64_decode($element->getValue());
		}

		$class = '';
		if($element->getRequired()){
			$class = 'required-entry';
		}

		$useContainerId = $element->getData('use_container_id');
		return '<tr class="system-fieldset-sub-head-tabs" id="row_'.$element->getHtmlId().'"><td class="label"><label for="'.$element->getHtmlId().'">'.$element->getLabel().'</label></td>
		<td class="value"><textarea id="'.$element->getHtmlId().'" name="'.$element->getName().'" class="textarea '.$class.'" rows="2" cols="15">'.$value.'</textarea><button id="id_'.$element->getHtmlId().'" title="WYSIWYG Editor" type="button" class="scalable btn-wysiwyg" onclick="productcarouselWysiwygEditor.open(\''.Mage::helper("adminhtml")->getUrl('adminhtml/catalog_product/wysiwyg').'\', \''.$element->getHtmlId().'\')" style=""><span><span><span>WYSIWYG Editor</span></span></span></button></td></tr>';
	}
}