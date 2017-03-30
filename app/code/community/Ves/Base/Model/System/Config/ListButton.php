<?php
class Ves_Base_Model_System_Config_ListButton{
	public function toOptionArray(){
		return array(
		array('value' => "facebook", 'label'=>Mage::helper('adminhtml')->__('Facebook')),
            array('value' => "twitter", 'label'=>Mage::helper('adminhtml')->__('Twitter')),
            array('value' => "yahoomail", 'label'=>Mage::helper('adminhtml')->__('Y! Mail')),
            array('value' => "zingme", 'label'=>Mage::helper('adminhtml')->__('ZingMe')),
            array('value' => "pinterest", 'label'=>Mage::helper('adminhtml')->__('Pinterest Pin It')),
            array('value' => "print", 'label'=>Mage::helper('adminhtml')->__('Print')),
            array('value' => "email", 'label'=>Mage::helper('adminhtml')->__('Email')),
            array('value' => "tumblr", 'label'=>Mage::helper('adminhtml')->__('Tumblr')),
            array('value' => "linkedin", 'label'=>Mage::helper('adminhtml')->__('LinkedIn')),
            array('value' => "favorites", 'label'=>Mage::helper('adminhtml')->__('Favorites')),
            array('value' => "gmail", 'label'=>Mage::helper('adminhtml')->__('Gmail')),
            array('value' => "google_plusone_share", 'label'=>Mage::helper('adminhtml')->__('Google+ Share')),
            array('value' => "hotmail", 'label'=>Mage::helper('adminhtml')->__('Hotmail')),
            array('value' => "linkshares", 'label'=>Mage::helper('adminhtml')->__('Linkshares')),
            array('value' => "myspace", 'label'=>Mage::helper('adminhtml')->__('Myspace')),
            array('value' => "printfriendly", 'label'=>Mage::helper('adminhtml')->__('PrintFriendly')),
            array('value' => "virb", 'label'=>Mage::helper('adminhtml')->__('Virb')),
            array('value' => "webnews", 'label'=>Mage::helper('adminhtml')->__('Webnews')),
            array('value' => "windows", 'label'=>Mage::helper('adminhtml')->__('Windows Gadgets')),
            array('value' => "wordpress", 'label'=>Mage::helper('adminhtml')->__('WordPress')),
            array('value' => "yigg", 'label'=>Mage::helper('adminhtml')->__('Yigg')),
            array('value' => "ziczac", 'label'=>Mage::helper('adminhtml')->__('ZicZac')),
		);
	}
}