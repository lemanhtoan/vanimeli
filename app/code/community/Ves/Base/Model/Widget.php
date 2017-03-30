<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */
class Ves_Base_Model_Widget extends Mage_Core_Model_Abstract{

	var $engines = array();
	/**
	 *
	 */	
	public function loadWidgetsEngines(){
	 	
		if( empty($this->engines) ){
			$engines = glob( Mage::getBaseDir('lib') . '/VesBase/widget/*.php' );
			foreach( $engines as $w ){
				$t = str_replace( ".php", "", basename($w) );
				$this->engines[$t] = $t;
			}
		}
		return $this;
	}

	public function getButtons() {
			
			$output = array();
		 	
			foreach( $this->engines as $w ){

				$class = "PtsWidget".ucfirst($w);
				
				if( class_exists($class) ){
				 	$obj = new $class( $this->registry );
				 	if( isset($obj->usemeneu) ){
				 		continue;
				 	}
					$cb_args = array();
					$info = 	call_user_func_array(array( $class, 'getWidgetInfo'), $cb_args);
					$group = isset($info['group'])?$info['group'] :  ( 'others' ); 
					$button = '
						<div id="wpo_'.$w.'" data-widget="'.$w.'"  >
							<div class="wpo-wicon wpo-icon-'.$w.'"></div>
							<div class="widget-title"> '.Mage::helper("ves_base")->__( $info['label'] ).' </div>
							 <i class="widget-desc">'.Mage::helper("ves_base")->__( $info['explain'] ).'</i>
						</div>
					';

					$output['widgets'][$w] = array('type' => $w, 'button' =>  $button, 'group' => $group );
					$output['groups'][$group]['group'] = Mage::helper("ves_base")->__(ucfirst($group));
					$output['groups'][$group]['key'] = $group;
				} 
			}
	 
			return $output;
			
	}

	/**
	 * general function to render FORM 
	 *
	 * @param String $type is form type.
	 * @param Array default data values for inputs.
	 *
	 * @return Text.
	 */
	public function renderForm( $type, $args, $data=array() ){
		
		$class = "PtsWidget".ucfirst($type);

		if( class_exists($class) ){
			$widget = new $class( $this->registry );
			 	$widget->token = $this->token;
			return $widget->renderForm( $args, $data );
		}

		return Mage::helper('ves_base')->__('Sorry, Form Setting is not avairiable for this type' );
	}
}