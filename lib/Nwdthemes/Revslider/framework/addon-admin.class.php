<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       h
 * @since      1.0.0
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rev_addon
 * @subpackage Rev_addon/admin
 * @author     ThemePunch <info@themepunch.com>
 */
class Rev_addon_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset(Nwdthemes_Revslider_Helper_Data::$_GET["page"]) && Nwdthemes_Revslider_Helper_Data::$_GET["page"]=="rev_addon"){
			Mage::helper('nwdrevslider/framework')->wp_enqueue_style('rs-plugin-settings', Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL .'admin/assets/css/admin.css', array(), RevSliderGlobals::SLIDER_REVISION);
			Mage::helper('nwdrevslider/framework')->wp_enqueue_style( $this->plugin_name, Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL . 'admin/assets/css/rev_addon-admin.css', array( ), $this->version);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rev_addon_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rev_addon_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if(isset(Nwdthemes_Revslider_Helper_Data::$_GET["page"]) && Nwdthemes_Revslider_Helper_Data::$_GET["page"]=="rev_addon"){
			Mage::helper('nwdrevslider/framework')->wp_enqueue_script('tp-tools', Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL .'public/assets/js/jquery.themepunch.tools.min.js', array(), RevSliderGlobals::SLIDER_REVISION );
			Mage::helper('nwdrevslider/framework')->wp_enqueue_script('unite_admin', Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL .'admin/assets/js/admin.js', array(), RevSliderGlobals::SLIDER_REVISION );
			Mage::helper('nwdrevslider/framework')->wp_enqueue_script( $this->plugin_name, Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_URL .'admin/assets/js/rev_addon-admin.js', array( 'jquery' ), $this->version, false );
			Mage::helper('nwdrevslider/framework')->wp_localize_script( $this->plugin_name, 'rev_slider_addon', array(
					'ajax_url' => Mage::helper('nwdrevslider/framework')->admin_url( 'admin-ajax.php' ),
					'please_wait_a_moment' => __("Please Wait a Moment",'revslider'),
					'settings_saved' => __("Settings saved",'revslider')
				));
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_submenu_page(
			'revslider',
			__( 'Add-Ons', 'revslider' ),
			__( 'Add-Ons', 'revslider' ),
			'manage_options',
			$this->plugin_name,
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( Nwdthemes_Revslider_Helper_Framework::$RS_PLUGIN_PATH.'admin/views/rev_addon-admin-display.php' );
	}

	/**
	 * Activates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function activate_plugin() {
		// Verify that the incoming request is coming with the security nonce
		if( Mage::helper('nwdrevslider/framework')->wp_verify_nonce( Nwdthemes_Revslider_Helper_Data::$_REQUEST['nonce'], 'ajax_rev_slider_addon_nonce' ) ) {
			if(isset(Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin'])){
				//Mage::helper('nwdrevslider/framework')->update_option( "rev_slider_addon_gal_default", Mage::helper('nwdrevslider/framework')->sanitize_text_field(Nwdthemes_Revslider_Helper_Data::$_REQUEST['default_gallery']) );
				$result = Mage::helper('nwdrevslider/plugin')->activate_plugin( Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin'] );
				if ( Mage::helper('nwdrevslider/framework')->is_wp_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Deactivates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function deactivate_plugin() {
		// Verify that the incoming request is coming with the security nonce
		if( Mage::helper('nwdrevslider/framework')->wp_verify_nonce( Nwdthemes_Revslider_Helper_Data::$_REQUEST['nonce'], 'ajax_rev_slider_addon_nonce' ) ) {
			if(isset(Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin'])){
				//Mage::helper('nwdrevslider/framework')->update_option( "rev_slider_addon_gal_default", Mage::helper('nwdrevslider/framework')->sanitize_text_field(Nwdthemes_Revslider_Helper_Data::$_REQUEST['default_gallery']) );
				$result = Mage::helper('nwdrevslider/plugin')->deactivate_plugins( Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin'] );
				if ( Mage::helper('nwdrevslider/framework')->is_wp_error( $result ) ) {
					// Process Error
					die('0');
				}
				die( '1' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

	/**
	 * Deactivates Installed Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	public function install_plugin() {
		if( Mage::helper('nwdrevslider/framework')->wp_verify_nonce( Nwdthemes_Revslider_Helper_Data::$_REQUEST['nonce'], 'ajax_rev_slider_addon_nonce' ) ) {
			if(isset(Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin'])){
				global $wp_version;
                $plugin_slug = basename(Nwdthemes_Revslider_Helper_Data::$_REQUEST['plugin']);
				$plugin_result = false;
				$plugin_message = 'UNKNOWN';
                $url = 'http://updates.themepunch.tools/magento/addons/'.$plugin_slug.'/'.$plugin_slug.'.zip';

				$get = Mage::helper('nwdrevslider/framework')->wp_remote_post($url, array(
					'user-agent' => 'Magento/'.$wp_version.'; '.Mage::helper('nwdrevslider/framework')->get_bloginfo('url'),
					'body' => '',
					'timeout' => 45
				));

                if( !$get || $get["response"]["code"] != "200" ){
				  $plugin_message = 'FAILED TO DOWNLOAD';
				}else{
					$plugin_message = 'ZIP is there';
					$upload_dir = Mage::helper('nwdrevslider/framework')->wp_upload_dir();
                    $file = $upload_dir['basedir']. '/revslider/templates/' . $plugin_slug . '.zip';
					@mkdir(dirname($file));
					$ret = @file_put_contents( $file, $get['body'] );

					Mage::helper('nwdrevslider/filesystem')->WP_Filesystem();

					global $wp_filesystem;

					$d_path = Nwdthemes_Revslider_Helper_Plugin::getPluginDir();
					$unzipfile = Mage::helper('nwdrevslider/filesystem')->unzip_file( $file, $d_path);

					if( Mage::helper('nwdrevslider/framework')->is_wp_error($unzipfile) ){
						define('FS_METHOD', 'direct'); //lets try direct. 

						Mage::helper('nwdrevslider/filesystem')->WP_Filesystem();  //Mage::helper('nwdrevslider/filesystem')->WP_Filesystem() needs to be called again since now we use direct !

						//@chmod($file, 0775);
						$unzipfile = Mage::helper('nwdrevslider/filesystem')->unzip_file( $file, $d_path);
						if( Mage::helper('nwdrevslider/framework')->is_wp_error($unzipfile) ){
							$d_path = Nwdthemes_Revslider_Helper_Plugin::WP_PLUGIN_DIR;
							$unzipfile = Mage::helper('nwdrevslider/filesystem')->unzip_file( $file, $d_path);

							if( Mage::helper('nwdrevslider/framework')->is_wp_error($unzipfile) ){
								$f = basename($file);
								$d_path = str_replace($f, '', $file);

								$unzipfile = Mage::helper('nwdrevslider/filesystem')->unzip_file( $file, $d_path);
							}
						}
					}
					@unlink($file);
					die('1');
				}
                //$result = Mage::helper('nwdrevslider/plugin')->activate_plugin( $plugin_slug.'/'.$plugin_slug.'.php' );
			}
			else{
				die( '0' );
			}
		} 
		else {
			die( '-1' );
		}
	}

} // END of class