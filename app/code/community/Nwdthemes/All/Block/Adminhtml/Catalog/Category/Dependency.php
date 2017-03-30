<?php

class Nwdthemes_All_Block_Adminhtml_Catalog_Category_Dependency extends Varien_Data_Form_Element_Select
{
	/**
	 * Retrieve Element HTML fragment
	 *
	 * @return string
	 */
	public function getElementHtml()
	{
		$this->setData('after_element_html', sprintf('<script>new categoryAttributeDependency("%s");</script>', $this->getHtmlId()));
		return parent::getElementHtml();
	}
}
