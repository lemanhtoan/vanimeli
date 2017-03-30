<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_Helper_Config extends Mage_Core_Helper_Abstract
{
	protected $_conf;

	/* System */
	public function getIpWhitelist($store = null)
	{
		$list = explode("\n", Mage::getStoreConfig('comingsoon/general/ip_whitelist', $store));
		foreach ($list as &$ip) {
			$ip = trim($ip);
		}
		return $list;
	}

	public function getEmailWelcome($store = null)
	{
		return Mage::getStoreConfig('comingsoon/email/welcome', $store);
	}

	public function getEmailWelcomeTemplate($store = null)
	{
		return 'comingsoon_email_welcome';
	}

	public function enabledMailchimp($store = null)
	{
		return Mage::getStoreConfig('comingsoon/mailchimp/enable', $store);
	}

	public function getMailchimpKey($store = null)
	{
		return Mage::getStoreConfig('comingsoon/mailchimp/key', $store);
	}

	public function getMailchimpList($store = null)
	{
		return explode(',', Mage::getStoreConfig('comingsoon/mailchimp/list', $store));
	}

	public function getMailchimpSendEmail($store = null)
	{
		return (int)Mage::getStoreConfig('comingsoon/mailchimp/send_email', $store);
	}

	/* Magento */

	public function getDesignHeaderLogoSrc($fullPath = true, $store = null)
	{
		if(!$path = Mage::getStoreConfig('design/header/logo_src', $store)) {
			$path = 'images/plumrocket/comingsoon/coming-soon-logo.png';
		}
		return $path;
	}

	public function getDesignHeaderLogoAlt($store = null)
	{
		return Mage::getStoreConfig('design/header/logo_alt', $store);
	}

	/* Adminhtml */

	public function getComingsoonRestrictionsAccessPages($store = null)
	{
		return explode(',',  $this->_getConf($store)->loadParams('comingsoon_restrictions_access_pages'));
	}

	public function getComingsoonSignupFields($onlyEnabled = false, $store = null)
	{
		if(!$data = $this->_getConf($store)->loadParams('comingsoon_signup_fields')) {
			$data = array();
		}

		$helper = Mage::helper('comingsoon');
		$fields = $helper->getSignupFields($data);
		if($onlyEnabled) {
			if($this->getComingsoonSignupMethod() == 'signup') {
				$fields = array('email' => $fields['email']);
			}else{
				$fields = array_filter($fields, create_function('$field', 'return !empty($field["enable"]);'));
			}
		}
		return $fields;
	}

	public function getComingsoonSocialLinks($store = null)
	{
		$social = array(
			'facebook' 	=> '',
			'twitter' 	=> '',
			'linkedin' 	=> '',
			'googleplus'=> '',
			'youtube' 	=> '',
			'github' 	=> '',
			'flickr' 	=> '',
			'pinterest'	=> '',
		);
		$params = $this->_getConf($store)->loadParams();

		foreach ($social as $key => &$link) {
			if(empty($params["comingsoon_social_{$key}_url"])) {
				unset($social[$key]);
				continue;
			}
			$link = $params["comingsoon_social_{$key}_url"];
		}

		return $social;
	}

	public function getComingsoonBackgroundImage($onlyEnabled = false, $store = null)
	{
		return $this->_getBackgroundConf('comingsoon_background_image', $onlyEnabled, $store);
	}

	public function getComingsoonBackgroundVideo($onlyEnabled = false, $store = null)
	{
		return $this->_getBackgroundConf('comingsoon_background_video', $onlyEnabled, $store);
	}

	public function getMaintenanceBackgroundImage($onlyEnabled = false, $store = null)
	{
		return $this->_getBackgroundConf('maintenance_background_image', $onlyEnabled, $store);
	}

	public function getMaintenanceBackgroundVideo($onlyEnabled = false, $store = null)
	{
		return $this->_getBackgroundConf('maintenance_background_video', $onlyEnabled, $store);
	}

	protected function _getBackgroundConf($key, $onlyEnabled, $store)
	{
		if(!$data = $this->_getConf($store)->loadParams($key)) {
			$data = array();
		}

		$fields = $onlyEnabled? Mage::helper('comingsoon')->filterByActive($data) : $data;
		return $fields;
	}

	public function __call($method, $args)
	{
		$helper = Mage::helper('comingsoon');

		switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $helper->underscore(substr($method, 3));
                break;

            case 'ena': // enabled
            	$key = $helper->underscore(substr($method, 7)) . '_enable';
            	break;

            case 'sho': // show
            	$key = $helper->underscore(substr($method, 4)) . '_show';
            	break;
        }

        $store = isset($args[0]) ? $args[0] : null;
        $data = $this->_getConf($store)
        	//->prepareTime(false)
            ->loadParams();

        if(isset($data[$key])) {
        	return $data[$key];
        }
        return null;
	}


	protected function _getConf($store = null)
	{
		if (is_null($this->_conf[$store])) {
			$this->_conf[$store] = Mage::getSingleton('comingsoon/config')->setScope($store);
		}

		return $this->_conf[$store];
	}


}