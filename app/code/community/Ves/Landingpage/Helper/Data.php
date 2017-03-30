<?php

class Ves_Landingpage_Helper_Data extends Mage_Core_Helper_Abstract {

    
    public function checkAvaiable($controller_name = "") {
        $arr_controller = array("Mage_Cms",
            "Mage_Catalog",
            "Mage_Tag",
            "Mage_Checkout",
            "Mage_Customer",
            "Mage_Wishlist",
            "Mage_CatalogSearch");
        if (!empty($controller_name)) {
            if (in_array($controller_name, $arr_controller)) {
                return true;
            }
        }
        return false;
    }

    public function checkMenuItem( $menu_name = "", $config = array())
	{
		if(!empty( $menu_name) && !empty( $config)){
			$menus = isset($config["menuAssignment"])?$config["menuAssignment"]:"all";
			$menus = explode(",", $menus);
			if( in_array("all", $menus) || in_array( $menu_name, $menus) ){
				return true;
			}
		}
		return false;
	}

    public function getEffectList() {
        $arrayParams = array(
            'slideUp' => Mage::helper('adminhtml')->__("slideUp"),
            'slideDown' => Mage::helper('adminhtml')->__("slideDown"),
            'slideLeft' => Mage::helper('adminhtml')->__("slideLeft"),
            'slideRight' => Mage::helper('adminhtml')->__("slideRight"),
            'slideExpandUp' => Mage::helper('adminhtml')->__("slideExpandUp"),
            'expandUp' => Mage::helper('adminhtml')->__("expandUp"),
            'fadeIn' => Mage::helper('adminhtml')->__("fadeIn"),
            'expandOpen' => Mage::helper('adminhtml')->__("expandOpen"),
            'bigEntrance' => Mage::helper('adminhtml')->__("bigEntrance"),
            'hatch' => Mage::helper('adminhtml')->__("hatch"),
            'bounce' => Mage::helper('adminhtml')->__("bounce"),
            'pulse' => Mage::helper('adminhtml')->__("pulse"),
            'floating' => Mage::helper('adminhtml')->__("floating"),
            'tossing' => Mage::helper('adminhtml')->__("tossing"),
            'pullUp' => Mage::helper('adminhtml')->__("pullUp"),
            'pullDown' => Mage::helper('adminhtml')->__("pullDown"),
            'stretchLeft' => Mage::helper('adminhtml')->__("stretchLeft"),
            'stretchRight' => Mage::helper('adminhtml')->__("stretchRight"),
        );
        return $arrayParams;
    }


    public function getImageUrl($url = null) {
        return Mage::getSingleton('ves_landingpage/config')->getBaseMediaUrl() . $url;
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @param  boolean $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string
     */
    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        /* @var $inline Mage_Core_Model_Translate_Inline */
        $inline = Mage::getSingleton('core/translate_inline');
        if ($inline->isAllowed()) {
            $inline->setIsJson(true);
            $inline->processResponseBody($json);
            $inline->setIsJson(false);
        }

        return $json;
    }
    
     /*
     * Recursively searches and replaces all occurrences of search in subject values replaced with the given replace value
     * @param string $search The value being searched for
     * @param string $replace The replacement value
     * @param array $subject Subject for being searched and replaced on
     * @return array Array with processed values
     */
    public function recursiveReplace($search, $replace, $subject)
    {
        if(!is_array($subject))
            return $subject;

        foreach($subject as $key => $value)
            if(is_string($value))
                $subject[$key] = str_replace($search, $replace, $value);
            elseif(is_array($value))
                $subject[$key] = self::recursiveReplace($search, $replace, $value);

        return $subject;
    }
}

?>