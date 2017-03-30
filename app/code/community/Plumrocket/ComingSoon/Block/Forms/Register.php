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


class Plumrocket_ComingSoon_Block_Forms_Register extends Mage_Customer_Block_Form_Register
{
	protected $_formFields = array();
	protected $_addressFields = array();

	protected function _prepareLayout()
    {
    	return Mage_Directory_Block_Data::_prepareLayout();
    }
	
	public function showAgree()
	{
		$attribute = Mage::getModel('eav/config')->getAttribute('customer', 'agree');
		return ($attribute && $attribute->getId());
	}
	
	protected function _toHtml()
	{
		$this->_addressFields = Mage::helper('comingsoon')->getAddressFieldsCodes();
		return parent::_toHtml();
	}

	public function createWidget($name) 
	{
		switch ($name) {
			case 'taxvat':
				$blockName = 'customer/widget_taxvat';
				break;
			case 'dob':
				$blockName = 'customer/widget_dob';
				break;
			case 'gender':
				$blockName = 'customer/widget_gender';
				break;
			case 'country_id':
				$blockName = 'directory/data';
				break;
			default:
				$blockName = 'core/template';
				break;
		}

		if (in_array($name, $this->_addressFields)) {
			$template = 'comingsoon/forms/register_address/' . $name . '.phtml';
		} else {
			$template = 'comingsoon/forms/register/' . $name . '.phtml';
		}

		$block = $this->getLayout()
			->createBlock($blockName)
			->setTemplate($template);

		$label = (array_key_exists($name, $this->_formFields))? $this->_formFields[$name]['label']: '';
		$block->setLabel($label);
		$block->setPlaceholder($label);
		return $block;
	}
	
	public function getPostActionUrl()
	{
		$url = $this->getUrl('comingsoon/index/register');
		if (! Mage::app()->getStore()->isCurrentlySecure()) {
	        $url = str_replace('https://', 'http://', $url);
	    }
		return $url;
	}

	public function enabledCountrySwitch()
	{
		return isset($this->_formFields['country_id'])
			&& !empty($this->_formFields['country_id']['enable'])
			&&  isset($this->_formFields['region'])
			&& !empty($this->_formFields['region']['enable']);
	}

	public function getChildHtml($name = '', $useCache = true, $sorted = false)
	{
		if ($name === '') {
			$out = '';
			$i = 0;
			$this->_formFields = Mage::helper('comingsoon/config')->getComingsoonSignupFields(true);
			foreach ($this->_formFields as $name => $item) {
				if (!empty($item['enable'])) {
					$_block = $this->createWidget($name);
					if ($_block) {
						$out .= $_block->toHtml();
						if (++$i == 2) {
							$out .= '<li class="row-special"></li>';
							$i = 0;
						}
					}
				}
			}
			return $out;
		}
		return parent::getChildHtml($name, $useCache, $sorted);
	}

}