<?php

/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * RewardpointsReferfriends Refer Gmail
 * 
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_Rewardpointsreferfriends_Model_Refer_Gmail extends Zend_Oauth_Consumer
{
	protected $_options = null;
	
	public function __construct(){
		$this->_config = new Zend_Oauth_Config;
		$this->_options = array(
			'consumerKey'       => $this->_getConsumerKey(),
			'consumerSecret'    => $this->_getConsumerSecret(),
			'signatureMethod'   => 'HMAC-SHA1',
			'version'           => '1.0',
			'requestTokenUrl'   => 'https://www.google.com/accounts/OAuthGetRequestToken',
			'accessTokenUrl'    => 'https://www.google.com/accounts/OAuthGetAccessToken',
			'authorizeUrl'      => 'https://www.google.com/accounts/OAuthAuthorizeToken'
		);
		$this->_config->setOptions($this->_options);
	}
		
	public function _getHelper(){
		return Mage::helper('rewardpointsreferfriends');
	}
	
	protected function _getConsumerKey(){
		return $this->_getHelper()->getReferConfig('google_consumer_key');
	}
	
	protected function _getConsumerSecret(){
		return $this->_getHelper()->getReferConfig('google_consumer_secret');
	}
	
	public function setCallbackUrl($url){
		$this->_config->setCallbackUrl($url);
	}
	
	public function getOptions(){
		return $this->_options;
	}
	
	public function getCoreSession(){
		return Mage::getSingleton('core/session');
	}
	
	public function getGmailRequestToken(){
		return $this->getCoreSession()->getRewardGmailRequestToken();
	}
	
	public function setGmailRequestToken($token){
		$this->getCoreSession()->setRewardGmailRequestToken($token);
		return $this;
	}
	
	public function isAuth(){
		$requestToken = $this->getGmailRequestToken();
		$request = Mage::app()->getRequest();
		if ($requestToken && $request->getParam('oauth_token') && $request->getParam('oauth_verifier'))
			return true;
		return false;
	}
	
	public function getAuthUrl(){
		$this->setCallbackUrl(Mage::getUrl('*/*/gmail'));
		$token = $this->getRequestToken(array('scope' => 'https://www.google.com/m8/feeds/'));
		$this->setGmailRequestToken(serialize($token));
		$url='https://accounts.google.com/o/oauth2/auth?client_id='.$this->_options['consumerKey'].'&redirect_uri='.  urlencode(Mage::getUrl("rewardpointsreferfriends/index/gmail")).'&scope=https://www.google.com/m8/feeds/&response_type=code';
		return $url;
		return $this->getRedirectUrl();
	}
}