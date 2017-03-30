<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright	Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
if (!class_exists("Ves_Megamenu_Block_List")) {
    require_once Mage::getBaseDir('code') . DIRECTORY_SEPARATOR . "community".DIRECTORY_SEPARATOR."Ves".DIRECTORY_SEPARATOR."Megamenu".DIRECTORY_SEPARATOR."Block".DIRECTORY_SEPARATOR."List.php";
}

class Ves_Megamenu_Block_Top extends Ves_Megamenu_Block_List {
	
	private $_menu_item_active_class = "active";
	/**
	 *
	 */
	private $_editString = '';

	/**
	 *
	 */
	private $children;
	
	/**
	 *
	 */
	private $shopUrl ;

	/**
	 *
	 */
	private $megaConfig = array();

	private $_editStringCol = '';

	private $_isLiveEdit = true;

	private $_current_category_id = 0;

	private $_current_product_id = 0;

	private $_current_cms_id = 0;

	public function __construct($attributes = array()) {
		parent::__construct($attributes);
	}
	
    function _toHtml() {

		$show_megamenu = $this->getConfig('show');
		if(!$show_megamenu && is_object($this->getLayout()->getBlock('catalog.topnav'))){
			return $this->getLayout()->getBlock('catalog.topnav')->toHtml();
		}
		
		$this->getLayout()->unsetBlock('catalog.topnav');

		//get store
		$store_id = Mage::app()->getStore()->getId();

		$this->assign('store_id', $store_id);

		$parent = $this->getConfig('root_menu_id', 1);
		$parent = !empty($parent)?(int)$parent:1;
		
		$html = $this->getTree( $parent, true, $store_id);
        $this->assign('menuHtml', $html);
        $this->assign('config', $this->_config);

        return parent::_toHtml();
    }

	/**
     * get value of the extension's configuration
     *
     * @return string
     */
    public function getConfig($key, $default = "", $panel = "ves_megamenu") {

        $return = "";
        $value = $this->getData($key);
        //Check if has widget config data
        if($this->hasData($key) && $value !== null) {
          if($key == "pretext") {
            $value = base64_decode($value);
          }
          if($value == "true") {
            return 1;
          } elseif($value == "false") {
            return 0;
          }
          
          return $value;
          
        } else {

          if(isset($this->_config[$key])){

            $return = $this->_config[$key];

            if($return == "true") {
              $return = 1;
            } elseif($return == "false") {
              $return = 0;
            }

          }else{
            $return = Mage::getStoreConfig("ves_megamenu/$panel/$key");
          }
          if($return == "" && $default) {
            $return = $default;
          }

        }

        return $return;
        //return (!isset($this->_config[$key]) || (isset($this->_config[$key]) && empty($this->_config[$key]))) ? $default : $this->_config[$key];
    }
	public function getMenuClassAcitve( $type ){

		switch(  $this->getRequest()->getRouteName()  ){
			case 'catalog':
				if( $this->getRequest()->getParam('id') == $menu->getItem() ){
					return ' active';
				}
				break;
			case 'cms_page':
				if( $this->getRequest()->getParam('page_id') == $menu->getItem() ){
					return ' active';
				}
				break;
			
		}
			

		return '';
	}

	/**
	 *
	 */
	public function hasChild( $id ){
		return (isset($this->children[$id]) && !empty($this->children[$id]));
	}	
	
	/**
	 *
	 */
	public function getNodes( $id ){
		return $this->children[$id];
	}

	public function hasMegaMenuConfig( $menu ){
        $id = $menu->getId();
        return isset( $this->megaConfig[$id] )?$this->megaConfig[$id] : new stdClass(); 
    }
	/**
	 *
	 */
	public function parserMegaConfig( $params ) {
		if( !empty($params) ) { 

			foreach( $params as $param ){
				if( $param && isset($param->id) && $param->id ){
					$this->megaConfig[$param->id] = $param;
				}
			}	
		}
	}
	/**
	 *
	 */
	public function renderAttrs( $menu ) {  

	}

	public function getActiveMenuItem ( $menu ) {
		$active_class = "";
		if($menu) {
			$menu_type = $menu->getType();
			$id = (int)$menu->getItem();
			switch( $menu->getType() ) {
				case "category":
					if(!$this->_current_category_id) {

						$_current_category = Mage::registry('current_category');
						if(!is_object($_current_category))
						{
						    $current_product = Mage::registry('current_product');
						    if(is_object($current_product))
						    {
						        $categories = $current_product->load($current_product->getId())->getCategoryIds();
						        if (is_array($categories) and count($categories))
						        {
						            $this->_current_category_id = (int)$categories[0];
						        }
						    }
						} else {
						    $this->_current_category_id = (int)$_current_category->getId();
						}
					}
					/*If isset current category check menu item id*/
					if($this->_current_category_id && $id == $this->_current_category_id) {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case "product":
					if(!$this->_current_product_id) {
						$current_product = Mage::registry('current_product');
					    if(is_object($current_product))
					    {
					        $this->_current_product_id = (int) $current_product->getId();
					    }
					}
					/*If isset current product check menu item id*/
					if($this->_current_product_id && $id == $this->_current_product_id) {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case "cms_page":
					if(!$this->_current_cms_id) {
						$this->_current_cms_id = Mage::getBlockSingleton('cms/page')->getPage()->getId();
					}
					/*If isset current product check menu item id*/
					if($this->_current_cms_id && $id == $this->_current_cms_id) {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case "ves_blog":
					$module_name = Mage::app()->getRequest()->getModuleName();
					if($module_name == "venusblog") {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case "ves_deal":
					$module_name = Mage::app()->getRequest()->getModuleName();
					if($module_name == "vesdeals") {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case "ves_brand":
					$module_name = Mage::app()->getRequest()->getModuleName();
					if($module_name == "venusbrand") {
						$active_class = $this->_menu_item_active_class;
					}
				break;
				case 'url':
					$url = $menu->getUrl();
					$parsed = parse_url($url);
					if(strpos($url, "{{") !== false) {
						$processor = Mage::helper('cms')->getPageTemplateProcessor();
						$url = $processor->filter($url);
					} elseif (empty($parsed['scheme']) && $url != "#" && $url != "") {
						$url = Mage::getUrl('',array('_direct' => $url, '_type' => 'direct_link'));
					}
					$currentUrl = Mage::helper('core/url')->getCurrentUrl();
					if($url == $currentUrl ) {
						$active_class = $this->_menu_item_active_class;
					}
				break;
			}
		}
		return $active_class;
	}
	/**
	 *
	 */
	public function getLink( $menu ) {
		$id = (int)$menu->getItem();

		switch( $menu->getType() ) {
			case 'category'     :
				return Mage::getModel("catalog/category")->load($id)->getUrl();
				;
			case 'product'      :
				return  Mage::getModel("catalog/product")->load($id)->getProductUrl();
				;
			case 'cms_page'      :
				return  Mage::Helper('cms/page')->getPageUrl($id);
				;
			case 'ves_blog'      :
				$modules = Mage::getConfig()->getNode('modules')->children();
				$modulesArray = (array)$modules;

				if(isset($modulesArray['Ves_Blog'])) {
					$route = Mage::getStoreConfig('ves_blog/general_setting/route');
					$extension = "";
					return  Mage::getBaseUrl().$route.$extension;
				} else {
					return ;
				}
			case 'ves_deal'      :
				$modules = Mage::getConfig()->getNode('modules')->children();
				$modulesArray = (array)$modules;

				if(isset($modulesArray['Ves_Deals'])) {
					$route = Mage::getStoreConfig('ves_deals/deals_setting/route');
					$extension = "";
					return  Mage::getBaseUrl().$route.$extension;
				} else {
					return ;
				}
			case 'ves_brand'      :
				$modules = Mage::getConfig()->getNode('modules')->children();
				$modulesArray = (array)$modules;

				if(isset($modulesArray['Ves_Brand'])) {
					$route = Mage::getStoreConfig('ves_brand/general_setting/route');
					$extension = "";
					return  Mage::getBaseUrl().$route.$extension;
				} else {
					return ;
				}	
			case 'url':
				$url = $menu->getUrl();
				$parsed = parse_url($url);
				if(strpos($url, "{{") !== false) {
					$processor = Mage::helper('cms')->getPageTemplateProcessor();
					$url = $processor->filter($url);
				} elseif (empty($parsed['scheme']) && $url != "#" && $url != "") {
					$url = Mage::getUrl('',array('_direct' => $url, '_type' => 'direct_link'));
				}
				return $url;
			default:
				return ;
		}
	}

	/**
	 *
	 */
	public function getTree( $parent=1 , $edit=false, $store_id = 0){
		$params = Mage::getStoreConfig('ves_megamenu/ves_megamenu/params'); //$this->model_setting_setting->getSetting( 'pavmegamenu_params' );

        if( !empty($params) ){
            $params = json_decode( $params );
        }

        $this->parserMegaConfig( $params );

		if( $edit ){
			$this->_editString  = ' data-id="%s" data-group="%s"  data-cols="%s" ';
		}
		$this->_editStringCol = ' data-colwidth="%s" data-class="%s" ' ;
		$parent_existed = true;
		if($parent == 1 || empty($parent)){
			$parent = 1;
			if($childs = Mage::getModel('ves_megamenu/megamenu')->getChilds( $parent, $store_id )) {
				$parent = $childs->getFirstItem()->getId();

			} else {
				$parent_existed = false;
			}
			
		}
		if(!$parent_existed)
			return;
		
		$childs = Mage::getModel('ves_megamenu/megamenu')->getChilds( null, $store_id );

		foreach($childs as $child ){
			$megaconfig = $this->hasMegaMenuConfig( $child );
			if(1 != $child->getIsGroup()) {
				$child->setData( "is_group", 0 );
			}
			if( isset($megaconfig->submenu) && $megaconfig->submenu != 0) {
				$child->setData("megaconfig", $megaconfig);

				if( isset($megaconfig->group) && ($megaconfig->group == 1)) {
					$child->setData( "is_group_menu", $child->getData("is_group") );
	                $child->setData( "is_group", $megaconfig->group );
	            } 
	        }
	        
            if( isset($megaconfig->submenu) && $megaconfig->submenu == 0) {
                $menu_class = $child->getData("menu_class");
                $child->setData("menu_class", $menu_class .' disable-menu');
            }

			

			$this->children[$child->getParentId()][] = $child;	
		}

		$this->shopUrl = Mage::getBaseUrl(); ;
	 
		if( $this->hasChild($parent) ){
			$data = $this->getNodes( $parent );
			
			// render menu at level 0
			$output = '<ul class="nav navbar-nav megamenu">';
			foreach( $data as $menu ){
 				$menu_class = $menu->getMenuClass();
                if( isset($menu->getMegaconfig()->align) ){
                    $menu_class .= ' '.$menu->getMegaconfig()->align;
                }

                $menu_target = $menu->getTarget();
                $menu_target = $menu_target?' target="'.$menu_target.'" ':'';
				if( $this->hasChild($menu->getId()) || $menu->getTypeSubmenu() == 'html' || $menu->getTypeSubmenu() == 'widget'){
					$menu_class .=' '.$this->getActiveMenuItem($menu);

					$output .= '<li class="parent dropdown '.$menu_class.'" '.$this->renderAttrs($menu).'>';
					if(!$menu->getIsGroup()) {
						$output .= '<span class="open-child hidden-md hidden-lg hidden-sm">'.$this->__("(open)").'</span>';
					}

					$output .= '<a class="dropdown-toggle" data-toggle="dropdown" title="'.$menu->getTitle().'" href="'.$this->getLink( $menu ).'" '.$menu_target.'>';
					
					if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}

					if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
					if($menu->getShowTitle()) {
						$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
					}
					if( $menu->getDescription() ){
						$output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
					}
					if( $menu->getImage()){  $output .= '</span>'; }

					$output .= "<b class=\"caret hidden-xs hidden-sm\"></b></a>";
					
					
					if($menu->getId() > 1) {
						$output .= $this->genTree( $menu->getId(), 1, $menu );	
					}
					$output .= '</li>';
				} else if ( !$this->hasChild($menu->getId()) && isset($menu->getMegaconfig()->rows) && $menu->getMegaconfig()->rows ){
					$output .= $this->genMegaMenuByConfig( $menu->getId(), 1, $menu );
				}elseif($menu->getType() == 'html'){
					$output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>';
					if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}
					if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
					
					if($menu->getShowTitle()) {
						$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
					}
					
					if( $menu->getContentText() ){
						$processor = Mage::helper('cms')->getPageTemplateProcessor();
						$content_text = $processor->filter($menu->getContentText());
						$output .= '<span class="menu-desc">' . $content_text . "</span>";
					}
					if( $menu->getImage()){ $output .= '</span>';	}
					$output .= '</li>';
				}else {
					$menu_class .=' '.$this->getActiveMenuItem($menu);
					$output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>
					<a href="'.$this->getLink( $menu ).'" '.$menu_target.' title="'.$menu->getTitle().'">';

					if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}

					if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
					
					if($menu->getShowTitle()) {
						$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
					}
					
					if( $menu->getDescription() ){
						$output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
					}
					if( $menu->getImage()){ $output .= '</span>';	}

					$output .= '</a></li>';
				}
			}
			$output .= '</ul>';
			
		}

		 return $output;
	
	}
	
	/**
	 *
	 */
	public function genTree( $parentId, $level,$parent, $store_id = 0) {
		 
		$attrw = '';
		$class = $parent->getIsGroup()?"dropdown-mega":"dropdown-menu";
		$class = $class." ";//$parent->getMenuClass();
		$menu_width = (float)$parent->getWidth();
		if( isset($parent->getMegaconfig()->subwidth) &&  $parent->getMegaconfig()->subwidth ){
			$attrw .= ' style="width:'.$parent->getMegaconfig()->subwidth.'px"' ;
		}elseif($menu_width){
			$attrw .= ' style="width:'.$menu_width.'px"' ;
		}

		if( $parent->getTypeSubmenu() == 'html' ) {
			$output = '<div class="'.$class.'"><div class="menu-content">';
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$submenu_content = $processor->filter($parent->getSubmenuContent());
			$output .= $submenu_content;
			$output .= '</div></div>';
			return $output;
		}elseif( $parent->getTypeSubmenu() == 'widget' ) {

			$output = '<div class="'.$class.'"><div class="menu-content">';
			$output .= Mage::getModel("ves_megamenu/widget")->renderContent( $parent->getWidgetId() );
			$output .= '</div></div>';
			return $output;
		}elseif( $this->hasChild($parentId) ){
			
			$data = $this->getNodes( $parentId );			
			$parent_colums = (int)$parent->getColums();
			
			if( $parent_colums > 1  ){

				if( isset($parent->getMegaconfig()->rows) && $parent->getMegaconfig()->rows) {
					
					$cols   = array_chunk( $data, ceil(count($data)/$parent->getColums())  );
					$output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
					foreach( $parent->getMegaconfig()->rows as $rows ){ 
						foreach( $rows as $rowcols ){
							$output .='<div class="row">';
							$colclass = isset($col->colclass)?$col->colclass:'';
							foreach( $rowcols as $key => $col ) {
								$col->colwidth = isset($col->colwidth)?$col->colwidth:6;
								if( isset($col->type) && $col->type == 'menu' && isset($cols[$key]) ){
									$scol = '<div class="mega-col col-sm-'.$col->colwidth.' '.$colclass.'" '.$this->getColumnDataConfig( $col ).'><div class="mega-col-inner">';
									$scol .= '<ul>';
									foreach( $cols[$key] as $menu ) {
										 $scol .= $this->renderMenuContent( $menu, $level+1 );
									}
									$scol .='</ul></div></div>';
								}else {
									$scol = '<div class="mega-col col-sm-'.$col->colwidth.' '.$colclass.'"  '.$this->getColumnDataConfig( $col ).'><div class="mega-col-inner">';
										$scol .= $this->renderWidgetsInCol( $col );
									$scol .= '</div></div>';	
								}
								$output .= $scol;
							}

							$output .= '</div>';
						}
					}
					$output .= '</div></div>';

				}else {
					$output = '<div class="'.$class.' mega-cols cols'.$parent->getColums().'" '.$attrw.' ><div class="dropdown-menu-inner"><div class="row">';
					$cols   = array_chunk( $data, ceil(count($data)/$parent->getColums())  );

					$oSpans = $this->getColWidth( $parent, (int)$parent->getColums() );
				
					foreach( $cols as $i =>  $menus ){

						$output .='<div class="mega-col '.$oSpans[$i+1].' col-'.($i+1).'" data-type="menu"><div class="mega-col-inner"><ul>';
							foreach( $menus as $menu ) {
								$output .= $this->renderMenuContent( $menu, $level+1 );
							}
						$output .='</ul></div></div>';
					}

					$output .= '</div></div></div>';
				}	
				return $output;
			}else {

				$failse = false; 

			///	echo '<pre>' .print_r( $parent, 1 );
				if( isset($parent->getMegaconfig()->rows) && $parent->getMegaconfig()->rows ) {
					$output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
					foreach( $parent->getMegaconfig()->rows as $rows ){ 
						foreach( $rows as $rowcols ){
							$output .='<div class="row">';
							$colclass = isset($col->colclass)?$col->colclass:'';
							foreach( $rowcols as $col ) {
								if( isset($col->type) && $col->type == 'menu' ){
									$colwidth = isset($col->colwidth)?$col->colwidth:'';
									$scol = '<div class="mega-col col-sm-'.$colwidth.' '.$colclass.'" '.$this->getColumnDataConfig( $col ).'><div class="mega-col-inner">';
									$scol .= '<ul>';
									foreach( $data as $menu ){
										$scol .= $this->renderMenuContent( $menu , $level+1 );
									}	
									$scol .= '</ul>';
									
								}else {
									$scol = '<div class="mega-col col-sm-'.$col->colwidth.' '.$colclass.'"  '.$this->getColumnDataConfig( $col ).'><div class="mega-col-inner">';
									$scol .= $this->renderWidgetsInCol( $col );
								}
								$scol .= '</div></div>';
								$output .= $scol;
							}	
							$output .= '</div>';
						}

					}$output .= '</div></div>';
				} else {
					$output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
					$row = '<div class="row"><div class="col-sm-12 mega-col" data-colwidth="12" data-type="menu"><div class="mega-col-inner"><ul>';
					foreach( $data as $menu ){
						$row .= $this->renderMenuContent( $menu , $level+1 );
					}	
					$row .= '</ul></div></div></div></div></div>';

					$output .= $row;
					
				}
				
			}

			return $output;

		}
		return ;
	}

	/**
	 *
	 */
	public function genMegaMenuByConfig( $parentId, $level,$menu  ){
	 
		$attrw = '';
		$class = $level > 1 ? "dropdown-submenu":"dropdown";
		if( isset($menu->getMegaconfig()->align) ){
            $class .= ' '.$menu->getMegaconfig()->align;
        }
        $class .=' '.$this->getActiveMenuItem($menu);

        $menu_target = $menu->getTarget();
        $menu_target = $menu_target?' target="'.$menu_target.'" ':'';

		$output = '<li class="'.$menu->getMenuClass().' parent '.$class.' " '.$this->renderAttrs($menu).'>';
		if(!$menu->getIsGroup() || !$menu->getIsGroupMenu()) {
			$output .= '<span class="open-child hidden-md hidden-lg hidden-sm">'.$this->__("(open)").'</span>';
		}
		$output .= '<a href="'.$this->getLink( $menu ).'" class="dropdown-toggle" title="'.$menu->getTitle().'" data-toggle="dropdown" '.$menu_target.'>';

		if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}
		if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
		
		$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
		if( $menu->getDescription() ){
			$output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
		}
		if( $menu->getImage()){ $output .= '</span>';	}
		$output .= "<b class=\"caret  hidden-sm hidden-xs\"></b></a>";

		if( isset($menu->getMegaconfig()->subwidth) &&  $menu->getMegaconfig()->subwidth ){
            $attrw .= ' style="width:'.$menu->getMegaconfig()->subwidth.'px"' ;
        }
		$class  = 'dropdown-menu';
		$output .= '<div class="'.$class.'" '.$attrw.' ><div class="dropdown-menu-inner">';

		foreach( $menu->getMegaconfig()->rows  as $row ){
		
			$output .= '<div class="row">';
				 foreach( $row->cols as $col ){

					$colclass = isset($col->colclass)?$col->colclass:'';
					 $output .= '<div class="mega-col col-sm-'.$col->colwidth.' '.$colclass.'" '.$this->getColumnDataConfig( $col ).'> <div class="mega-col-inner">';
					 $output .= $this->renderWidgetsInCol( $col );
					 $output .= '</div></div>';
				}
			$output .= '</div>';
		}
		unset($colclass);

		$output .= '</div></div>';
		$output .= '</li>';
		return $output; 
	}
	public function getColumnDataConfig( $col ){
        $output = '';
        if( is_object($col)  && $this->_isLiveEdit ){
            $vars = get_object_vars($col);
            foreach( $vars as $key => $var ){
                $output .= ' data-'.$key.'="'.$var . '" ' ;
            }
        }
        return $output;
    }
    public function getColumnData(  $col, $correct_key = "colclass" ) {
    	$output = '';
        if( is_object($col) ){
            $vars = get_object_vars($col);
            foreach( $vars as $key => $var ){
            	if($key == $correct_key) {
            		$output = $var;
            		break;
            	}
            }
        }
        return $output;
    }
	public function renderWidgetsInCol( $col ){
		 if( is_object($col) && isset($col->widgets)  ){
		 	$widgets = $col->widgets; 
		 	$widgets = explode( '|wid-', '|'.$widgets );
			if( !empty($widgets) ){
				unset( $widgets[0] );
				$output = '';
				foreach( $widgets as $wid ){
					$output .= Mage::getModel("ves_megamenu/widget")->renderContent( $wid );
				}

				return $output;
			}
		 }
	}
	/**
	 *
	 */
	public function renderMenuContent( $menu , $level ){
		$output = '';
		$class = $menu->getIsGroup()?"mega-group":"";
		$menu_class = ' '.$class." ".$menu->getMenuClass();
		if( $menu->getType() == 'html' ){
			$output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>';
			$processor = Mage::helper('cms')->getPageTemplateProcessor();
			$content_text = $processor->filter($menu->getContentText());
			$output .= '<div class="menu-content">'.$content_text.'</div>'; 
			$output .= '</li>';
			return $output;
		}
		$menu_target = $menu->getTarget();
        $menu_target = $menu_target?' target="'.$menu_target.'" ':'';

		if( $this->hasChild($menu->getId()) || $menu->getTypeSubmenu() == 'html' || $menu->getTypeSubmenu() == 'widget'){
			$menu_class .=' '.$this->getActiveMenuItem($menu);
			$output .= '<li class="parent dropdown-submenu'.$menu_class.'" '.$this->renderAttrs($menu). '>';
			if( $menu->getShowTitle() ){
				if(!$menu->getIsGroup()) {
					$output .='<span class="open-child hidden-md hidden-lg hidden-sm">'.$this->__("(open)").'</span>';
				}
				
				$output .= '<a class="dropdown-toggle" data-toggle="dropdown" title="'.$menu->getTitle().'" href="'.$this->getLink( $menu ).'" '.$menu_target.'>';
				$t = '%s';
				if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}
				if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
				$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
				if( $menu->getDescription() ){
					$output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
				}
				if( $menu->getImage()){ 
					$output .= '</span>';
				}
				$output .= "<b class=\"caret hidden-xs hidden-sm\"></b>";
				$output .= '</a>';
			}	
			if($menu->getId() > 1) {
				$output .= $this->genTree( $menu->getId(), $level, $menu );
			}
			$output .= '</li>';

		}else if ( isset($menu->getMegaconfig()->rows) && $menu->getMegaconfig()->rows ){
			$output .= $this->genMegaMenuByConfig( $menu->getId(), $level, $menu );
		}else {
			$menu_class .=' '.$this->getActiveMenuItem($menu);
			$output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>';
			if( $menu->getShowTitle() ){
				$output .= '<a href="'.$this->getLink( $menu ).'" '.$menu_target.' title="'.$menu->getTitle().'">';
				if( $menu->getMenuIconClass()){ $output .= '<i class="'.$menu->getMenuIconClass().'"></i>';	}
				if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';	}
				$output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
				if( $menu->getDescription() ){
					$output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
				}
				if( $menu->getImage()){ 
					$output .= '</span>';
				}

				$output .= '</a>';
			}
			$output .= '</li>';
		}
		return $output;
	}
	/**
	 *
	 */
	public function getColWidth( $menu, $cols ){
		$output = array();
		$submenu_column_width  = $menu->getSubmenuColumWidth();
		$split = preg_split('#\s+#',$submenu_column_width );
		if( !empty($split) && !empty($submenu_column_width) ){
			foreach( $split as $sp ) {
				$tmp = explode("=",$sp);
				if( count($tmp) > 1 ){
					$output[trim(preg_replace("#col#","",$tmp[0]))]=(int)$tmp[1];
				}
			}
		}
		$tmp = array_sum($output);
		$spans = array();
		$t = 0; 
		for( $i=1; $i<= $cols; $i++ ){
			if( array_key_exists($i,$output) ){
				$spans[$i] = 'col-sm-'.$output[$i];
			}else{		
				if( (12-$tmp)%($cols-count($output)) == 0 ){
					$spans[$i] = "col-sm-".((12-$tmp)/($cols-count($output)));
				}else {
					if( $t == 0 ) {
						$spans[$i] = "col-sm-".( ((11-$tmp)/($cols-count($output))) + 1 ) ;
					}else {
						$spans[$i] = "col-sm-".( ((11-$tmp)/($cols-count($output))) + 0 ) ;
					}
					$t++;
				}					
			}
		}
		return $spans;
	}
	/**
	 * render static block by id
	 */
	public function renderBlockStatic( $id ){
		return $this->getLayout()->createBlock('cms/block')->setBlockId( $id )->toHtml();
	}
	
	/**
	 * get childrent menu by parent id
	 */
	public function getMenuList( $id ){
		return $this->menus[$id];
	}
	
	/**
	 * check menu having sub menu or not
	 */
	public function hasSubMenu( $id ){
		return isset($this->menus[$id]); 
	}
	
	/**
	 * get url icon
	 */
	public function getMenuIcon( $image ){
		if ( file_exists( Mage::getBaseDir('media') . DS . $image ) ){
			return Mage::getBaseDir('media') . DS . $image;
		}
		return '';
	}
	 
}
