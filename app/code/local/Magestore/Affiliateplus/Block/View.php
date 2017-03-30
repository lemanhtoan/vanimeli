<?php
class Magestore_Affiliateplus_Block_View extends Mage_Core_Block_Template
{
	/**
	 * Catalog Product collection
	 *
	 * @var Mage_Catalog_Model_Resource_Product_Collection
	 */
	protected $_productCollection;

	/**
	 * Prepare layout
	 *
	 * @return Mage_CatalogSearch_Block_Result
	 */
	protected function _prepareLayout()
	{
		// add Home breadcrumb
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		$account = $this->_getAccount();
		if ($breadcrumbs) {
			$title = '';
			if($account && $account->getStatus() == 1) {
				$title = $this->__("Affiliate Page Shop: '%s'", $account->getName());
			} else {
				$title = $this->__("Affiliate Page Shop");
			}


			$breadcrumbs->addCrumb('home', array(
				'label' => $this->__('Home'),
				'title' => $this->__('Go to Home Page'),
				'link'  => Mage::getBaseUrl()
			))->addCrumb('view', array(
				'label' => $title,
				'title' => $title
			));
		}

		$this->getLayout()->getBlock('head')->setTitle($title);

		return parent::_prepareLayout();
	}

	/**
	 *
	 */
	public function setListCollection() {
		$this->getChild('affiliateplus_view_product_list')
			->setCollection($this->_getProductCollection())
			->setColumnCount(5);
	}


	/**
	 * Retrieve Search result list HTML output
	 *
	 * @return string
	 */
	public function getProductListHtml()
	{
		return $this->getChildHtml('affiliateplus_view_product_list');
	}

	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
	 */
	protected function _getProductCollection()
	{
		$productIds = $this->_getListProductByAccount();
		if (is_null($this->_productCollection)) {
			$collection = Mage::getModel('catalog/product')->getCollection()
				->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
				->addMinimalPrice()
				->addFinalPrice()
				->addTaxPercents()
				->addFieldToFilter('entity_id', array('in'=>$productIds))
				;

			$this->_productCollection = $collection;


			//2 dong in dam ben duoi la bat buoc phai co
			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($this->_productCollection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($this->_productCollection);
		}
		return $this->_productCollection;

	}

	/**
	 * Retrieve search result count
	 *
	 * @return string
	 */
	public function getResultCount()
	{
		if (!$this->getData('result_count')) {
			$size = $this->_getProductCollection()->getSize();
			$this->setResultCount($size);
		}
		return $this->getData('result_count');
	}

	/**
	 *	Added By Adam (31/08/2016)
	 * Get list product Ids which were assigned to affiliate
	 */
	protected function _getListProductByAccount(){
		$account = $this->_getAccount();
		$productIds = array();
		if($account && $account->getStatus() == 1){
			$collection = Mage::getModel('affiliateplus/accountproduct')->getCollection()
				->addFieldToFilter('account_id', $account->getId());
			foreach($collection as $item) {
				$productIds[] = $item->getProductId();
			}
		}
		return $productIds;
	}

	/**
	 * Retrieve query model object
	 *
	 * @return Mage_CatalogSearch_Model_Query
	 */
	protected function _getAccountId()
	{
		return $this->getRequest()->getParam('id');
	}

	/**
	 * Added by adam (31/08/2016): get Account by ID
	 * @return null
	 */
	protected function _getAccount(){
		$id = $this->_getAccountId();
		$account = null;
		if(id) {
			$account = Mage::getModel('affiliateplus/account')->load($id);
		}
		return $account;
	}
}