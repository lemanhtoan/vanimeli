<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * New products widget
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Ves_ProductList_Block_Widget_New extends Ves_ProductList_Block_Gridwidget
    implements Mage_Widget_Block_Interface
{
    /**
     * Display products type
     */
    const DISPLAY_TYPE_RULE_PRODUCTS         = 'rule_products';

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER                = false;

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE         = 5;

    /**
     * Name of request parameter for page number value
     */
    const PAGE_VAR_NAME                     = 'rnp';

    /**
     * Instance of pager block
     *
     * @var Mage_Catalog_Block_Product_Widget_Html_Pager
     */
    protected $_pager;

    /**
     * Default product amount per row
     *
     * @var int
     */
    protected $_defaultColumnCount = 5;

    /**
     * Product Collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection;


    public function __construct($attributes = array()) {
        $this->convertAttributesToConfig($attributes);

        if(!$this->getConfig('show')) {
            return;
        }

        if ($this->hasData("template")) {
            $my_template = $this->getData("template");
        }
        elseif (isset($attributes['template']) && $attributes['template']) {
            $my_template = $attributes['template'];
        }
        elseif (isset($attributes['block_template']) && $attributes['block_template']) {
            $my_template = $attributes['block_template'];
        }
        elseif( $this->getConfig('block_template')) {
            $my_template = $this->getConfig('block_template');
        }
        else {
            $my_template = "ves/productlist/widget/new_grid.phtml";
        }

        $this->setTemplate($my_template);

        parent::__construct($attributes);

        /*Cache Block*/
        $enable_cache = $this->getConfig("enable_cache", 1 );
        if(!$enable_cache) {
          $cache_lifetime = null;
        } else {
          $cache_lifetime = $this->getConfig("cache_lifetime", 86400 );
          $cache_lifetime = (int)$cache_lifetime>0?$cache_lifetime: 86400;
        }

        $this->addData(array('cache_lifetime' => $cache_lifetime));

        $this->addCacheTag(array(
          Mage_Core_Model_Store::CACHE_TAG,
          Mage_Cms_Model_Block::CACHE_TAG,
          Ves_ProductList_Model_Rule::CACHE_WIDGET_NEW_TAG
        ));
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $ruleId = $this->getConfig("rule_id");
        return array(
           'VES_PRODUCTLIST_NEW',
           $this->getNameInLayout(),
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           $ruleId,
           'template' => $this->getTemplate(),
        );
    }

    /**
     * Initialize block's cache and template settings
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');
    }

    /**
     * Retrieve Layer object
     *
     * @return Ves_ProductList_Model_Layer
     */
    public function getLayer() {
        return Mage::getModel('productlist/layer');

    }

    /**
     * Product collection initialize process
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|Object|Varien_Data_Collection
     */
    protected function _getProductCollection()
    {

        if (is_null($this->_productCollection)) {
            $ruleId = (int) $this->getConfig("rule_id", 0);
            $ruleId = (int) $this->getConfig("rule_id", 0);
        
            $rule = Mage::getModel('productlist/rule')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($ruleId);

            $this->setRuleModel($rule);

            $layer = $this->getLayer();
            $layer->setRuleId( $ruleId );
            $layer->setRule( $rule );

            $collection = $layer->getProductCollection( true );
            $this->setCollection($collection);
            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }


    /**
     * Retrieve display type for products
     *
     * @return string
     */
    public function getDisplayType()
    {
        if (!$this->hasData('display_type')) {
            $this->setData('display_type', self::DISPLAY_TYPE_RULE_PRODUCTS);
        }
        return $this->getData('display_type');
    }

    /**
     * Retrieve how much products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->hasData('products_count')) {
            return parent::getProductsCount();
        }
        return $this->getData('products_count');
    }

    /**
     * Retrieve how much products should be displayed
     *
     * @return int
     */
    public function getProductsPerPage()
    {
        if (!$this->hasData('products_per_page')) {
            $product_count = $this->getProductsCount();
            $this->setData('products_per_page', $product_count);
        }
        return $this->getData('products_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {

        //if ($this->showPager()) {
        if (!$this->_pager) {
            $this->_pager = $this->getLayout()
                ->createBlock('productlist/widget_html_pager', 'widget.new.vesproductlist.list.pager');


            $this->_pager->setUseContainer(true)
                ->setShowAmounts(true)
                ->setShowPerPage(false)
                ->setPageVarName(self::PAGE_VAR_NAME)
                ->setLimit($this->getProductsPerPage())
                ->setTotalLimit($this->getProductsCount())
                ->setCollection($this->getProductCollection());
        }
        if ($this->showPager()) {
            if ($this->_pager instanceof Mage_Core_Block_Abstract) {
                return $this->_pager->toHtml();
            }
        }
        //}
        return '';
    }
}
