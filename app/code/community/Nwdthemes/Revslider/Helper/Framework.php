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

class Nwdthemes_Revslider_Helper_Framework extends Mage_Core_Helper_Abstract {

	const ABSPATH = true;
	const WPINC = true;
	public static $RS_PLUGIN_PATH;
	public static $RS_PLUGIN_URL;
	const WP_CONTENT_DIR = 'revslider';
	const RS_DEMO = false;

	/**
	 *	Constructor
	 */

	public function __construct() {
		self::$RS_PLUGIN_PATH = Mage::getSingleton('core/design_package')->getSkinBaseDir() . '/nwdthemes/revslider/';
		self::$RS_PLUGIN_URL = Mage::getDesign()->getSkinUrl('nwdthemes/revslider/');
	}

	/**
	 *	Check if unserialized
	 *
	 *	@param	var	Original
	 *	@return	var
	 */

	public function maybe_unserialize($original)
	{
		if ( $this->is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
			return @unserialize( $original );
		return $original;
	}

	/**
	 * Check value to find if it was serialized.
	 *
	 * @param string $data   Value to check to see if was serialized.
	 * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
	 * @return bool False if not serialized and true if it was.
	 */

	public function is_serialized( $data, $strict = true ) {
		// if it isn't a string, it isn't serialized.
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		if ( strlen( $data ) < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = substr( $data, -1 );
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace )
				return false;
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 )
				return false;
			if ( false !== $brace && $brace < 4 )
				return false;
		}
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== substr( $data, -2, 1 ) ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
				// or else fall through
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';
				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}
		return false;
	}

	/**
	 *	Output checkbox checked
	 *
	 *	@param	string	Value
	 *	@param	string	State (on/off)
	 */

	public function checked($value = '', $state = 'on')
	{
		if ( $value == $state )
		{
			echo 'checked="checked"';
		}
	}

	/**
	 *	Output select option selected
	 *
	 *	@param	string	Value
	 *	@param	string	State
     *	@param	boolean	Output
	 */

	public function selected($value = '', $state = '', $output = true) {
		if ( $value == $state ) {
            if ($output) {
    			echo 'selected="selected"';
            } else {
                return 'selected="selected"';
            }
		}
	}

	/**
	 *	Does nothing
	 *
	 *	@param	string	Nounce
	 *	@param	string	Actions
	 *	@return	boolean	True
	 */

	public function wp_verify_nonce($nonce = '', $actions = '')
	{
		return TRUE;
	}

	/**
	 *	Does nothing
	 *
	 *	@param	string	Content
	 *	@param	RevSliderSlide	    Slide
	 *	@return	string	Content
	 */

	public function do_shortcode($content = '', $slide = false) {
        $content = Mage::helper('cms')->getBlockTemplateProcessor()->filter($content);
        if ($slide && $slide->isFromPost()) {
            $content = $slide->set_post_data($content, $slide->getPostData(), $slide->getID());
        }
        $content = $this->forceSSL($content);
		return $content;
	}

	/**
	 *	Does nothing
	 *
	 *	@return	string	Empty
	 */

	public function wp_create_nonce()
	{
		return Mage::getSingleton('core/session')->getFormKey();
	}

	/**
	 *	Does nothing
	 *
	 *	@return	boolean	False
	 */

	public function is_multisite()
	{
		return FALSE;
	}

	/**
	 *	Returns content url
	 *
	 *	@return	string
	 */

	public function content_url()
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
	}

	/**
	 *	Gets Get Param
	 *
	 *	@param	string	Handle
	 *	@param	string	Default
	 *	@return	string	Value
	 */

	public function get_param($handle = '', $default = '')
	{
		$ci = &get_instance();
		$value = $ci->input->get($handle);
		return $value === FALSE ? $default : $value;
	}

	/**
	 *	Replicates WP style pagination
	 *
	 *	@param	array	Arguments
	 *	@return	string	Pagination html
	 */

	public function paginate_links( $args = array() ) {

		$total        = 1;
		$current      = Mage::app()->getRequest()->getParam('paged', 1);

		$defaults = array(
			'total' => $total,
			'current' => $current,
			'show_all' => false,
			'prev_next' => true,
			'prev_text' => __('&laquo; Previous'),
			'next_text' => __('Next &raquo;'),
			'end_size' => 1,
			'mid_size' => 2,
			'type' => 'plain',
			'add_args' => false, // array of query args to add
			'add_fragment' => '',
			'before_page_number' => '',
			'after_page_number' => ''
		);

		$args = array_merge( $defaults, $args );

		// Who knows what else people pass in $args
		$total = (int) $args['total'];
		if ( $total < 2 ) {
			return;
		}
		$current  = (int) $args['current'];
		$end_size = (int) $args['end_size']; // Out of bounds?  Make it the default.
		if ( $end_size < 1 ) {
			$end_size = 1;
		}
		$mid_size = (int) $args['mid_size'];
		if ( $mid_size < 0 ) {
			$mid_size = 2;
		}
		$add_args = is_array( $args['add_args'] ) ? $args['add_args'] : false;
		$r = '';
		$page_links = array();
		$dots = false;

		if ( $args['prev_next'] && $current && 1 < $current ) :
			$link = str_replace( '%_%', 2 == $current ? '' : $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current - 1, $link );
			if ( $add_args )
				$link = $this->add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/**
			 * Filter the paginated links for the given archive pages.
			 *
			 * @since 3.0.0
			 *
			 * @param string $link The paginated link URL.
			 */
			$page_links[] = '<a class="prev page-numbers" href="' . Mage::helper("adminhtml")->getUrl($link) . '">' . $args['prev_text'] . '</a>';
		endif;
		for ( $n = 1; $n <= $total; $n++ ) :
			if ( $n == $current ) :
				$page_links[] = "<span class='page-numbers current'>" . $args['before_page_number'] . $n . $args['after_page_number'] . "</span>";
				$dots = true;
			else :
				if ( $args['show_all'] || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
					$link = str_replace( '%_%', 1 == $n ? '' : $args['format'], $args['base'] );
					$link = str_replace( '%#%', $n, $link );
					if ( $add_args )
						$link = $this->add_query_arg( $add_args, $link );
					$link .= $args['add_fragment'];

					/** This filter is documented in wp-includes/general-template.php */
					$page_links[] = "<a class='page-numbers' href='" . Mage::helper("adminhtml")->getUrl($link) . "'>" . $args['before_page_number'] . $n . $args['after_page_number'] . "</a>";
					$dots = true;
				elseif ( $dots && ! $args['show_all'] ) :
					$page_links[] = '<span class="page-numbers dots">' . __( '&hellip;' ) . '</span>';
					$dots = false;
				endif;
			endif;
		endfor;
		if ( $args['prev_next'] && $current && ( $current < $total || -1 == $total ) ) :
			$link = str_replace( '%_%', $args['format'], $args['base'] );
			$link = str_replace( '%#%', $current + 1, $link );
			if ( $add_args )
				$link = $this->add_query_arg( $add_args, $link );
			$link .= $args['add_fragment'];

			/** This filter is documented in wp-includes/general-template.php */
			$page_links[] = '<a class="next page-numbers" href="' . Mage::helper("adminhtml")->getUrl($link) . '">' . $args['next_text'] . '</a>';
		endif;
		switch ( $args['type'] ) {
			case 'array' :
				return $page_links;

			case 'list' :
				$r .= "<ul class='page-numbers'>\n\t<li>";
				$r .= join("</li>\n\t<li>", $page_links);
				$r .= "</li>\n</ul>\n";
				break;

			default :
				$r = join("\n", $page_links);
				break;
		}
		return $r;
	}

	/**
	 *	Add arguments to url
	 *
	 *	@param	array	Arguments
	 *	@param	string	Url
	 *	@return	string	Link
	 */

	public function add_query_arg($args = array(), $link) {
		if ( is_array($args) )
		{
			foreach ($args as $_key => $_val) {
				$link .= $_key . '/' . $_val . '/';
			}
		}
		return $link;
	}

	/**
	 *	Check if SSL in use
	 *
	 *	@return	boolean
	 */

	public function is_ssl() {
		if ( isset($_SERVER['HTTPS']) ) {
			if ( 'on' == strtolower($_SERVER['HTTPS']) )
				return true;
			if ( '1' == $_SERVER['HTTPS'] )
				return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
			return true;
		}
		return false;
	}
    
    /**
     *	Force ssl on urls
     *
     *	@param	string
     *	@return	string
     */

	public function forceSSL($url) {
        if ($this->is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        }
		return $url;
	}    

	/**
	 *	Get upload dir
	 *
	 *	@return	array
	 */

	public function wp_upload_dir() {
		$upload_dir = array(
			'path'		=> Mage::getBaseDir(),
			'url'		=> Mage::getBaseUrl(),
			'subdir'	=> '/',
			'basedir'	=> Mage::getBaseDir('media') . DIRECTORY_SEPARATOR . self::WP_CONTENT_DIR,
			'baseurl'	=> Mage::getBaseUrl('media') . self::WP_CONTENT_DIR . '/',
			'error'		=> FALSE
		);
		return $upload_dir;
	}

	/**
	 *	Get time
	 *
	 *	@return	int
	 */

	public function current_time() {
		return time();
	}

	/**
	 *	Snitize title
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_title($title) {
		return $this->sanitize_text_field($title);
	}

	/**
	 *	Snitize title
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_title_with_dashes($title) {
        $string = $this->sanitize_title($title);
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));
	}

	/**
	 *	Snitize text field
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function sanitize_text_field($string) {
		if (is_string($string)) {
			$filtered = strip_tags($string);
			$found = false;
			while ( preg_match('/%[a-f0-9]{2}/i', $filtered, $match) ) {
				$filtered = str_replace($match[0], '', $filtered);
				$found = true;
			}
			if ( $found ) {
				$filtered = trim( preg_replace('/ +/', ' ', $filtered) );
			}
			return $filtered;
		} else {
			if (is_array($string)) {
                foreach($string as $_key => $_string) {
    				$string[$_key] = $this->sanitize_text_field($_string);
    			}
            }
			return $string;
		}
	}

	/**
	 *	Strip shortcodes
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function strip_shortcodes($string) {
		// TODO: strip Magento shortcodes
		return $string;
	}

	/**
	 *	Escape attribute
	 *
	 *	@param	string
	 *	@return	string
	 */

	public function esc_attr($text) {
		return $text;
	}

	public function esc_url($text) {
		return $text;
	}

	/**
	 *	Format sizes
	 *
	 *	@param	int	Bytes
	 *	@param	int	Decimals
	 *	@return	string
	 */

	public function size_format( $bytes, $decimals = 0 ) {
		$quant = array(
			// ========================= Origin ====
			'TB' => 1099511627776,  // pow( 1024, 4)
			'GB' => 1073741824,     // pow( 1024, 3)
			'MB' => 1048576,        // pow( 1024, 2)
			'kB' => 1024,           // pow( 1024, 1)
			'B ' => 1,              // pow( 1024, 0)
		);
		foreach ( $quant as $unit => $mag )
			if ( doubleval($bytes) >= $mag )
				return number_format( $bytes / $mag, $decimals ) . ' ' . $unit;

		return false;
	}

    /**
     *  Adds handle data to register by key
     *
     *  @param  string  Key
     *  @param  string  Handle
     *  @param  mixed   Data
     */

    public function addToRegister($key, $handle, $data = false) {
        if ($data) {
            $item = $this->getFromRegister($key, $handle);
            $item[] = $data;
            $this->setToRegister($key, $handle, $item);
        }
    }

    /**
     *  Sets handle data to register by key
     *
     *  @param  string  Key
     *  @param  string  Handle
     *  @param  mixed   Data
     */

    public function setToRegister($key, $handle, $data = false) {
        if ($data) {
            $registerKey = 'nwdrevslider_' . $key;
            $register = $this->getFromRegister($key);
            $register[$handle] = $data;
            Mage::unregister($registerKey);
            Mage::register($registerKey, $register);
        }
    }

    /**
     *  Get data from register
     *
     *  @param  string  Key
     *  @param  string  Handle
     *  @param  mixed   Default
     *  @return mixed
     */

    public function getFromRegister($key, $handle = false, $default = array()) {
        $registerKey = 'nwdrevslider_' . $key;
        $register = Mage::registry($registerKey) ?  Mage::registry($registerKey) : array();
        return $handle
            ? (isset($register[$handle]) ? $register[$handle] : array())
            : $register;
    }

    /**
     *	Add filter
     *
     *	@param	string	$handle
     *	@param	array	$filter
     *	@param	int 	$priority
     *	@param	int 	$accepted_args
     */

	public function add_filter($handle, $filter, $priority = 10, $accepted_args = 1) {
        $data = array(
            'function'      => $filter,
            'priority'      => $priority,
            'accepted_args' => $accepted_args
        );
        $this->addToRegister('filters', $handle, $data);
	}

    /**
     *	Add filter
     *
     *	@param	string	$handle
     *	@param	mixed	$value
     *	@return	var
     */

	public function apply_filters($handle, $value) {
        $args = func_get_args();
        if (func_num_args()) {
            unset($args[0]);
        }
        $filtersToApply = $this->getFromRegister('filters', $handle);
        usort($filtersToApply, function ($a, $b) {
            if ($a['priority'] == $b['priority']) {
                return 0;
            } else {
                return $a['priority'] < $b['priority'] ? -1 : 1;
            }
        });
        foreach ($filtersToApply as $filter) {
            $args[1] = $value;
            $args = array_slice(func_get_args(), 1, $filter['accepted_args']);
            $value = call_user_func_array($filter['function'], $args);
        }
		return $value;
	}

	/**
	 *	Add action
	 *
	 *	@param	string	$handle
	 *	@param	array	$action
	 */

	public function add_action($handle, $action) {
        $this->addToRegister('actions', $handle, $action);
	}

	/**
	 *	Do action
	 *
	 *	@param	string	$handle
	 *	@param	mixed	$args
	 *	@return string
	 */

	public function do_action($handle, $args = false) {
        $args = func_get_args();
        if (func_num_args()) {
            unset($args[0]);
        }
		$output = false;
        ob_start();
        foreach ($this->getFromRegister('actions', $handle) as $action) {
            call_user_func_array($action, $args);
        }
        $output = ob_get_contents();
        ob_end_clean();
        $output = str_replace(' for WordPress', '', $output);
        echo $output;
		return $output;
	}

    /**
     *	Register script
     *
     *	@param	string	$handle
     *	@param	string	$script
     */

	public function wp_register_script($handle, $script = false) {
        $this->addToRegister('register_scripts', $handle, $script);
	}

    /**
     *	Add script
     *
     *	@param	string	$handle
     *	@param	string	$script
     */

	public function wp_enqueue_script($handle, $script = false) {
        $handles = is_array($handle) ? $handle : array($handle);
        foreach ($handles as $_handle) {
            foreach ($this->getFromRegister('register_scripts', $_handle) as $regScript) {
                $this->setToRegister('scripts', $_handle, $regScript);
            }
        }
		if ($script && is_string($handle)) {
			$this->setToRegister('scripts', $handle, $script);
		}
	}

    /**
     *	Register style
     *
     *	@param	string	$handle
     *	@param	string	$style
     */

	public function wp_register_style($handle, $style = false) {
        $this->addToRegister('register_styles', $handle, $style);
	}

	/**
	 *	Add style
	 *
	 *	@param	string	$handle
	 *	@param	string	$style
	 */

	public function wp_enqueue_style($handle, $style = false) {
        $handles = is_array($handle) ? $handle : array($handle);
        foreach ($handles as $_handle) {
            foreach ($this->getFromRegister('register_styles', $_handle) as $regStyle) {
                $this->setToRegister('styles', $_handle, $regStyle);
            }
        }
		if ($style && is_string($handle)) {
			$this->setToRegister('styles', $handle, $style);
		}
	}

	/**
	 *	Add inline style
	 *
	 *	@param	string	$handle
	 *	@param	string	$style
	 */

	public function wp_add_inline_style($handle, $style = false) {
        $this->addToRegister('inline_styles', $handle, $style);
	}

    /**
     *	Get localize styles
     *
     *	@param	string	$handle
     *	@param	string	$var
     *	@param	array	$lang
     */

	public function wp_localize_script($handle, $var, $lang = array()) {
        $data = array(
            'var'   => $var,
            'lang'  => $lang
        );
        $this->setToRegister('localize_scripts', $handle, $data);
	}

	/**
	 *	Get post meta (for compatibility)
	 *
	 *	@param	int		$id
	 *	@param	string	$hanlde
	 *	@param	bool	$flag
	 *	@return	var
	 */

	public function get_post_meta($id, $handle, $flag = true) {
        $product = Mage::helper('nwdrevslider/products')->getProduct($id);
		return isset($product[$handle]) ? $product[$handle] : false;
	}

	/**
	 *	Get post types
	 *
	 *	@param	array	$args
	 *	@return	array
	 */

	public function get_post_types($args = array()) {
		return array();
	}

	/**
	 *	Get post type object
	 *
	 *	@param	string	$postType
	 *	@return	object
	 */

	public function get_post_type_object($postType) {
		switch ($postType) {
			case 'post' :
				$postArray = array(
					'labels' => array('singular_name' => 'Product')
				);
			break;
			default :
				$postArray = array();
			break;
		}
		return $this->_array_to_object($postArray);
	}

	/**
	 *	Get object taxonomies
	 *
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	object
	 */

	public function get_object_taxonomies($args, $type = 'objects') {
		$taxonomies = array();
		switch ($args['post_type']) {
			case 'post' :
				$taxonomies = array(
					'category' => array(
						'name'		=> 'category',
						'labels'	=> array('name' => 'Categories')
					)
				);
			break;
		}
		return $this->_array_to_object($taxonomies);
	}

	/**
	 *	Get categories
	 *
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	object
	 */

	public function get_categories($args, $type = 'objects') {
		$categories = Mage::helper('nwdrevslider/products')->getCategories();
		foreach ($categories as $key => $category) {
			$categories[$key] = $this->_array_to_object($category);
		}
		return $categories;
	}

	/**
	 *	Get category link
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_category_link($id) {
		$category = Mage::helper('nwdrevslider/products')->getCategory($id);
		return $category['url'];
	}


	/**
	 *	Get tag list for compatibility
	 *	@param	int		$id
	 *	@return	array
	 */

	public function get_the_tag_list($id) {
		return array();
	}

	/**
	 *	Get post image id (product image)
	 *	@param	int		$id
	 *	@return	int
	 */

	public function get_post_thumbnail_id($id) {
		return $id;
	}

	/**
	 *	Get post
	 *
	 *	@param	int		$id
	 *	@return	object
	 */

	public function get_post($id) {
		return $this->_array_to_object( Mage::helper('nwdrevslider/products')->getProduct($id) );
	}


	/**
	 *	Get post title
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_the_title($id) {
		$product = Mage::helper('nwdrevslider/products')->getProduct($id);
		return $product && isset($product['name']) ? $product['name'] : false;
	}

	/**
	 *	Get post link
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_permalink($id) {
		$product = Mage::helper('nwdrevslider/products')->getProduct($id);
		return $product && isset($product['url']) ? $product['url'] : false;
	}

	/**
	 *	Get author for compatibility
	 *
	 *	@param	int		$id
	 *	@return	string
	 */

	public function get_the_author_meta($id) {
		return '';
	}
	
	/**
	 *	Get post terms
	 *
	 *	@param	int		$id
	 *	@param	array	$args
	 *	@param	string	$type
	 *	@return	array
	 */

	public function wp_get_post_terms($id, $args) {
		$terms = array();
		foreach ($args as $arg) {
			switch ($arg) {
				case 'category' :
					// Not used now, only if someone will wany to display product categories in product slider
					/*$product = Mage::getModel('catalog/product')->load($id);
					$currentCatIds = $product->getCategoryIds();
					$categoryCollection = Mage::getResourceModel('catalog/category_collection')
						->addAttributeToSelect('name')
						->addAttributeToSelect('url')
						->addAttributeToFilter('entity_id', $currentCatIds)
						->addIsActiveFilter();
					foreach ($categoryCollection as $category) {
						$terms[] = $category->getName();
					}*/
				break;
			}
		}
		return $terms;
	}	

	/**
	 *	Check if in admin access now
	 *
	 *	@return	bool
	 */

	public function is_admin() {
		Mage::getSingleton('core/session', array('name'=>'adminhtml'));
		return Mage::getSingleton('admin/session')->isLoggedIn();
	}

	public function is_super_admin() {
		return $this->is_admin();
	}

	public function is_admin_bar_showing() {
		return $this->is_admin();
	}

	public function is_user_logged_in() {
		return $this->is_admin();
	}

	/**
	 *	Check if current user have access
	 *
	 *	@return	bool
	 */

	public function current_user_can() {
		return $this->is_admin();
	}

	/**
	 *	Get content from remote url
	 *
	 *	@param	string	$url
	 *	@param	array	$args
	 *	@return	array
	 */

	public function wp_remote_post($url, $args) {

		$defaults = array(
			'user-agent' => false,
			'headers' => array(),
			'cookies' => array(),
			'httpversion' => CURL_HTTP_VERSION_NONE,
			'timeout' => 30,
			'method' => 'POST',
			'body' => array()
		);
		$args = array_merge($defaults, $args);

		$headers = array();
		foreach ($args['headers'] as $key => $value) {
			$headers[] = "$key: $value";
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
        if ($args['method'] == 'POST' && $args['body'] && is_array($args['body'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args['body']));
		}
		if ($args['user-agent']) {
			curl_setopt($ch, CURLOPT_USERAGENT, $args['user-agent']);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_COOKIE,  implode('; ', $args['cookies']));
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $args['timeout']);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, $args['httpversion'] == '1.0' ? CURL_HTTP_VERSION_1_0 : ($args['httpversion'] == '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_NONE));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_ENCODING, '');

		$output = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error = curl_errno($ch);
		$message = curl_error($ch);

		curl_close ($ch);

		$result = array(
			'response'	=> array('code' => $code, 'message' => $message),
			'body'		=> $output
		);
		return $result;
	}

	/**
	 *	Get content from remote url
	 *
	 *	@param	string	$url
	 *	@param	array	$args
	 *	@return	array
	 */

	public function wp_remote_get($url, $args = array()) {
		$args['method'] = 'GET';
		return $this->wp_remote_post($url, $args);
	}

	/**
	 *	Open content from remote url
	 *
	 *	@param	string	$url
	 *	@return	string
	 */

	public function wp_remote_fopen($url) {
		$args = array(
			'method'             =>         'GET',
			'timeout'            =>         5,
			'redirection'        =>         5,
			'httpversion'        =>         '1.0',
			'blocking'           =>         true,
			'body'               =>         null
		);
		$response = $this->wp_remote_post($url, $args);
		return $response['response']['code'] == 200 ? $response['body'] : '';
	}

	/**
	 *  Get response code from response
	 *
	 *	@param	array	$response
	 *	@return	string
	 */

	public function wp_remote_retrieve_response_code($response) {
		return $response['response']['code'];
	}

	/**
	 *  Get body from response
	 *
	 *	@param	array	$response
	 *	@return	string
	 */

	public function wp_remote_retrieve_body($response) {
		return $response['body'];
	}

	/**
	 *	Get blog info
	 *
	 *	@param	string	$option
	 *	@return	array
	 */

	public function get_bloginfo($option) {
		$info = array(
			'url' => Mage::getBaseUrl()
		);
		return isset($info[$option]) ? $info[$option] : false;
	}

	/**
	 *	Check for error in data
	 *
	 *	@param	var		$data
	 *	@return	bool
	 */

	public function is_wp_error($data) {
		return false;
	}

	/**
	 *	Get transient
	 *
	 *	@param	string	Handle
	 *	@return var
	 */

	public function get_transient($handle) {
		$data = Mage::app()->getCache()->load('nwdrevslider-transient-' . $handle);
		if ($data !== false)
		{
			$data = unserialize($data);
		}
		return $data;
	}

	/**
	 *	Set transient
	 *
	 *	@param	string	Handle
	 *	@param	var		Value
	 *	@param	int		Expiration (seconds)
	 */

	public function set_transient($handle, $value, $expiration = 0) {
		Mage::app()->getCache()->save(serialize($value), 'nwdrevslider-transient-' . $handle, array(), $expiration);
	}

	/**
	 *	Delete transient
	 *
	 *	@param	string	Handle
	 */

	public function delete_transient($handle) {
		Mage::app()->getCache()->remove('nwdrevslider-transient-' . $handle);
	}

	public function delete_site_transient($handle) {
		$this->delete_transient($handle);
	}

	/**
	 *	Date localization
	 *
	 *	@param	string	$format
	 *	@param	int		$date
	 */

	public function date_i18n($format, $date) {
		$format = trim($format);
		$format = $format ? $format : 'd M, Y - H:i';
		return date($format, $date);
	}

	/**
	 *	Check if file is writable
	 *
	 *	@param	string	$path
	 *	@return	bool
	 */

	public function wp_is_writable($path) {
		return is_writable($path);
	}

	/**
	 * Converts byte value to integer byte value
	 *
	 * @param	string	$size
	 * @return	int
	 */

	public function wp_convert_hr_to_bytes($size) {
		$size  = strtolower( $size );
		$bytes = (int) $size;
		if ( strpos( $size, 'k' ) !== false ) $bytes = intval( $size ) * 1024;
		elseif ( strpos( $size, 'm' ) !== false ) $bytes = intval($size) * 1024 * 1024;
		elseif ( strpos( $size, 'g' ) !== false ) $bytes = intval( $size ) * 1024 * 1024 * 1024;
		return $bytes;
	}

	/**
	 *	Get current page info
	 *
	 *	@return	obj
	 */

	public function get_current_screen() {
		$screen = array('id' => 'revslider');
		return (object) $screen;
	}

	/**
	 *	Get revslider admin url
	 *
	 *	@param	string	Url
	 *	@return	string
	 */
	
	public function admin_url($url) {
        $url = str_replace('admin-ajax.php', 'adminhtml/nwdrevslider/adminajax', $url);
		$adminUrl = Mage::helper('adminhtml')->getUrl($url);
		return $adminUrl;
	}

	/**
	 *	Get option
	 *
	 *	@param	string	Handle
	 *	@param	string	Default value
	 *	@return	string	Option value
	 */

	public function get_option($handle, $default = false) {
		if ( $value = Mage::getModel('nwdrevslider/options')->getOption($handle, $default) ) {
			if ((strpos($value, 'a:') !== false
                || strpos($value, 's:') !== false
                || strpos($value, 'O:') !== false
                || strpos($value, 'i:') !== false
                || strpos($value, 'b:') !== false)
                && (($unserializedValue = @unserialize($value)) !== false || $value == 'b:0;')) {
				$value = $unserializedValue;
			}
			return $value;
		} else {
			return $default;
		}
	}

	/**
	 * Update option
	 *
	 * @param string $handle
	 * @param string value
	 */

	public function update_option($handle, $option = '') {
		$option = is_string($option) ? $option : serialize($option);
		Mage::getModel('nwdrevslider/options')->updateOption($handle, $option);
	}

    /**
     * Add option
     *
     * @param string $handle
     * @param string value
     */

	public function add_option($handle, $option = '', $deprecated = '', $autoload = 'yes') {
		$this->update_option($handle, $option);
        return true;
	}

    /**
     * Delete option
     *
     * @param string $handle
     */

	public function delete_option($handle) {
		Mage::getModel('nwdrevslider/options')->deleteOption($handle);
	}

	/**
	 * Dump output
	 *
	 * @param var $str
	 */

	public function dmp($str) {
		echo "<div align='left'>";
		echo "<pre>";
		print_r($str);
		echo "</pre>";
		echo "</div>";
	}

	/**
	 *	Convert array to object
	 *
	 *	params	array	$array
	 *	return object
	 */

	private function _array_to_object($array) {
		return json_decode(json_encode($array), FALSE);
	}

	/**
	 *	Check if mobile browser
	 *
	 *	@return	boolean
	 */

	public function wp_is_mobile() {
		$useragent = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$is_mobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
		return $is_mobile;
	}

    /**
     *  Wrapper functions for compatiblity with WP
     */

    public function _get_list_table() {
    }

	public function register_activation_hook($path, $hook) {
	}

    public function load_plugin_textdomain() {
    }
	
}