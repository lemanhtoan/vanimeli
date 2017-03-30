<?php
/*------------------------------------------------------------------------
 # VenusTheme Block Builder Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_BlockBuilder_Model_Block extends Mage_Core_Model_Abstract
{
	const CACHE_BLOCK_TAG              = 'ves_blockbuilder_block';
	const CACHE_PAGE_TAG              = 'ves_blockbuilder_page';
	const CACHE_PRODUCT_TAG              = 'ves_blockbuilder_product';
	const CACHE_MEDIA_TAG 			  = 'ves_blockbuilder_media';
	const CACHE_LIVECSS_TAG 		 	= 'ves_blockbuilder_livecss';

    protected function _construct() {	
        $this->_init('ves_blockbuilder/block');
    }

    public function getBlockByAlias($alias = "", $is_page = false) {
    	$customer_group_id = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
		if($alias) {
			$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
			$todayDateTime = strtotime($todayDate);
			$todayDate = date("Y-m-d", $todayDateTime);

			$collection = $this->getCollection()
						->addFieldToFilter('alias', $alias)
						->addFieldToFilter('status', 1)
						->addFieldToFilter('show_from', array('or'=> array(
			                0 => array('date' => true, 'lt' => $todayDate),
			                1 => array('is' => new Zend_Db_Expr('null')))
			            ), 'left')
			            ->addFieldToFilter('show_to', array('or'=> array(
			                0 => array('date' => true, 'gteq' => $todayDate),
			                1 => array('is' => new Zend_Db_Expr('null')))
			            ), 'left');

			if($is_page) {
				$collection->addFieldToFilter('block_type', "page");
			}
			$block_entity =	$collection->getFirstItem();
					
			if($block_entity) {
				$customer_group = $block_entity->getCustomerGroup();
				$array_groups = explode(",",$customer_group);
				if($array_groups && in_array(0, $array_groups)){
					return $block_entity;
				} elseif( $array_groups && in_array($customer_group_id, $array_groups)) {
					return $block_entity;
				}
			}
			
		}
		return null;
    }

    public function checkBlockProfileAvailable( $block_profile = null ){
    	$checked = true;
    	if($block_profile) {
    		if($block_profile->getStatus() != "1") {
				$checked = false;	
    		} else {
    			$customer_group_id = (int)Mage::getSingleton('customer/session')->getCustomerGroupId();
    			$customer_group =  $block_profile->getCustomerGroup();
				$array_groups = explode(",",$customer_group);
				if($array_groups && !in_array(0, $array_groups) && !in_array($customer_group_id, $array_groups)){
					$checked = false;
				} else {
					$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
					$todayDateTime = strtotime($todayDate);
					$date_from = $block_profile->getShowFrom();
					if($date_from) {
						$date_from = strtotime($date_from);
					} else {
						$date_from = 0;
					}
					
					$date_to = $block_profile->getShowTo();
					if($date_to) {
						$date_to = strtotime($date_to);
					} else {
						$date_to = 0;
					}

					if($date_from > $todayDateTime || ($date_to > 0 && $date_to < $todayDateTime)) {
						$checked = false;
					}
				}
				
    		}
    	}
    	return $checked;
    }
    public function getBlocksByPosition( $position = "") {

    }

    public function getCMSPageUrl() {
    	$cmspage_id = $this->getCmspageId();
    	$href = "";
    	if($cmspage_id) {
    		$href = Mage::helper('cms/page')->getPageUrl( $cmspage_id );
    	}
    	
    	return $href;
    }

    public function getProfileByProduct($product_id = 0, $store_id = 0) {
    	if($product_id) {
    		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
			$todayDateTime = strtotime($todayDate);
			$todayDate = date("Y-m-d", $todayDateTime);
    		$collection = $this->getCollection()
    					->addFieldToFilter('block_type', "product")
						->addFieldToFilter('status', 1)
						->addFieldToFilter('show_from', array('or'=> array(
			                0 => array('date' => true, 'lt' => $todayDate),
			                1 => array('is' => new Zend_Db_Expr('null')))
			            ), 'left')
			            ->addFieldToFilter('show_to', array('or'=> array(
			                0 => array('date' => true, 'gteq' => $todayDate),
			                1 => array('is' => new Zend_Db_Expr('null')))
			            ), 'left');

		
			$collection->addProductIdFilter($product_id, $store_id, true);

			if(0 < $collection->getSize() ) {
				return $collection->getFirstItem();
			}
		}
		
		return false;
    }

    public function loadCMSPage($field_value, $field_name = "identifier", $stores = array()) {
    	$is_single_store = false;
    	$tmp_stores = $stores;
    	if (Mage::app()->isSingleStoreMode() || !$stores) {
            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            $is_single_store = true;
        } else {
            $stores = (array)$stores;
        }

        $resource = Mage::getSingleton('core/resource');
    	$readConnection = $resource->getConnection('core_read');
    	$cms_page_table = $resource->getTableName("cms/page");
        $cms_page_store_table = $resource->getTableName("cms/page_store");
        $core_store = $resource->getTableName("core/store");

    	$select = $readConnection->select()
            ->from(array('cp' => $cms_page_table))
            ->join(
                array('cps' => $cms_page_store_table),
                'cp.page_id = cps.page_id',
                array())
            ->where('cp.'.$field_name.' = ?', $field_value)
            ->where('cps.store_id IN (?)', $stores);

        $page = false;
        if($page = $readConnection->fetchRow($select)) {
        	$page_id = $page['page_id'];
        	$page = Mage::getModel("cms/page")->load($page_id);
        } elseif($is_single_store || (!$is_single_store && !$tmp_stores)) {
        	$page = Mage::getModel("cms/page")->load($field_value, $field_name);
        } else {
        	$page = Mage::getModel("cms/page")->load(0);
        }
        return $page;
        
    }

}