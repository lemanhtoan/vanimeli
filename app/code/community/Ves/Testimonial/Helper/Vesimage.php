<?php 
if( !defined('PhpThumbFactoryLoaded') ) {

	require_once dirname(__FILE__).DS.'phpthumb'.DS.'ThumbLib.inc.php';
	define('PhpThumbFactoryLoaded',1);
}
 if( !class_exists("Ves_Testimonial_Helper_Vesimage") ){
	class Ves_Testimonial_Helper_Vesimage  {
		
		/**
		 * @var string $_thumbnailPath
		 *
		 * @access private 
		 */
		protected $_thumbnailPath = '';
		
		/**
		 * @var string $_thumbnailPath
		 *
		 * @access private 
		 */
		protected $_thumbnailURL = '';
		
		/**
		 * set path of folder where will store images rendered.
		 *
		 * @access private 
		 */
		public function setStoredFolder( $subpath='vesthumbs' ){
			$this->_thumbnailPath = Mage::getBaseDir().DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$subpath.DIRECTORY_SEPARATOR;
		
			if(!is_dir($this->_thumbnailPath) ){
				mkdir($this->_thumbnailPath,0755);	
			}
			$this->_thumbnaiURL = Mage::getBaseUrl('media').$subpath.'/';
			
	
		}
		
		/**
		 * precess creating thumbnail image.
		 */
		public  function resize( $path, $width=100, $height=100, $isRender=true, $type='l'   ){
			
			if( !$isRender ){
				return $path;
			}
			$imagSource =Mage::getBaseDir().DIRECTORY_SEPARATOR. str_replace( '/', DIRECTORY_SEPARATOR,  $path );
		
			if( file_exists($imagSource)  ) {
				$tmp = explode("/", $path);
				$imageName = $type."-".$tmp[count($tmp)-1];
				$thumbPath = $this->_thumbnailPath.$imageName;
		
				if( !file_exists($thumbPath) ) {	
					$thumb = PhpThumbFactory::create( $imagSource  );  		
					$thumb->adaptiveResize( $width, $height);
					$thumb->save( $thumbPath  ); 
				}
				$path = $this->_thumbnaiURL.$imageName;
			
			} 
			return $path;
		}
	}
 }
?>