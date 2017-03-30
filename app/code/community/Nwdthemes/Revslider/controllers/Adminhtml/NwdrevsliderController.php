<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Adminhtml_NwdrevsliderController extends Mage_Adminhtml_Controller_Action {

	private $_revSliderAdmin;

	/**
	 *	Constructor
	 */

	protected function _construct() {
        spl_autoload_register( array(Mage::helper('nwdrevslider'), 'loadRevClasses'), true, true );
		global $revSliderVersion;
		global $wp_version;
		global $wpdb;
        $this->wp_magic_quotes();
		$revSliderVersion = RevSliderGlobals::SLIDER_REVISION;
		$wp_version = Mage::getVersion();
		$wpdb = Mage::helper('nwdrevslider/query');
    }

	/**
	 * Check permissions
	 */

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('nwdthemes/nwdrevslider');
    }

	/**
	 * Init action
	 */

	protected function _initAction() {
		$this->_revSliderAdmin = new RevSliderAdmin;
		Mage::helper('nwdrevslider/framework')->add_filter('revslider_slide_updateSlideFromData_pre', array(Mage::helper('nwdrevslider/images'), 'relativeImagesUrl'));
		Mage::helper('nwdrevslider/framework')->add_action('plugins_loaded', array( 'RevSliderFront', 'createDBTables' ));
        Mage::helper('nwdrevslider/framework')->add_action('plugins_loaded', array( 'RevSliderPluginUpdate', 'do_update_checks' ));
        Mage::helper('nwdrevslider/plugin')->loadPlugins();
		return $this;
	}

	/**
	 * Init page
	 *
	 * @param   string Get Page
	 */

	protected function _initPage($getPage = 'revslider') {

        Nwdthemes_Revslider_Helper_Data::setPage($getPage);
		
		if (Mage::helper('nwdrevslider/install')->validateInstall()) {
		    $this->_redirect('*/*/error');
		}		

		$this->_initAction();
		$this->_revSliderAdmin->onAddScripts();

		Mage::helper('nwdrevslider/framework')->add_filter('revslider_mod_icon_sets', array('RevSliderBase', 'set_icon_sets'));
		Mage::helper('nwdrevslider/framework')->do_action('admin_enqueue_scripts');

		$this->loadLayout()
			->_setActiveMenu('nwdthemes/nwdrevslider/nwdrevslider')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Revolution Slider'), Mage::helper('adminhtml')->__('Revolution Slider'));

        $this->_addHeadIncludes();
	}

	/**
	 * Set page title
	 *
	 * @param string $title
	 */

	protected function _setTitle($title) {
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('nwdrevslider')->__('Revolution Slider - ') . $title);
	}

    /**
     *  Include scritps and styles
     */

    protected function _addHeadIncludes() {
		$headBlock = $this->getLayout()->getBlock('head');
        $skinUrl = Mage::getDesign()->getSkinUrl();

        foreach (Mage::helper('nwdrevslider/framework')->getFromRegister('styles') as $_handle => $_style) {
            if (strpos($_style, $skinUrl) === false) {
                Mage::helper('nwdrevslider/framework')->wp_add_inline_style('inline_css_' . $_handle, '<link rel="stylesheet" type="text/css" href="' . $_style . '" media="all" />');
            } else {
                $headBlock->addItem('skin_css', str_replace($skinUrl, '', $_style));
            }
        }

        foreach (Mage::helper('nwdrevslider/framework')->getFromRegister('scripts') as $_handle => $_script) {
            if (strpos($_script, $skinUrl) === false) {
                Mage::helper('nwdrevslider/framework')->wp_add_inline_style('inline_js_' . $_handle, '<script type="text/javascript" src="' . $_script . '"></script>');
            } else {
                $headBlock->addItem('skin_js', str_replace($skinUrl, '', $_script));
            }
        }

		$headBlock
            ->setCanLoadExtJs(true)
			->assign('inlineStyles', Mage::helper('nwdrevslider/framework')->getFromRegister('inline_styles'))
			->assign('localizeScripts', Mage::helper('nwdrevslider/framework')->getFromRegister('localize_scripts'));
    }

	/**
	 * Default page
	 */

	public function indexAction() {
        $this->_createUploadDir();
		$this->slidersAction();
	}

	/**
	 * All Sliders
	 */

	public function slidersAction() {
		$this->_initPage();
		$this->_setTitle(Mage::helper('nwdrevslider')->__('All Sliders'));
		$this->renderLayout();
	}

	/**
	 * Slider Settings
	 */

	public function sliderAction() {
		$this->_initPage();
		$this->_setTitle(Mage::helper('nwdrevslider')->__('Slider Settings'));
		$this->renderLayout();
	}

	/**
	 * Slide Editor
	 */

	public function slideAction() {
		$this->_initPage();
		$this->_setTitle(Mage::helper('nwdrevslider')->__('Slide Editor'));
		$this->renderLayout();
	}

	/**
	 * Order Products Slides
	 */

	public function slidesAction() {
		$this->_initPage();
		$this->_setTitle(Mage::helper('nwdrevslider')->__('Order Products'));
		$this->renderLayout();
	}

	/**
	 * Navigation Editor
	 */

	public function navigationAction() {
		$this->_initPage();
		$this->_setTitle(Mage::helper('nwdrevslider')->__('Navigation Editor'));
		$this->renderLayout();
	}

	/**
	 * Add-Ons
	 */

	public function addonAction() {
		$this->_initPage('rev_addon');
		$this->_setTitle(Mage::helper('nwdrevslider')->__('Install & Configure Add-Ons'));
		$this->renderLayout();
	}

	/**
	 * Ajax actions
	 */

	public function ajaxAction() {
		$this->_initAction();
		$this->_revSliderAdmin->onAjaxAction();
	}

	/**
	 * Admin ajax actions
	 */

	public function adminajaxAction() {
		$this->_initAction();
        $action = 'wp_ajax_' . $this->getRequest()->getParam('action');
        echo Mage::helper('nwdrevslider/framework')->do_action($action);
	}

	/**
	 * Error page
	 */

	public function errorAction() {
		if ( ! $strError = Mage::helper('nwdrevslider/install')->validateInstall() )
		{
			$this->_redirect('*/*/index');
		}
		else
		{
		    Mage::getSingleton('adminhtml/session')->addError($strError);
		    $this->loadLayout()->_setActiveMenu('nwdthemes/nwdrevslider/nwdrevslider');
			$this->_setTitle(Mage::helper('nwdrevslider')->__('Error'));
			$this->renderLayout();
		}
	}

    /**
     *  Add magic quotes for WP compatiblity
     */

    private function wp_magic_quotes() {
        // If already slashed, strip.
        if ( get_magic_quotes_gpc() ) {
            $_GET    = RevSliderBase::stripslashes_deep( $_GET    );
            $_POST   = RevSliderBase::stripslashes_deep( $_POST   );
            $_COOKIE = RevSliderBase::stripslashes_deep( $_COOKIE );
        }

        // Escape with wpdb.
        $_GET    = $this->add_magic_quotes( $_GET    );
        $_POST   = $this->add_magic_quotes( $_POST   );
        $_COOKIE = $this->add_magic_quotes( $_COOKIE );
        $_SERVER = $this->add_magic_quotes( $_SERVER );

        // Force REQUEST to be GET + POST.
        $_REQUEST = array_merge( $_GET, $_POST );
    }

    /**
     * Walks the array while sanitizing the contents.
     *
     * @param array $array Array to walk while sanitizing contents.
     * @return array Sanitized $array.
     */

    private function add_magic_quotes( $array ) {
        foreach ( (array) $array as $k => $v ) {
            if ( is_array( $v ) ) {
                $array[$k] = $this->add_magic_quotes( $v );
            } else {
                $array[$k] = addslashes( $v );
            }
        }
        return $array;
    }

    /**
     *  Creates folders for uploading images
     */

    private function _createUploadDir() {
        try {
            $dir = Mage::helper('nwdrevslider/images')->getStorageRoot();
            if ( ! file_exists($dir)) {
                $io = new Varien_Io_File();
                $io->mkdir($dir);
            }
        } catch (Exception $e) {
        }
    }

}
