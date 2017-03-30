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
if(!Mage::helper("productlist")->checkModuleInstalled("Ves_Base")) {
	class Ves_ProductList_Model_Mage_Widget extends Mage_Widget_Model_Widget
	{
		public function getWidgetDeclaration($type, $params = array(), $asIs = true)
		{
			if(isset($params['tabs'])){
				$params['tabs'] = base64_encode(serialize($params['tabs']));
			}

			if(isset($params['productlist_pretext'])){
				$params['productlist_pretext'] = base64_encode(htmlentities($params['productlist_pretext']));
			}
			$params = $this->getWidgetDeclaration2($type, $params, $asIs);

			return parent::getWidgetDeclaration($type, $params, $asIs);
		}

		public function getWidgetDeclaration2($type, $params = array(), $asIs = true)
	    {
	        $field_pattern = array("pretext","shortcode","html","raw_html","content","latestmod_desc","custom_css");
	        foreach ($params as $k => $v) {
	            if(in_array($k, $field_pattern) || preg_match("/^content_(.*)/", $k)){
	                $params[$k] = base64_encode($params[$k]);
	            }
	        }
	        return $params;
	    }
	}
} else {
	class Ves_ProductList_Model_Mage_Widget extends Ves_Base_Model_Mage_Widget
	{
		public function getWidgetDeclaration($type, $params = array(), $asIs = true)
		{
			if(isset($params['tabs'])){
				$params['tabs'] = base64_encode(serialize($params['tabs']));
			}

			if(isset($params['productlist_pretext'])){
				$params['productlist_pretext'] = base64_encode(htmlentities($params['productlist_pretext']));
			}
			return parent::getWidgetDeclaration($type, $params, $asIs);
		}

	}
}