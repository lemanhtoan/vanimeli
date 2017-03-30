<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */
 
if( !defined( 'Nwdthemes_Revslider_Helper_Framework::ABSPATH') ) exit();

class RevSliderUpdate {

	private $plugin_url			= 'http://codecanyon.net/item/slider-revolution-responsive-magento-extension/9332896';
	private $remote_url			= 'http://updates.themepunch.tools/check_for_updates.php';
	private $remote_url_info	= 'http://updates.themepunch.tools/revslider-magento/revslider-magento.php';
    private $remote_temp_active = 'http://updates.themepunch.tools/temp_activate.php';
	private $plugin_slug		= 'revslider_magento';
	private $plugin_path		= 'revslider/revslider.php';
	private $version;
	private $plugins;
	private $option;
	
	
	public function __construct($version) {
		$this->option = $this->plugin_slug . '_update_info';
		$this->_retrieve_version_info();
		$this->version = $version;
		
	}
	
	public function add_update_checks(){
		
		Mage::helper('nwdrevslider/framework')->add_filter('pre_set_site_transient_update_plugins', array(&$this, 'set_update_transient'));
		Mage::helper('nwdrevslider/framework')->add_filter('plugins_api', array(&$this, 'set_updates_api_results'), 10, 3);
		
	}
	
	public function set_update_transient($transient) {
	
		$this->_check_updates();

		if(isset($transient) && !isset($transient->response)) {
			$transient->response = array();
		}

		if(!empty($this->data->basic) && is_object($this->data->basic)) {
			if(version_compare($this->version, $this->data->basic->version, '<')) {

				$this->data->basic->new_version = $this->data->basic->version;
				$transient->response[$this->plugin_path] = $this->data->basic;
			}
		}
		
		return $transient;
	}


	public function set_updates_api_results($result, $action, $args) {
	
		$this->_check_updates();

		if(isset($args->slug) && $args->slug == $this->plugin_slug && $action == 'plugin_information') {
			if(is_object($this->data->full) && !empty($this->data->full)) {
				$result = $this->data->full;
			}
		}
		
		return $result;
	}


	protected function _check_updates() {
		//reset saved options
		//Mage::helper('nwdrevslider/framework')->update_option($this->option, false);
		
		$force_check = false;
		
		if(isset($_GET['checkforupdates']) && $_GET['checkforupdates'] == 'true') $force_check = true;
		
		// Get data
		if(empty($this->data)) {
			$data = Mage::helper('nwdrevslider/framework')->get_option($this->option, false);
			$data = $data ? $data : new stdClass;
			
			$this->data = is_object($data) ? $data : Mage::helper('nwdrevslider/framework')->maybe_unserialize($data);
			
		}
		
		$last_check = Mage::helper('nwdrevslider/framework')->get_option('revslider-update-check');
		if($last_check == false){ //first time called
			$last_check = time();
			Mage::helper('nwdrevslider/framework')->update_option('revslider-update-check', $last_check);
		}
		
		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			$data = $this->_retrieve_update_info();
			
			if(isset($data->basic)) {
				Mage::helper('nwdrevslider/framework')->update_option('revslider-update-check', time());
				
				$this->data->checked = time();
				$this->data->basic = $data->basic;
				$this->data->full = $data->full;
				
				Mage::helper('nwdrevslider/framework')->update_option('revslider-stable-version', $data->full->stable);
				Mage::helper('nwdrevslider/framework')->update_option('revslider-latest-version', $data->full->version);
			}
			
		}
		
		// Save results
		Mage::helper('nwdrevslider/framework')->update_option($this->option, $this->data);
	}


	public function _retrieve_update_info() {

		global $wp_version;
		$data = new stdClass;

		// Build request
		$code = Mage::helper('nwdrevslider/framework')->get_option('revslider-code', '');
		
		$validated = Mage::helper('nwdrevslider/framework')->get_option('revslider-valid', 'false');
		$stable_version = Mage::helper('nwdrevslider/framework')->get_option('revslider-stable-version', '4.2');
		
		$rattr = array(
			'code' => urlencode($code),
			'version' => urlencode(RevSliderGlobals::SLIDER_REVISION)
		);
		
		if($validated !== 'true' && version_compare(RevSliderGlobals::SLIDER_REVISION, $stable_version, '<')){ //We'll get the last stable only now!
			$rattr['get_stable'] = 'true';
		}
		
		$request = Mage::helper('nwdrevslider/framework')->wp_remote_post($this->remote_url_info, array(
			'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
			'body' => $rattr
		));

		if(!Mage::helper('nwdrevslider/framework')->is_wp_error($request)) {
			if($response = Mage::helper('nwdrevslider/framework')->maybe_unserialize($request['body'])) {
				if(is_object($response)) {
					$data = $response;
					
					$data->basic->url = $this->plugin_url;
					$data->full->url = $this->plugin_url;
					$data->full->external = 1;
				}
			}
		}
		
		return $data;
	}
	
	
	public function _retrieve_version_info($force_check = false) {
		global $wp_version;
		
		$last_check = Mage::helper('nwdrevslider/framework')->get_option('revslider-update-check-short');
		if($last_check == false){ //first time called
			$last_check = time();
			Mage::helper('nwdrevslider/framework')->update_option('revslider-update-check-short', $last_check);
		}
		

		// Check for updates
		if(time() - $last_check > 172800 || $force_check == true){
			
			Mage::helper('nwdrevslider/framework')->update_option('revslider-update-check-short', time());
			
            $purchase = (Mage::helper('nwdrevslider/framework')->get_option('revslider-valid', 'false') == 'true') ? Mage::helper('nwdrevslider/framework')->get_option('revslider-code', '') : '';

			$response = Mage::helper('nwdrevslider/framework')->wp_remote_post($this->remote_url, array(
				'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
				'body' => array(
					'item' => urlencode('revslider_magento'),
                    'version' => urlencode(RevSliderGlobals::SLIDER_REVISION),
                    'code' => urlencode($purchase)
				)
			));

			$response_code = Mage::helper('nwdrevslider/framework')->wp_remote_retrieve_response_code( $response );
			$version_info = Mage::helper('nwdrevslider/framework')->wp_remote_retrieve_body( $response );
			
			if ( $response_code != 200 || Mage::helper('nwdrevslider/framework')->is_wp_error( $version_info ) ) {
				Mage::helper('nwdrevslider/framework')->update_option('revslider-connection', false);
				return false;
			}else{
				Mage::helper('nwdrevslider/framework')->update_option('revslider-connection', true);
			}
			
			$version_info = json_decode($version_info);
			if(isset($version_info->version)){
				Mage::helper('nwdrevslider/framework')->update_option('revslider-latest-version', $version_info->version);
			}
			
			if(isset($version_info->stable)){
				Mage::helper('nwdrevslider/framework')->update_option('revslider-stable-version', $version_info->stable);
			}
			
			if(isset($version_info->notices)){
				Mage::helper('nwdrevslider/framework')->update_option('revslider-notices', $version_info->notices);
			}
			
            if(isset($version_info->dashboard)){
                Mage::helper('nwdrevslider/framework')->update_option('revslider-dashboard', $version_info->dashboard);
            }

            if(isset($version_info->addons)){
                Mage::helper('nwdrevslider/framework')->update_option('revslider-addons', $version_info->addons);
            }

            if(isset($version_info->deactivated) && $version_info->deactivated === true){
                if(Mage::helper('nwdrevslider/framework')->get_option('revslider-valid', 'false') == 'true'){
                    //remove validation, add notice
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-valid', 'false');
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-deact-notice', true);
                }
            }

		}
		
		if($force_check == true){ //force that the update will be directly searched
			Mage::helper('nwdrevslider/framework')->update_option('revslider-update-check', '');
		}
		
	}
	

    public function add_temp_active_check($force = false){
        global $wp_version;

        $last_check = Mage::helper('nwdrevslider/framework')->get_option('revslider-activate-temp-short');
        if($last_check == false){ //first time called
            $last_check = time();
            Mage::helper('nwdrevslider/framework')->update_option('revslider-activate-temp-short', $last_check);
        }


        // Check for updates
        if(time() - $last_check > 3600 || $force == true){
            $response = Mage::helper('nwdrevslider/framework')->wp_remote_post($this->remote_temp_active, array(
                'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
                'body' => array(
                    'item' => urlencode('revslider_magento'),
                    'version' => urlencode(RevSliderGlobals::SLIDER_REVISION),
                    'code' => urlencode(Mage::helper('nwdrevslider/framework')->get_option('revslider-code', ''))
                )
            ));

            $response_code = Mage::helper('nwdrevslider/framework')->wp_remote_retrieve_response_code( $response );
            $version_info = Mage::helper('nwdrevslider/framework')->wp_remote_retrieve_body( $response );

            if ( $response_code != 200 || Mage::helper('nwdrevslider/framework')->is_wp_error( $version_info ) ) {
                //wait, cant connect
            }else{
                if($version_info == 'valid'){
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-valid', 'true');
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-temp-active', 'false');
                }elseif($version_info == 'temp_valid'){
                    //do nothing,
                }elseif($version_info == 'invalid'){
                    //invalid, deregister plugin!
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-valid', 'false');
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-temp-active', 'false');
                    Mage::helper('nwdrevslider/framework')->update_option('revslider-temp-active-notice', 'true');
                }
            }

            $last_check = time();
             Mage::helper('nwdrevslider/framework')->update_option('revslider-activate-temp-short', $last_check);
        }
    }

}


/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteUpdateClassRev extends RevSliderUpdate {}
?>