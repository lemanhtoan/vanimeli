<?php

class Magestore_Affiliateplusprogram_Block_Adminhtml_Program_Renderer_Account extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        return sprintf('<a href="%s" title="%s">%s</a>',
                //Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
                $this->getUrl('adminhtml/affiliateplus_account/edit', array(
                    '_current' => true,
                    'id' => $row->getAccountId(),
                    'store' => $this->getRequest()->getParam('store'),
                )), Mage::helper('affiliateplusprogram')->__('View Affiliate Account Details'), $row->getAccountName()
        );
    }

}
