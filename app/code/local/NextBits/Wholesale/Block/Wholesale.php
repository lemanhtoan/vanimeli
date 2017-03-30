<?php
class NextBits_Wholesale_Block_Wholesale extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
    public function getWholesale()     
    { 
        if (!$this->hasData('wholesale')) {
            $this->setData('wholesale', Mage::registry('wholesale'));
        }
        return $this->getData('wholesale');
        
    }
}