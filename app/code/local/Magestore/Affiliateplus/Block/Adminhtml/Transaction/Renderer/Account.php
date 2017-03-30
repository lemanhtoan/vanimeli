<?php 
class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Renderer_Account
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row) 
	{
		if($row->getAccountId())
			return sprintf('
				<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/affiliateplus_account/edit/', array('_current'=>true, 'id' => $row->getAccountId())),      //Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
				Mage::helper('affiliateplus')->__('View Affiliate Account Details'),
				$row->getAccountEmail()
			);
		else
			return sprintf('%s', $row->getAccountEmail());	
	}
}