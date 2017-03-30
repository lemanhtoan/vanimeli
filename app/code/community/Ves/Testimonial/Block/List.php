<?php
/*------------------------------------------------------------------------
 # VenusTheme Testimonial Module
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Block_List extends Mage_Core_Block_Template
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
		
		parent::__construct();
	}

	/**
     * get value of the extension's configuration
     *
     * @return string
     */
    public function getConfig( $key, $panel='general_setting', $default = ""){
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
            $return = Mage::getStoreConfig("ves_testimonial/$panel/$key");
          }
          if($return == "" && !$default) {
            $return = $default;
          }

        }

        return $return;
    }
    function getListConfig($key, $default =""){
      return $this->getConfig($key,'list_setting', $default);
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
