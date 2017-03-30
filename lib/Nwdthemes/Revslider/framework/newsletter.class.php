<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 * @version   1.0.0
 */
 
if( !defined( 'Nwdthemes_Revslider_Helper_Framework::ABSPATH') ) exit();

if(!class_exists('ThemePunch_Newsletter')) {
	 
	class ThemePunch_Newsletter {
	
		protected static $remote_url	= 'http://newsletter.themepunch.com/';
		protected static $subscribe		= 'subscribe.php';
		protected static $unsubscribe	= 'unsubscribe.php';
		
		public function __construct(){
			
		}
		
		
		/**
		 * Subscribe to the ThemePunch Newsletter
		 * @since: 1.0.0
		 **/
		public static function subscribe($email){
			global $wp_version;
			
			$request = Mage::helper('nwdrevslider/framework')->wp_remote_post(self::$remote_url.self::$subscribe, array(
				'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
				'timeout' => 15,
				'body' => array(
					'email' => urlencode($email)
				)
			));
			
			if(!Mage::helper('nwdrevslider/framework')->is_wp_error($request)) {
				if($response = json_decode($request['body'], true)) {
					if(is_array($response)) {
						$data = $response;
						
						return $data;
					}else{
						return false;
					}
				}
			}
		}
		
		
		/**
		 * Unsubscribe to the ThemePunch Newsletter
		 * @since: 1.0.0
		 **/
		public static function unsubscribe($email){
			global $wp_version;
			
			$request = Mage::helper('nwdrevslider/framework')->wp_remote_post(self::$remote_url.self::$unsubscribe, array(
				'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
				'timeout' => 15,
				'body' => array(
					'email' => urlencode($email)
				)
			));
			
			if(!Mage::helper('nwdrevslider/framework')->is_wp_error($request)) {
				if($response = json_decode($request['body'], true)) {
					if(is_array($response)) {
						$data = $response;
						
						return $data;
					}else{
						return false;
					}
				}
			}
		}
		
	}
}

?>