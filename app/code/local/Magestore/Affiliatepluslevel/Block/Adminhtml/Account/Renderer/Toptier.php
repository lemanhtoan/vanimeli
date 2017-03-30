<?php 
class Magestore_Affiliatepluslevel_Block_Adminhtml_Account_Renderer_Toptier
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
		if($row->getToptierId())
			return sprintf('
				<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/affiliateplus_account/edit/', array('_current'=>true, 'id' => $row->getToptierId())),			//Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
				Mage::helper('affiliatepluslevel')->__('View Account Detail'),
				$row->getToptierName()
			);
		else
			return Mage::helper('affiliatepluslevel')->__('N/A');
	}
}