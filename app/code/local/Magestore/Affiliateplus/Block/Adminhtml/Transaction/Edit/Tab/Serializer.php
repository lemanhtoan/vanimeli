<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Edit_Tab_Serializer extends Mage_Core_Block_Template
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('affiliateplus/transaction/serializer.phtml');
		return $this;
	}

	public function initSerializerBlock($gridName,$hiddenInputName)
	{
		$grid = $this->getLayout()->getBlock($gridName);
		$this->setGridBlock($grid)
			->setInputElementName($hiddenInputName);
	}
}