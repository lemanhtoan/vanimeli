<?php
class Ves_Base_Block_Widget_Reviewrotator extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface{

	protected $_reviewsCollection;

	public function __construct($attributes = array())
	{
		$this->setTemplate( "ves/base/reviewsrotator.phtml" );
		parent::__construct( $attributes );
	}



	public function getReviews($number,$type){
		$reviews = Mage::getModel('review/review')->getResourceCollection();
		$reviews->addStoreFilter(Mage::app()->getStore()->getId());
		$reviews->setPageSize($number);
		if($type=='random'){
			$reviews->getSelect()->order(new Zend_Db_Expr('RAND()'));
		}else{
			$reviews->setOrder('review_id');
		}
		$reviewCollection = array();
		foreach ($reviews as $review) {
			$reviewId = $review->getId();
			$productId = $review->getEntityPkValue();
			$obj = Mage::getModel('catalog/product');
			$_product = $obj->load($productId);
			$reviewCollection[$reviewId]['review'] = array(
				'title' => $review->getTitle(),
				'description' => $review->getDetail(),
				'url' => $this->getUrl('review/product/view',array('id'=>$reviewId)),
				'created_at' => Mage::helper('core')->formatDate($review->getCreatedAt(), 'full', false),
				);
			$reviewCollection[$reviewId]['product'] = array(
				'name' => $_product->getName(),
				'url' => $_product->getProductUrl(),
				);
			$reviewCollection[$reviewId]['author'] = array(
				'name' => $review->getNickname(),
				);	
			$votesCollection = Mage::getModel('rating/rating_option_vote')
			->getResourceCollection()
			->setReviewFilter($reviewId)
			->setStoreFilter(Mage::app()->getStore()->getId())
			->load();
			foreach ($votesCollection as $vote) {
				$id = $vote->getRatingId();
				$ratings = Mage::getModel('rating/rating')->getCollection()->addFilter('rating_id',$id);
				foreach ($ratings as $rating) {
					$reviewCollection[$reviewId]['rating'][] = array(
						'name' => $rating->getRatingCode(),
						'percent' => $vote->getPercent(),
						'value' => $vote->getValue(),
						);	
				}
			}
		}
		return $reviewCollection;
	}


	protected function _toHtml(){
		if(!Mage::getStoreConfig('ves_base/general_setting/show')) {
			return ;
		}
		$number = $this->getConfig('show_number');
		$type = $this->getConfig('show_type');
		$this->assign('reviews',$this->getReviews($number,$type));
		$this->assign('title',$this->getConfig('title'));
		$this->assign('author',$this->getConfig('enable_author'));
		$this->assign('description',$this->getConfig('enable_description'));
		$this->assign('reviewTitle',$this->getConfig('enable_title'));
		return parent::_toHtml();
	}

	/**
	 * get value of the extension's configuration
	 *
	 * @return string
	 */
	public function getConfig( $key, $default = ""){
	    $value = $this->getData($key);
	    //Check if has widget config data
	    if($this->hasData($key) && $value !== null) {

	      if($value == "true") {
	        return 1;
	      } elseif($value == "false") {
	        return 0;
	      }
	      
	      return $value;
	      
	    }
	    return $default;
	}
}