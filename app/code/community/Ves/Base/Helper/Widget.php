<?php
/**
 * Base for Venus Extensions
 *
 * @category   Ves
 * @package    Ves_Base
 * @author     venustheme <venustheme@gmail.com>
 * @copyright  Copyright (c) 2009 Venustheme.com <venustheme@gmail.com>
 */
class Ves_Base_Helper_Widget extends Mage_Core_Helper_Abstract{

	var $_widgetinfo = "/Ves/Base/etc/widgetinfo.xml";
	var $_data = array();

	public function getListWidgetTypes($type = "array", $available_widgets = array()) {
		$widgets = array();
		$controller_name = Mage::app()->getRequest()->getControllerName();
		$module_name = Mage::app()->getRequest()->getModuleName();
		$module_controller = $module_name."/".$controller_name;

		/*Get Widget Information*/
		if(Mage::registry("widgets_data")) {
			$widgets = Mage::registry("widgets_data");
		} else {
			$widgetinfo_xml = Mage::getBaseDir('code').'/community'.$this->_widgetinfo;
			$widgets = $this->getWidgetsInfoArray();
			$type_widgets = $this->_getData("type_widgets");
			/*
			if( file_exists($widgetinfo_xml)  ){

				$xmlObj = new Varien_Simplexml_Config($widgetinfo_xml);
				$type_widgets = array();
				$info = $xmlObj->getNode();
				if($info->widget) {
	                    foreach($info->widget as $widget) {
	                    	$attributes = $widget->attributes();
	                    	$type = isset($attributes['type'])?trim($attributes['type']):"";
	                    	$type_widgets[$type] = $type;
	                    	$tmp = array();
	                    	$show_in_extensions = array();
	                    	$checked = true;
	                    	if($widget->show) {
	                    		$show_in_extensions = explode(",", (string)$widget->show);
	                    	}
	                    	if($show_in_extensions) {
	                    		if(!in_array($module_name."/".$controller_name, $show_in_extensions)) {
	                    			$checked = false;
	                    		}
	                    	}
	                    	if(!$checked)
	                    		continue;

	                    	$tmp['type'] = $type;
	                    	$tmp['title'] = (string)$widget->title;
	                    	$tmp['code'] = (string)$widget->code;
	                    	$tmp['description'] = (string)$widget->description;
	                    	$tmp['icon'] = (string)$widget->icon;
	                    	$tmp['group'] = (string)$widget->group;

	                    	$widgets[] = $tmp;
	                    }
				}
			}*/
			/*Get other available widgets*/
			$tmp_available_widgets = array();

			if(is_array($available_widgets) && $available_widgets) {
				foreach($available_widgets as $widget) {

					$tmp_available_widgets[$widget['type']] = $widget['type'];

					if(is_array($type_widgets) && in_array($widget['type'], $type_widgets)) 
						continue;
                    
                    $show_in_extensions = array();
                    $checked = true;

                    if(isset($widget['show']) && $widget['show']) {
                        $show_in_extensions = explode(",", (string)$widget['show']);
                    }
                    if($show_in_extensions) {
                        if(!in_array($module_name."/".$controller_name, $show_in_extensions)) {
                            $checked = false;
                        }
                    }
                    if(!$checked)
                        continue;

					$tmp = array();
					$tmp['type'] = $widget['type'];
	                $tmp['title'] = (string)$widget['name'];
                    $tmp['title'] = str_replace(array("'",'"','\"'), "", $tmp['title']);
                    $tmp['code'] = (string)$widget['code'];
                    $tmp['description'] = (string)$widget['description'];
                    $tmp['description'] = str_replace(array("'",'"','\"'), "", $tmp['description']);
	                $tmp['icon'] = (string)$widget['code'];
	                $tmp['group'] = "others";
	                $widgets[] = $tmp;
				}
			}
			/*Remove not available widget*/
			if($widgets && $tmp_available_widgets) {
				$tmp_widgets = array();
				foreach($widgets as $widget) {
					if(in_array($widget['type'], $tmp_available_widgets)) {
                        $widget['title'] = str_replace(array("'",'"','\"'), "", $widget['title']);
                        $widget['description'] = str_replace(array("'",'"','\"'), "", $widget['description']);
						$tmp_widgets[] = $widget;
					}
				}
				$widgets = $tmp_widgets;
			}
		}
		if($type == "json") {
			return Zend_Json::encode($widgets);
		}

		return $widgets;
	}

	public function getWidgetInfo($widget_type = "") {

	}

	/**
     * Load Widgets XML config from widget.xml files and cache it
     *
     * @return Varien_Simplexml_Config
     */
    public function getWidgetInfoConfig()
    {

        $cachedXml = Mage::app()->loadCache('widgetinfo_config');
        if ($cachedXml) {
            $xmlConfig = new Varien_Simplexml_Config($cachedXml);
        } else {
            $config = new Varien_Simplexml_Config();
            $config->loadString('<?xml version="1.0"?><widgets></widgets>');
            Mage::getConfig()->loadModulesConfiguration('widgetinfo.xml', $config);
            $xmlConfig = $config;
            if (Mage::app()->useCache('config')) {
                Mage::app()->saveCache($config->getXmlString(), 'widgetinfo_config',
                    array(Mage_Core_Model_Config::CACHE_TAG));
            }
        }

        return $xmlConfig;
    }

    /**
     * Return filtered list of widgets as SimpleXml object
     *
     * @param array $filters Key-value array of filters for widget node properties
     * @return Varien_Simplexml_Element
     */
    public function getWidgetsInfoXml($filters = array())
    {
        $widgets = $this->getWidgetInfoConfig()->getNode();
        $result = clone $widgets;

        // filter widgets by params
        if (is_array($filters) && count($filters) > 0) {
            foreach ($widgets as $code => $widget) {
                try {
                    $reflection = new ReflectionObject($widget);
                    foreach ($filters as $field => $value) {
                        if (!$reflection->hasProperty($field) || (string)$widget->{$field} != $value) {
                            throw new Exception();
                        }
                    }
                } catch (Exception $e) {
                    unset($result->{$code});
                    continue;
                }
            }
        }
       
        return $result;
    }

    /**
     * Return list of widgets as array
     *
     * @param array $filters Key-value array of filters for widget node properties
     * @return array
     */
    public function getWidgetsInfoArray($filters = array())
    {
        if (!$this->_getData('widgetsinfo_array')) {
            $result = array();
            $types = array();
            foreach ($this->getWidgetsInfoXml($filters) as $widget) {
                $widget_type  = $widget->getAttribute('type') ? $widget->getAttribute('type') : '';
                $helper = Mage::helper("widget");
                $types[] = $widget_type;
                $tmp = array(
                	'type'          => $widget_type,
                    'title'         => $helper->__((string)$widget->title),
                    'code'          => (string)$widget->code,
                    'description'   => $helper->__((string)$widget->description),
                    'icon'   		=> (string)$widget->icon,
                    'group'   		=> (string)$widget->group
                );
                $tmp['description'] = str_replace("'", "", $tmp['description']);
                $result[] = $tmp;
            }
            usort($result, array($this, "_sortWidgets"));

            $this->setData('widgetsinfo_array', $result);
            $this->setData('type_widgets', $types);
        }
        return $this->_getData('widgetsinfo_array');
    }

    public function setData($key, $value =null ) {
    	if($value !== null) {
    		$this->_data[$key] = $value;
    	}
    }
    /**
     * User-defined widgets sorting by Name
     *
     * @param array $a
     * @param array $b
     * @return boolean
     */
    protected function _sortWidgets($a, $b)
    {
        return strcmp($a["title"], $b["title"]);
    }
    protected function _getData($key) {
    	return isset($this->_data[$key])?$this->_data[$key]:false;
    }
}