<?php
/**
 * Slider Revolution
 *
 * @package   Essential_Grid
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://revolution.themepunch.com/
 * @copyright 2015 ThemePunch
 */

/**
 * @package RevSliderExtension
 * @author  ThemePunch <info@themepunch.com>
 */
 
if( !defined( 'Nwdthemes_Revslider_Helper_Framework::ABSPATH') ) exit();

class RevSliderExtension {
	
	public function __construct() {
	
		$this->init_essential_grid_extensions();
		
	}
	
	
	/***************************
	 * Setup part for Revslider inclusion into Essential Grid
	 ***************************/
	
	/**
	 * Do all initializations for RevSlider integration
	 */
	public function init_essential_grid_extensions(){
		
		if(!class_exists('Essential_Grid')) return false; //only add if Essential Grid is installed
		
		Mage::helper('nwdrevslider/framework')->add_filter('essgrid_set_ajax_source_order', array($this, 'add_slider_to_eg_ajax'));
		Mage::helper('nwdrevslider/framework')->add_filter('essgrid_handle_ajax_content', array($this, 'set_slider_values_to_eg_ajax'), 10, 4);
		Mage::helper('nwdrevslider/framework')->add_action('essgrid_add_meta_options', array($this, 'add_eg_additional_meta_field'));
		Mage::helper('nwdrevslider/framework')->add_action('essgrid_save_meta_options', array($this, 'save_eg_additional_meta_field'), 10, 2);
		
		//only do on frontend
		
		Mage::helper('nwdrevslider/framework')->add_action('admin_head', array($this, 'add_eg_additional_inline_javascript'));
		Mage::helper('nwdrevslider/framework')->add_action('wp_head', array($this, 'add_eg_additional_inline_javascript'));
		
	}
	
	
	/**
	 * Add Slider to the List of choosable media
	 */
	public function add_slider_to_eg_ajax($media){
		
		$media['revslider'] = array('name' => __('Slider Revolution'), 'type' => 'ccw');
		
		return $media;
	}
	
	
	/**
	 * Add Slider to the List of choosable media
	 */
	public function set_slider_values_to_eg_ajax($handle, $media_sources, $post, $grid_id){
		
		if($handle !== 'revslider') return false;
		
		$slider_source = '';
		
		$values = get_post_custom($post['ID']);
		
		if(isset($values['eg_sources_revslider'])){
			if(isset($values['eg_sources_revslider'][0]))
				$slider_source = (isset($values['eg_sources_revslider'][0])) ? $values['eg_sources_revslider'][0] : '';
			else
				$slider_source = (isset($values['eg_sources_revslider'])) ? $values['eg_sources_revslider'] : '';
		}
		
		if($slider_source === ''){
			return false;
		}else{
			return ' data-ajaxtype="'.$handle.'" data-ajaxsource="'.$slider_source.'"';
		}
		
	}
	
	
	/**
	 * Adds custom meta field into the essential grid meta box for post/pages
	 */
	public function add_eg_additional_meta_field($values){
		
		$sld = new RevSlider();
		$sliders = $sld->getArrSliders();
		$shortcodes = array();
		if(!empty($sliders)){
			$first = true;
			foreach($sliders as $slider){
				$name = $slider->getParam('shortcode','false');
				if($name != 'false'){
					$shortcodes[$slider->getID()] = $name;
					$first = false;
				}
			}
		}
		
		$selected_slider = (isset($values['eg_sources_revslider'])) ? $values['eg_sources_revslider'] : '';
		if($selected_slider == '') $selected_slider[0] = '';
		?>
		<p>
			<strong style="font-size:14px"><?php echo Mage::helper('nwdrevslider')->__( 'Choose Revolution Slider'); ?></strong>
		</p>
		<p>
			<select name="eg_sources_revslider" id="eg_sources_revslider">
				<option value=""<?php Mage::helper('nwdrevslider/framework')->selected($selected_slider[0], ''); ?>><?php echo Mage::helper('nwdrevslider')->__( '--- Choose Slider ---'); ?></option>
				<?php
				if(!empty($shortcodes)){
					foreach($shortcodes as $id => $name){
						?>
						<option value="<?php echo $id; ?>"<?php Mage::helper('nwdrevslider/framework')->selected($selected_slider[0], $id); ?>><?php echo $name; ?></option>
						<?php
					}
				}
				?>
			</select>
		</p>
		<?php
		
	}
	
	/**
	 * Adds custom meta field into the essential grid meta box for post/pages
	 */
	public function save_eg_additional_meta_field($metas, $post_id){
		
		if(isset($metas['eg_sources_revslider']))
			update_post_meta($post_id, 'eg_sources_revslider', $metas['eg_sources_revslider']);
		
	}
	
	
	/**
	 * Adds needed javascript to the DOM
	 */
	public function add_eg_additional_inline_javascript(){
		?>
		<script type="text/javascript">
            var ajaxRevslider;
            
			jQuery(document).ready(function() {
				// CUSTOM AJAX CONTENT LOADING FUNCTION
                ajaxRevslider = function(obj) {
				
					// obj.type : Post Type
					// obj.id : ID of Content to Load
					// obj.aspectratio : The Aspect Ratio of the Container / Media
					// obj.selector : The Container Selector where the Content of Ajax will be injected. It is done via the Essential Grid on Return of Content
					
					var content = "";

					data = {};
					
					data.action = 'revslider_ajax_call_front';
					data.client_action = 'get_slider_html';
					data.token = '<?php echo Mage::helper('nwdrevslider/framework')->wp_create_nonce("RevSlider_Front"); ?>';
					data.type = obj.type;
					data.id = obj.id;
					data.aspectratio = obj.aspectratio;
					
					// SYNC AJAX REQUEST
					jQuery.ajax({
						type:"post",
						url:"<?php echo Mage::helper('nwdrevslider/framework')->admin_url('admin-ajax.php'); ?>",
						dataType: 'json',
						data:data,
						async:false,
						success: function(ret, textStatus, XMLHttpRequest) {
							if(ret.success == true)
								content = ret.data;								
						},
						error: function(e) {
							console.log(e);
						}
					});
					
					 // FIRST RETURN THE CONTENT WHEN IT IS LOADED !!
					 return content;						 
				};
				
				// CUSTOM AJAX FUNCTION TO REMOVE THE SLIDER
				var ajaxRemoveRevslider = function(obj) {
					return jQuery(obj.selector+" .rev_slider").revkill();
				};

				// EXTEND THE AJAX CONTENT LOADING TYPES WITH TYPE AND FUNCTION
				var extendessential = setInterval(function() {
					if (jQuery.fn.tpessential != undefined) {
						clearInterval(extendessential);
						if(typeof(jQuery.fn.tpessential.defaults) !== 'undefined') {
							jQuery.fn.tpessential.defaults.ajaxTypes.push({type:"revslider",func:ajaxRevslider,killfunc:ajaxRemoveRevslider,openAnimationSpeed:0.3});   
							// type:  Name of the Post to load via Ajax into the Essential Grid Ajax Container
							// func: the Function Name which is Called once the Item with the Post Type has been clicked
							// killfunc: function to kill in case the Ajax Window going to be removed (before Remove function !
							// openAnimationSpeed: how quick the Ajax Content window should be animated (default is 0.3)
						}
					}
				},30);
			});
		</script>
		<?php
	}
	
}
?>