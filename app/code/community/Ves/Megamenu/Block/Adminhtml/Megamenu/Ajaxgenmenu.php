<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright   Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
?>
<?php
class Ves_Megamenu_Block_Adminhtml_Megamenu_Ajaxgenmenu extends Mage_Adminhtml_Block_Widget_Form_Container
{
    var $treemenu = null;
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

    public function __construct()
    {
        $this->_blockGroup  = 'ves_megamenu';
        $this->_objectId    = 'ves_megamenu_id';
        $this->_controller  = 'adminhtml_megamenu';
        $this->_mode        = 'ajaxgenmenu';

        $this->setTemplate('ves_megamenu/megamenu/megamenu_tree.phtml');

        $store_id = Mage::helper("ves_megamenu")->getStoreId();

        $params = "";

        if($store_id) {
            $params = Mage::getStoreConfig('ves_megamenu/ves_megamenu/params', $store_id); 
        }
        if(!$params || !$store_id) {
              $params = Mage::getStoreConfig('ves_megamenu/ves_megamenu/params');
        }
      

        if( !empty($params) ){
            $params = json_decode( $params );
        }

        $this->treemenu = $this->getTree( 1, true, $params, $store_id );

    }

    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getTreemenu(){
        return $this->treemenu;
    }

    public function getLiveSiteUrl(){
        $live_site_url = Mage::getBaseUrl();
        $live_site_url = str_replace("index.php/", "", $live_site_url);
        return $live_site_url;

    }

    /**
     *
     */
    public function getTree( $parent=1 , $edit=false, $params, $store_id = 0 ) {

        $this->parserMegaConfig( $params );
        if( $edit ){ 
            $this->_editString  = ' data-id="%s" data-group="%s"  data-cols="%s" ';
        }
        $this->_editStringCol = ' data-colwidth="%s" data-class="%s" ' ;

        if($parent == 1 || empty($parent)) {
            $parent = 1;
            $childs = Mage::getModel('ves_megamenu/megamenu')->getChilds( $parent, $store_id );
            $parent = $childs->getFirstItem()->getId();
        }

        $childs = Mage::getModel('ves_megamenu/megamenu')->getChilds( null, $store_id );

        foreach($childs as $child ) {
            $megaconfig = $this->hasMegaMenuConfig( $child );
            
            if( isset($megaconfig->submenu) && $megaconfig->submenu != 0) {
                $child->setData("megaconfig", $megaconfig);
                if( isset($megaconfig->group) && $megaconfig->group) {
                    $child->setData( "is_group", $megaconfig->group );
                } 
            }
            if( isset($megaconfig->submenu) && $megaconfig->submenu == 0) {
                $menu_class = $child->getData("menu_class");
                $child->setData("menu_class", $menu_class .' disable-menu');
            }

            $this->children[$child->getParentId()][] = $child;
        }

        $this->shopUrl = Mage::getBaseUrl();
        if( $this->hasChild($parent) ) {
            $data = $this->getNodes( $parent );
            // render menu at level 0
            $output = '<ul class="nav navbar-nav megamenu">';
            foreach( $data as $menu ) {
                $menu_class = $menu->getMenuClass();
                if( isset($menu->getMegaconfig()->align) ){
                    $menu_class .= ' '.$menu->getMegaconfig()->align;
                }
                if( $this->hasChild($menu->getId()) || $menu->getTypeSubmenu() == 'html' || $menu->getTypeSubmenu() == 'widget') {
                    $output .= '<li class="parent dropdown'.$menu_class.'" '.$this->renderAttrs($menu).'>
                    <a class="dropdown-toggle" data-toggle="dropdown" href="'.$this->getLink( $menu ).'">';
                    
                    if( $menu->getImage()) { $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';   }
                    
                    $output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
                    if( $menu->getDescription() ) {
                        $output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
                    }
                    $output .= "<b class=\"caret\"></b></a>";
                    if( $menu->getImage()) {  $output .= '</span>'; }
                    
                    $output .= $this->genTree( $menu->getId(), 1, $menu );        
                    $output .= '</li>';

                } else if ( !$this->hasChild($menu->getId()) && isset($menu->getMegaconfig()->rows) && $menu->getMegaconfig()->rows) {
                    $output .= $this->genMegaMenuByConfig( $menu->getId(), 1, $menu );
                } elseif($menu->getType() == 'html') {
                    $output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>';
                    
                    if( $menu['image']){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';    }
                    
                    if($menu->getShowTitle()) {
                        $output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
                    }
                    
                    if( $menu->getContentText() ){
                        $processor = Mage::helper('cms')->getPageTemplateProcessor();
                        $content_text = $processor->filter($menu->getContentText());
                        $output .= '<span class="menu-desc">' . $content_text . "</span>";
                    }
                    if( $menu->getImage()){ $output .= '</span>';   }
                    $output .= '</li>';
                } else {
                    $output .= '<li class="'.$menu_class.'" '.$this->renderAttrs($menu).'>
                    <a href="'.$this->getLink( $menu ).'">';
                    
                    if( $menu->getImage()) { $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';   }
                    
                    $output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
                    if( $menu->getDescription() ) {
                        $output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
                    }
                    if( $menu->getImage()){ $output .= '</span>';  }
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
    public function renderAttrs( $menu ){  
        $t = sprintf( $this->_editString, $menu->getId(), $menu->getIsGroup(), $menu->getColumns()  );
        if( $this->_isLiveEdit  ){  
            if( isset($menu->getMegaconfig()->subwidth) &&  $menu->getMegaconfig()->subwidth ){
                $t .= ' data-subwidth="'.$menu->getMegaconfig()->subwidth.'" ';
            }
            $t .= ' data-submenu="'.(isset($menu->getMegaconfig()->submenu)?$menu->getMegaconfig()->submenu:$this->hasChild($menu->getId())).'"'; 
            $t .= ' data-align="'.(isset($menu->getMegaconfig()->align)?$menu->getMegaconfig()->align:"aligned-left").'"';
        }   
        return $t;
    }   

    /**
     *
     */
    public function renderMenuContent( $menu , $level ){

        $output = '';
        $class = $menu->getIsGroup()?"mega-group":"";
        $menu->setData('menu_class', ' '.$class);
        if( $menu->getType() == 'html' ){ 
            $output .= '<li class="'.$menu->getMegaClass().'" '.$this->renderAttrs($menu).'>';
            $processor = Mage::helper('cms')->getPageTemplateProcessor();
            $content_text = $processor->filter($menu->getContentText());
            $output .= '<div class="menu-content">'.$content_text.'</div>'; 
            $output .= '</li>';
            return $output;
        }
        if( $this->hasChild($menu->getId()) || $menu->getTypeSubmenu() == 'html' || $menu->getTypeSubmenu() == 'widget') {

            $output .= '<li class="parent dropdown-submenu'.$menu->getMenuClass().'" '.$this->renderAttrs($menu). '>';
            if( $menu->getShowTitle() ){
                $output .= '<a class="dropdown-toggle" data-toggle="dropdown" href="'.$this->getLink( $menu ).'">';
                $t = '%s';
                if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';   }
                $output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
                if( $menu->getDescription() ){
                    $output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
                }
                $output .= "<b class=\"caret\"></b>";
                if( $menu->getImage()){ 
                    $output .= '</span>';
                }
                $output .= '</a>';
            }   
            $output .= $this->genTree( $menu->getId(), $level, $menu );
            $output .= '</li>';

        } else if (  $menu->getMegaconfig() && $menu->getMegaconfig()->rows ){
            $output .= $this->genMegaMenuByConfig( $menu->getId(), $level, $menu );
        }else {
            $output .= '<li class="'.$menu->getMenuClass().'" '.$this->renderAttrs($menu).'>';
            if( $menu->getShowTitle() ){ 
                $output .= '<a href="'.$this->getLink( $menu ).'">';
            
                if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$menu->getImage().'\') no-repeat;">';   }
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
        
        $split = preg_split('#\s+#',$menu->getData('submenu_colum_width') );
        $submenu_colum_width = $menu->getData('submenu_colum_width');
        if( !empty($split) && !empty($submenu_colum_width) ){
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
     *
     */
    public function genTree( $parentId, $level,$parent  ){
        
        $attrw = '';
        $class = $parent->getIsGroup()?"dropdown-mega":"dropdown-menu";
        $menu_width = (float)$parent->getWidth();
        if( isset($parent->getMegaconfig()->subwidth) &&  $parent->getMegaconfig()->subwidth ){
            $attrw .= ' style="width:'.$parent->getMegaconfig()->subwidth.'px"' ;
        }elseif($menu_width){
            $attrw .= ' style="width:'.$menu_width.'px"' ;
        }


        if( $parent->getTypeSubmenu() == 'html' ){
            $output = '<div class="'.$class.'"><div class="menu-content">';
            $processor = Mage::helper('cms')->getPageTemplateProcessor();
            $content_text = $processor->filter($parent->getSubmenuContent());
            $output .= $content_text;
            $output .= '</div></div>';
            return $output;
        }elseif( $parent->getTypeSubmenu() == 'widget' ) {

            $output = '<div class="'.$class.'"><div class="menu-content">';
            $output .= Mage::getModel("ves_megamenu/widget")->renderContent( $parent->getWidgetId() );
            $output .= '</div></div>';
            return $output;
        }elseif( $this->hasChild($parentId) ){
            
            $data = $this->getNodes( $parentId );           
            $parent['colums'] = (int)$parent['colums'];
            if( $parent['colums'] > 1  ){

                if( !empty($parent['megaconfig']->rows) ) {
                    
                    $cols   = array_chunk( $data, ceil(count($data)/$parent['colums'])  );
                    $output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
                    foreach( $parent['megaconfig']->rows as $rows ){ 
                        foreach( $rows as $rowcols ){
                            $output .='<div class="row">';
                            
                            foreach( $rowcols as $key => $col ) {
                                $col->colwidth = isset($col->colwidth)?$col->colwidth:6;
                                if( isset($col->type) && $col->type == 'menu' && isset($cols[$key]) ){
                                    $scol = '<div class="mega-col col-sm-'.$col->colwidth.'" data-type="menu" '.$this->getColumnDataConfig( $col ).'><div class="inner">';
                                    $scol .= '<div class="ves-submenu"><ul>';
                                    foreach( $cols[$key] as $menu ) {
                                         $scol .= $this->renderMenuContent( $menu, $level+1 );
                                    }
                                    $scol .='</ul></div></div></div>';
                                }else {
                                    $scol = '<div class="mega-col col-sm-'.$col->colwidth.'"  '.$this->getColumnDataConfig( $col ).'><div class="inner">';

                                    $scol .= '</div></div>';    
                                }
                                $output .= $scol;
                            }

                            $output .= '</div>';
                        }
                    }
                    $output .= '</div></div>';

                }else { 
                    $output = '<div class="'.$class.' mega-cols cols'.$parent['colums'].'" '.$attrw.' ><div class="dropdown-menu-inner"><div class="row">';
                    $cols   = array_chunk( $data, ceil(count($data)/$parent['colums'])  );

                    $oSpans = $this->getColWidth( $parent, (int)$parent['colums'] );
                
                    foreach( $cols as $i =>  $menus ){

                        $output .='<div class="mega-col '.$oSpans[$i+1].' col-'.($i+1).'" data-type="menu"><div class="inner"><div class="ves-submenu"><ul>';
                            foreach( $menus as $menu ) {
                                $output .= $this->renderMenuContent( $menu, $level+1 );
                            }
                        $output .='</ul></div></div></div>';
                    }

                    $output .= '</div></div></div>';
                }   
                return $output;
            }else {

                

                $failse = false; 
                if( !empty($parent['megaconfig']->rows) ) {
                    $output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
                    foreach( $parent['megaconfig']->rows as $rows ){ 
                        foreach( $rows as $rowcols ){
                            $output .='<div class="row">';
                            foreach( $rowcols as $col ) {
                                
                                if( isset($col->type) && $col->type == 'menu' ){
                                    $colwidth = isset($col->colwidth)?$col->colwidth:'';
                                    $scol = '<div class="mega-col col-sm-'.$colwidth.'" data-type="menu" '.$this->getColumnDataConfig( $col ).'><div class="inner">';
                                    $scol .= '<div class="ves-submenu"><ul>';
                                    foreach( $data as $menu ){
                                        $scol .= $this->renderMenuContent( $menu , $level+1 );
                                    }   
                                    $scol .= '</ul></div>';
                                    
                                }else {
                                    $scol = '<div class="mega-col col-sm-'.$col->colwidth.'"  '.$this->getColumnDataConfig( $col ).'><div class="inner">';
                                }
                                $scol .= '</div></div>';
                                $output .= $scol;
                            }   
                            $output .= '</div>';
                        }

                    }$output .= '</div></div>';
                } else {
                    $output = '<div class="'.$class.' level'.$level.'" '.$attrw.' ><div class="dropdown-menu-inner">';
                    $row = '<div class="row"><div class="col-sm-12 mega-col" data-colwidth="12" data-type="menu" ><div class="inner"><div class="ves-submenu"><ul>';
                    foreach( $data as $menu ){
                        $row .= $this->renderMenuContent( $menu , $level+1 );
                    }   
                    $row .= '</ul></div></div></div></div>';

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
            case 'url':
                return $menu->getUrl();
            default:
                return ;
        }
    }

    /**
     *
     */
    public function hasChild( $id ){
        return isset($this->children[$id]);
    }   
    
    /**
     *
     */
    public function getNodes( $id ){
        return $this->children[$id];
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

        $output = '<li class="'.$menu->getMenuClass().' parent '.$class.' " '.$this->renderAttrs($menu).'>
                    <a href="'.$this->getLink( $menu ).'" class="dropdown-toggle" data-toggle="dropdown">';
                    
                    if( $menu->getImage()){ $output .= '<span class="menu-icon" style="background:url(\''.$this->shopUrl."image/".$menu->getImage().'\') no-repeat;">';   }
                    
                    $output .= '<span class="menu-title">'.$menu->getTitle()."</span>";
                    if( $menu->getDescription() ){
                        $output .= '<span class="menu-desc">' . $menu->getDescription() . "</span>";
                    }
                    if( $menu->getImage()){ $output .= '</span>';  }
                    $output .= "<b class=\"caret\"></b></a>";

        if( isset($menu->getMegaconfig()->subwidth) &&  $menu->getMegaconfig()->subwidth ){
            $attrw .= ' style="width:'.$menu->getMegaconfig()->subwidth.'px"' ;
        }
        $class  = 'dropdown-menu';
        $output .= '<div class="'.$class.'" '.$attrw.' ><div class="dropdown-menu-inner">';

        foreach( $menu->getMegaconfig()->rows  as $row ){
        
            $output .= '<div class="row">';
                foreach( $row->cols as $col ){
                     $output .= '<div class="mega-col col-sm-'.$col->colwidth.'" '.$this->getColumnDataConfig( $col ).'> <div>';

                     $output .= '</div></div>';
                }
            $output .= '</div>';
        }

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
    /**
     *
     */
    public function getColumnSpans( $col ){
        
    }

    public function hasMegaMenuConfig( $menu ){
        $id = $menu->getId();
        return isset( $this->megaConfig[$id] )?$this->megaConfig[$id] :array(); 
    }

    /**
     *
     */
    public function parserMegaConfig( $params ){
        if( !empty($params) ) { 
            foreach( $params as $param ){
                if( $param  && isset($param->id) && $param->id  ){
                    $this->megaConfig[$param->id] = $param;
                }
            }   
        }
    }
   
}