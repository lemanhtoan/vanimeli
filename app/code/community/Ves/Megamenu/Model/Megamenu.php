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
class Ves_Megamenu_Model_Megamenu extends Mage_Core_Model_Abstract
{
	const CACHE_BLOCK_TAG = 'ves_megamenu_top';
	const CACHE_WIDGET_TAG = 'ves_megamenu_widget_menu';
	const DEFAULT_STORE_ID = 0;
	const TREE_ROOT_ID = 1;
	
    public function _construct()
    {
        parent::_construct();
        $this->_init('ves_megamenu/megamenu');
    }
    
    public function isActive() {
        if($this->getPublished() == Ves_Megamenu_Model_Status::STATUS_ENABLED) {
            return true;
        }
        return false;
    }
    
    public function hasChild($statusFilter = false, $storeId = null) {
        $collection = Mage::getModel('ves_megamenu/megamenu')->getCollection()
			->addFieldToFilter('parent_id', $this->getId());

		if($storeId !== null) {
			$collection->addStoreFilter($storeId);
		}
		if($statusFilter) {
			$collection->addStatusFilter();
		}
        if(count($collection))
            return true;
        return false;
    }
	
	public function checkExistMenuRoot($parent_id = 1, $store_id = null) {
		$collection = Mage::getModel('ves_megamenu/megamenu')->getCollection()
			->addFieldToFilter('parent_id', $parent_id);

		if($store_id !== null) {
			$collection->addStoreFilter((int)$store_id);
		}
		$total = $collection->getSize();
        if((int)$total > 0)
            return true;
        return false;
	}
    public function getMegaMenus($id_megamenu = null, $active = false, $position_type = false, $parent_id = null){
		$where = ( $id_megamenu ? 'AND megamenu_id <> '.$id_megamenu : '').( $active ? ' AND published = 1' : '');
		
	
		if($position_type){
			//$where .= ' AND `position_type` = \''.$position_type.'\'';
		}
		$session = Mage::getSingleton('customer/session');
		$privacy = array(0,1);
		if($session->isLoggedIn()) {
			$privacy[] = 99;
			$privacy[]=$session->getCustomerGroupId();

						
		}
		
		$where .= ' AND privacy in('.implode(',',$privacy).') ';
	//	echo '<pre>'.print_r( $where, 1 ); die;
		if($parent_id !== null && $parent_id !== ""){
			$where .= " AND parent_id = ".$parent_id;
		}
		$tableName = Mage::getSingleton('core/resource')->getTableName('ves_megamenu_megamenu');
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$select = $connection->select()
							->from( $tableName , array('*'))		
							->where('1' . $where)               
							->group('megamenu_id')
                            ->order('position');
		$rowsArray = $connection->fetchAll($select);
		return $rowsArray;
	}
	
	/**
	* get sub mega menu level 1
	*/
	public function getsubmegamenu($parent_id){
		$where = 'parent_id = '.$parent_id;
		$results = array();
		if($parent_id){
			$tableName = Mage::getSingleton('core/resource')->getTableName('ves_megamenu_megamenu');
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $connection->select()
							->from($tableName, array('*'))
							->where($where)
                            ->order('position');
			$results = $connection->fetchAll($select);
		}
		return $results;
	}
    public function getChildItem($col=null, $storeId = null) {
        $collection = Mage::getModel('ves_megamenu/megamenu')->getCollection()
			->addFieldToFilter('parent_id', $this->getId())
			->setOrder('position', 'ASC');
		if($storeId !== null) {
			$collection->addStoreFilter($storeId);
		}
        if($col != null) {
            $collection->addFieldToFilter('col', $col);
        }
        return $collection;
    }
    
    public function isGroup() {
        if($this->getIsGroup() == 1) {
            return true;
        }
        return false;
    }
    
    public function showTitle() {
        if($this->getShowTitle() == 1) {
            return true;
        }
        return false;
    }
    
    public function isContent() {
        if($this->getIsContent() == 1) {
            return true;
        }
        return false;
    }
    
    public function isRoot() {
	if($this->getParentId() == 1)
	    return true;
	return false;
    }
    
    public function showSub() {
	if($this->getShowSub() == 1) {
		return true;
	}
	return false;
    }
    
    public function getRootId($storeId = null, $parentId=1) {
    	if($storeId == null)
    		$storeId = 0;
		$position_type = Mage::helper('ves_megamenu/data')->getPositionType();
    	$collection = $this->getCollection()
    				->addStoreFilter($storeId)
					/*->addFieldToFilter("position_type", $position_type)*/
    				->addFieldToFilter('parent_id', $parentId)
    				->addFieldToFilter('level_depth', 0);
    	$data = array();
    	foreach ($collection as $megamenu) {
    		$data[] = $megamenu->getId();
    	}
    	return $data;
    }
    
    public function renderTree($menu=null, $level=0, $activeId, $storeId = null)
    {
    	$html = '';

    	if(!$menu) {
			foreach($this->getRootId($storeId) as $rootId) {
				$menu = $this->load($rootId);
				$html .= $this->renderTree($menu, 0, $activeId, $storeId);
			}
			return $html;
    	}
    	
    	$html .= '<li id="'.$menu->getId().'" class="';
    	if($menu->isRoot() || $level==0) 
    	    $html .= 'root folder-open';
    	$html .= '"><span ';
		if($activeId == $menu->getId())
			$html .= 'class="active"';
		$html .= '>'.$menu->getTitle().'<span style="font-size:10px">( ID: '.$menu->getId().')</span></span>';
    	
    	if($menu->hasChild(false, $storeId)) {
			$html .= '<ul>';
			foreach ($menu->getChildItem(null, $storeId) as $child) {
				$html .= $this->renderTree($child, $level+1, $activeId, $storeId);
			}
			$html .= '</ul>';	
		}
			$html .= '</li>';
			
    	return $html;
    }
    
	public function renderDropdownMenu( $menu, $level=0,$activeID=0,$storeId=0 ){
		
		$html = '';
		if(!$menu) {
			$html = '<option value="0"> ROOT </option>';
			foreach($this->getRootId($storeId) as $rootId) {
				$menu = $this->load($rootId);
				$html .= $this->renderDropdownMenu($menu, 0, $activeID );
			}
			return $html;
    	}
		$selected = $menu->getId()==$activeID?'selected="selected"':"";
		$html = '<option '.$selected.' value="'.$menu->getId().'">'.str_repeat( "--",$level).$menu->getTitle().'(ID:'.$menu->getId().')'.'</option>';
		
		if($menu->hasChild(false, $storeId)) {
			foreach ($menu->getChildItem(null, $storeId) as $child) {
				$html .= $this->renderDropdownMenu( $child, $level+1, $activeID );
			}
		}
		return $html;
	}

	public function updateStores( $stores = array(), $menu_id = 0)
    {
    	return $this->_getResource()->updateStores( $stores, $menu_id );
    }

    public function loadByCategoryId($categoryId, $store_id = 0, $import_mode = false)
    {	
    	if($store_id || $import_mode) {
    		return $this->getCollection()
    				->addFieldToFilter('item', $categoryId)
    				->addStoreFilter($store_id)
    				->getFirstItem();
    	} else {
    		return $this->getCollection()->addFieldToFilter('item', $categoryId)->getFirstItem();
    	}
		
    }

    public function loadByTitle($menu_title = "") {
		return $this->getCollection()->addFieldToFilter('title', $menu_title)->getFirstItem();
    }

	/**
	 *
	 */
	public function getChilds( $id=null, $store_id=null ){

		$collection = $this->getChildsCollection( $id, $store_id );
        //verify if the user is logged in to the backend
        if(!$this->isAdmin() && !empty($collection) && (0 < $collection->getSize()) && (!is_array($store_id) || (is_array($store_id) && !in_array(0, $store_id))) ){

        	$parent = $collection->getFirstItem()->getId();
        	if(!$parent || $collection->getSize() == 0) {
        		$collection = $this->getChildsCollection( $id, array(0, $store_id) );
        	}
        }

		return $collection;
	}

	public function getChildsCollection( $id=null, $store_id = null) {
		if( $id != null ) {
			$collection = $this->getCollection()
								->addFieldToFilter('parent_id', (int)$id);
		}else{
			$collection = $this->getCollection();
		}
				
		$collection->addFieldToFilter('published', 1)
				   ->setOrder('position', 'ASC');

		if(!$collection->getSize()) {
			return false;
		}
		
		if($store_id !== null) {
			$collection->addStoreFilter($store_id);
		}
		return $collection;
	}

	public function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }

	public function updateId($new_id = 0){
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		$resource = Mage::getSingleton('core/resource');
		$table = $resource->getTableName('ves_megamenu/megamenu');
		// now $write is an instance of Zend_Db_Adapter_Abstract
		$readresult=$write->query("UPDATE ".$table." SET megamenu_id=".$new_id." WHERE megamenu_id = ".$this->getId());
	}
}