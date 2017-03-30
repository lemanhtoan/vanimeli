<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_RuleController extends Mage_Core_Controller_Front_Action
{

	public function preDispatch()
	{
		parent::preDispatch();
		if( !Mage::getStoreConfigFlag('productlist/general_setting/show') ) {
			$this->norouteAction();
		}
	}

    /**
     * Initialize rule
     * @return Ves_ProductList_Model_RUle
     */
    protected function _initRule(){
    	$ruleId = (int) $this->getRequest()->getParam('id', false);
    	if (!$ruleId) {
    		return false;
    	}
    	$rule = Mage::getModel('productlist/rule')
    	->setStoreId(Mage::app()->getStore()->getId())
    	->load($ruleId);
    	Mage::register('current_rule', $rule);
    	return $this;
    }

    protected function _setBreadcrumbs(){
    	$rule = Mage::registry('current_rule');
    	$breadcrumbBlock = $this->getLayout()->getBlock('breadcrumbs');
    	if ($breadcrumbBlock){
    		$breadcrumbs = Mage::app()->getLayout()->getBlock('breadcrumbs');
    		$breadcrumbs->addCrumb('home', array(
    			'label' => $this->__('Home'),
    			'title' => $this->__('Go to Home Page'),
    			'link'  => Mage::getBaseUrl()
    			));
    		$breadcrumbs->addCrumb('rule', array(
    			'label' => $this->__($rule->getTitle()),
    			'title' => $this->__($rule->getTitle())
    			));
    	}
    }

    /**
     * Rule view action
     */
    public function viewAction()
    {

        if ($this->_initRule()) {
            $rule = Mage::registry('current_rule');

            $active_from = $rule->getCustomDesignFrom();
            $ative_to = $rule->getCustomDesignTo();
            $today =  time();
            $is_customlayout = false;
            if( $rule->getData('page_layout')!='' ){
                if($active_from !=''  && $ative_to !=''){
                    if( $today >= strtotime( $active_from) && strtotime($active_from) <= strtotime( $ative_to ) && $today <= strtotime( $ative_to)  ){
                        $is_customlayout = true;
                    }
                }elseif($active_from ==''  && $ative_to !=''){
                    if( $today <= strtotime( $ative_to ) ){
                        $is_customlayout = true;
                    }
                }elseif($active_from !=''  && $ative_to ==''){
                    if( $today >= strtotime( $active_from)){
                        $is_customlayout = true;
                    }
                }else{
                    $is_customlayout = true;
                }
            }
            $update = $this->getLayout()->getUpdate();
            $update->addHandle('default');
            $update->addHandle('productlist_rule_view');
            $this->loadLayoutUpdates();

            if($is_customlayout){
                $update->addUpdate($rule->getData('custom_layout_update'));
            }
            $this->generateLayoutXml()->generateLayoutBlocks();
            if($is_customlayout){
                $this->getLayout()->helper('page/layout')->applyTemplate($rule->getData('page_layout'));
            }
            // Color Swatches
            $blockName = 'catalog.leftnav';
            Mage::helper('configurableswatches/productlist')->convertLayerBlock($blockName);


            if($this->getRequest()->isXmlHttpRequest()){ //Check if it was an AJAX request
                $response = array();
                
                if ($rule->getId()) {
                    
                $viewpanel = $this->getLayout()->getBlock('catalog.leftnav')->toHtml(); // Generate New Layered Navigation Menu
                $productlist = $this->getLayout()->getBlock('products')->toHtml(); // Generate product list
                $response['status'] = 'SUCCESS';
                $response['viewpanel'] = $viewpanel;
                $response['productlist'] = $productlist;
                
                // apply custom layout (page) template once the blocks are generated
                }elseif (!$this->getResponse()->isRedirect()) {
                    $this->_forward('noRoute');
                    $response['status'] = 'FAILURE';            
                }
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
                return;
            }
            /*End ajax request*/
            $this->_setBreadcrumbs();
            $this->renderLayout();
        }
        elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }
}
