<?php
/*------------------------------------------------------------------------
 # VenusTheme BannerCountdown Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Widgets_Block_List extends Mage_Core_Block_Template 
{
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_config = '';
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_listDesc = array();
	
	/**
	 * @var string $_config
	 * 
	 * @access protected
	 */
	protected $_show = 0;
	protected $_theme = "";
	
	/**
	 * Contructor
	 */
	public function __construct($attributes = array())
	{
		$this->convertAttributesToConfig($attributes);
		parent::__construct();		
	}

	public function convertAttributesToConfig($attributes = array()) {
		if($attributes) {
			foreach($attributes as $key=>$val) {
				$this->setConfig($key, $val);
			}
		}
	}
	
	public function checkGroupCustomer($groupid){
			//$login = Mage::getSingleton( 'customer/session' )->isLoggedIn(); //Check if User is Logged In
			$check = false;
			foreach ($groupid as $key => $value) {
				$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId(); //Get Customers Group ID
				if($groupId == $value) //My wholesale customer id was 2 So I checked for 2. You can check according to your requirement
				{
				  $check = true;
				}
			}
			return $check;
	}
	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = "", $panel='general'){
		$return = "";
		$value = $this->getData($key);
	    //Check if has widget config data
		if($this->hasData($key) && $value !== null) {
			if($value == "true") {
				return 1;
			} elseif($value == "false") {
				return 0;
			}

			return $value;

		} else {

			if(isset($this->_config[$key])){
				$return = $this->_config[$key];
			}else{
				$return = Mage::getStoreConfig("ves_widgets/$panel/$key");
			}

			if($return == "" && $default) {
				$return = $default;
			}
		}
		return $return;
	}

	/**
     * overrde the value of the extension's configuration
     *
     * @return string
     */
	function setConfig($key, $value) {
		if($value == "true") {
			$value =  1;
		} elseif($value == "false") {
			$value = 0;
		}
		if($value != "") {
			$this->_config[$key] = $value;
		}

		return $this;
	}


}
