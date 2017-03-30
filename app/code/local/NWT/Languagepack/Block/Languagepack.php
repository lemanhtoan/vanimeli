<?php
class NWT_Languagepack_Block_Languagepack extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getLanguagepack()     
     { 
        if (!$this->hasData('languagepack')) {
            $this->setData('languagepack', Mage::registry('languagepack'));
        }
        return $this->getData('languagepack');
        
    }
}