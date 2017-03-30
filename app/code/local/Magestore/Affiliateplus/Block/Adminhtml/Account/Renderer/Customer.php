<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Renderer_Customer
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	/* Render Grid Column*/
	public function render(Varien_Object $row)
	{
		if($customerId = $row->getCustomerId()) {
			$customer = Mage::getModel('customer/customer')->load($customerId);
			$html = "";
			return sprintf('<a href="%s" title="%s">%s</a>',
				$this->getUrl('adminhtml/customer/edit/', array('_current'=>true, 'id' => $customerId)),
				$customer->getName(),
				$customer->getEmail()
			);
		} else {
			return sprintf('%s', $row->getCustomerEmail());
		}
	}
}