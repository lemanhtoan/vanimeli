<?php 

class Ves_Megamenu_Helper_Subtype extends Mage_Core_Helper_Abstract {
	
	public function renderMenuByCategories( $menu, $dwidth, $level, $owidth=array() ){
		
		$categories = Mage::getModel('ves_megamenu/megamenu')
								->getListCategories( $menu['submenu_content'] );
		if( !$categories ){
			return ;
			break;
		}	
		$categories = $categories->getItems();		
		$cols = (int)$menu['colums'] > 0 ? (int)$menu['colums'] : 1;
		$dwidth = (int)$menu['submenu_width']>0?(int)$menu['submenu_width']:200;
		$colswidth = (int)$menu['colum_width']>0?(int)$menu['colum_width']:200;
		$ocolWidth = $this->getColWidth( $menu );
		if( (int)$menu['submenu_width'] <= 0 ){$dwidth = $colswidth*$cols; }
		$spcols = array_chunk( $categories, ceil(count($categories)/$cols) );	
		
		$context = "";	
		foreach( $spcols as $k => $categories){
			$tmp = isset($ocolWidth['col'.($k+1)])?$ocolWidth['col'.($k+1)]:$colswidth;
			$context .= '<div class="vescolumn col'.($k+1).'" style="width:'.$tmp.'px">';
				$context .='<ul class="megamenu">';
				$j=0;
				foreach( $categories as $category ){ 
					$iclass="level".($level+1);
					if( $j==0){	$iclass .=" first";}
					elseif( ($j+1)==count($categories) ){	$iclass .=" last";}
					$link = Mage::helper('catalog/category')->getCategoryUrl( $category );
					$context .='<li class="mega '.$iclass.'">';
						$context .= '<a href="'.$link.'" class="mega"><span class="menu-title">'.$category->getName().'</span></a>';
					$context .='</li>';$j++;
				}
				$context .='</ul>';
			$context .= '</div>';
		}
		
		$gClass = $menu['is_group']==1?" menugroup":" menunongroup";
		$html = '<div style="width:'.$dwidth.'px" class="level'.($level+1).$gClass.'"><div class="submenu-wrapper">';
		$html .= $context;
		$html .= '</div></div>';
			
		return $html;
	}
	
	public function renderMenuByCMSs( $menu, $dwidth, $level, $owidth=array() ){
		$cms = Mage::getModel('ves_megamenu/megamenu')
				->getListCMSs( $menu['submenu_content'] );
		if( !$cms ){
			return ;
			break;
		}	
		$cms = $cms->getItems();	
		$cols = (int)$menu['colums'] > 0 ? (int)$menu['colums'] : 1;
		$dwidth = (int)$menu['submenu_width']>0?(int)$menu['submenu_width']:200;
		$colswidth = (int)$menu['colum_width']>0?(int)$menu['colum_width']:200;
		$ocolWidth = $this->getColWidth( $menu );
		if( (int)$menu['submenu_width'] <= 0 ){$dwidth = $colswidth*$cols; }
		
		$spcols = array_chunk( $cms, ceil(count($cms)/$cols) );	
		$context = "";	
		foreach( $spcols as $k => $cms){
			$tmp = isset($ocolWidth['col'.($k+1)])?$ocolWidth['col'.($k+1)]:$colswidth;
			$context .= '<div class="vescolumn col'.($k+1).'" style="width:'.$tmp.'px">';
				$context .='<ul class="megamenu">';
				$j=0;
				foreach( $cms as $page ){ 
					$iclass="level".($level+1);
					if( $j==0){	$iclass .=" first";}
					elseif( ($j+1)==count($categories) ){	$iclass .=" last";}
					$link = Mage::getUrl(null, array('_direct' => $page->getIdentifier()));
					$context .='<li class="mega '.$iclass.'">';
						$context .= '<a href="'.$link.'" class="mega"><span class="menu-title">'.$page->getTitle().'</span></a>';
					$context .='</li>';$j++;
				}
				$context .='</ul>';
			$context .= '</div>';
		}	
		$gClass = $menu['is_group']==1?" menugroup":" menunongroup";
		$html = '<div style="width:'.$dwidth.'px"  class="level'.($level+1).$gClass.'"><div class="submenu-wrapper">';
		$html .= $context;
		$html .= '</div></div>';
		return $html;
	}
	
	public function renderMenuByText( $menu, $dwidth, $level, $owidth=array() ){
		$html = '<div style="width:'.$dwidth.'px" id="menu-'.$menu['megamenu_id'].'_menusub_sub'.($level+1).'" class="divul_container   level'.($level+1).' menunongroup"><div class="submenu-wrapper">';
		$html .= $menu['content_text'];
		$html .= '</div></div>';
		return $html;
	}
	
	public function renderMenuByModules( $menu, $dwidth, $level, $owidth=array() ){
		$ids = explode(',', $menu['submenu_content']);
		$colswidth = $menu['colum_width'];
		if( !empty($ids) ){
			$modules = array();
			foreach( $ids as $id ){
			  $modules = $this->getLayout()->createBlock('cms/block')->setBlockId( $id )->toHtml();
			}
		
			$cols = (int)$menu['colums'] > 0 ? (int)$menu['colums'] : 1;
			$dwidth = (int)$menu['submenu_width']>0?(int)$menu['submenu_width']:200;
			$colswidth = (int)$menu['colum_width']>0?(int)$menu['colum_width']:200;
			$ocolWidth = $this->getColWidth( $menu );
			if( (int)$menu['submenu_width'] <= 0 ){$dwidth = $colswidth*$cols; }
		
			$spcols = array_chunk( $modules, ceil(count($modules)/$cols) );	
			$context = "";	
			foreach( $spcols as $k => $modules){
				$tmp = isset($ocolWidth['col'.($k+1)])?$ocolWidth['col'.($k+1)]:$colswidth;
				$context .= '<div class="vescolumn col'.($k+1).'" style="width:'.$tmp.'px">';
					$context .='<ul class="megamenu">';
					foreach( $modules as $module ){ 
					 
						$context .='<li class="mega">';
							$context .= $module;
						$context .='</li>';
					}
					$context .='</ul>';
				$context .= '</div>';
			}
			$html = '<div style="width:'.$dwidth.'px" class=" level'.($level+1).' menunongroup"><div class="submenu-wrapper">';
			$html .= $context;
			$html .= '</div></div>';
			return $html;
		}
		return ;
	}
	
	
	
	public function getColWidth( $menu ){
		$output = array();
		$split = preg_split('#\s+#',$menu['submenu_colum_width'] );
		if( !empty($split) ){
			foreach( $split as $sp ) {
				$tmp = explode("=",$sp);
				$output[trim($tmp[0])]=(int)$tmp[1];
			}
		}
		return $output;
	}
}

