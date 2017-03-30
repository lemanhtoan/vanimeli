<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Megamenu_Block_Adminhtml_Megamenu_Liveedit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    var $params = null;
    public function __construct()
    {
	    $this->_blockGroup  = 'ves_megamenu';
        $this->_objectId    = 'ves_megamenu_id';
        $this->_controller  = 'adminhtml_megamenu';
        $this->_mode        = 'liveedit';

        $this->_updateButton('save', 'label', Mage::helper('ves_megamenu')->__('Save Theme'));
        $this->_updateButton('delete', 'label', Mage::helper('ves_megamenu')->__('Delete Theme'));

        $this->setTemplate('ves_megamenu/megamenu/liveedit.phtml');
        
        $mediaHelper = Mage::helper('ves_megamenu/media');
        $mediaHelper->loadMedia();
        $mediaHelper->loadMediaLiveEdit();

        $this->params = array();
    }

    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getParam($name, $default = ""){
        return isset($this->params[$name])?$this->params['name']: $default;
    }

    public function getParams(){
        return $this->params;
    }
    public function getWidgets(){
        $widgets = Mage::getModel('ves_megamenu/widget')->getCollection();
        /*
        $store_id = $this->getRequest()->getParam('store_id');

        if($store_id){
            $widgets->addFieldToFilter('store_id', $store_id);
        }*/
        return $widgets;
        
    }

    public function getHeaderText()
    {
        return Mage::helper('ves_megamenu')->__("Venus Megamenu");
    }

    public function getLiveSiteUrl(){
        $live_site_url = Mage::getBaseUrl();
        $live_site_url = str_replace("index.php/", "", $live_site_url);
        return $live_site_url;

    }

    public function getBackLink(){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/index', array("store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/index'); 
        }

    }
    public function getLiveEditLink(){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/livesave', array("store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/livesave'); 
        }

    }
    public function getCreateWidgetLink($widget_id = 0, $widget_type = ""){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/addwidget', array("id"=>$widget_id,"wtype"=>$widget_type, "store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/addwidget', array("id"=>$widget_id,"wtype"=>$widget_type)); 
        }
    }
    public function getRenderWidgetLink(){
       return $this->getUrl('*/adminhtml_megamenu/renderwidget');
    }
    public function getAjaxGenmenuLink(){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/ajxgenmenu', array("store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/ajxgenmenu'); 
        }
        
    }
    public function getAjaxMenuinfoLink(){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/ajxmenuinfo', array("store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/ajxmenuinfo'); 
        }
    }
    public function getAjaxSaveLink(){
        $store_id = Mage::helper("ves_megamenu")->getStoreId();
        if($store_id) {
            return $this->getUrl('*/adminhtml_megamenu/ajaxsave', array("store_id"=>$store_id));
        } else {
            return $this->getUrl('*/adminhtml_megamenu/ajaxsave'); 
        }
    }
    public function getStoreSwitcherHtml() {
       return $this->getChildHtml('store_switcher');
    }
    protected function getCustomLink($route , $params = array()){
        $link =  Mage::helper("adminhtml")->getUrl($route, $params);
        $link = str_replace("/adminhtml/","/", $link);
        $link = str_replace("/adminhtml_megamenu/","/", $link);
        $link = str_replace("//admin","/admin", $link);
        return $link;
    }
    public function getDirectivesLink($params = array()){
       return $this->getCustomLink("*/adminhtml/admin/cms_wysiwyg/directive", $params);
    }
    public function getVariablesLink($params = array()){
       return $this->getCustomLink("*/adminhtml/admin/system_variable/wysiwygPlugin", $params);
    }
    public function getImagesLink($params = array()){
       return $this->getCustomLink("*/adminhtml/admin/cms_wysiwyg_images/index", $params);
    }
    public function getWidgetLink($params = array()){
        return $this->getCustomLink("*/adminhtml/admin/widget/index", $params);
    }

    public function getGenSubmenuLink($params = array()){
        return $this->getCustomLink("*/adminhtml_megamenu/gensubmenu", $params);
    }
   
}