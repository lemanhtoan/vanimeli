<?php
class NextBits_LoginCatalog_Model_Observer
{
    const ROUTE_PART_MODULE = 0;
    const ROUTE_PART_CONTROLLER = 1;
    const ROUTE_PART_ACTION = 2;

    private $_redirectSetFlag = false;
    private $_disabledRoutes = null;

    public function controllerFrontInitBefore(Varien_Event_Observer $observer)
    {
        if ($this->_shouldRewriteOldNavigationBlock()) {
            Mage::getConfig()->setNode(
                'global/blocks/catalog/rewrite/navigation',
                'NextBits_LoginCatalog_Block_Navigation'
            );
        }
    }
    public function pageBlockHtmlTopmenuGethtmlBefore(Varien_Event_Observer $observer)
    {
        if (Mage::helper('logincatalog')->shouldHideCategoryNavigation()) {
            $menu = $observer->getData('menu');
            foreach ($menu->getChildren() as $key => $node) {
                if (strpos($key, 'category-') === 0) {
                    $menu->removeChild($node);
                }
            }
        }
    }
    public function catalogProductLoadAfter(Varien_Event_Observer $observer)
    {
        $this->_handlePossibleRedirect();
    }
    public function catalogProductCollectionLoadAfter(Varien_Event_Observer $observer)
    {
        $this->_handlePossibleRedirect();
    }
    public function catalogCategoryLoadAfter(Varien_Event_Observer $observer)
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_for_categories')) {
            if ($this->_requestedRouteMatches(array('catalog', 'category', 'view'))) {
                $this->_handlePossibleRedirect();
            }
        }
    }
    public function controllerActionPredispatch(Varien_Event_Observer $args)
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_on_all_pages')) {
            $this->_handlePossibleRedirect();
        }
    }
    private function _handlePossibleRedirect()
    {
        if (!Mage::helper('logincatalog')->moduleActive()) {
            return;
        }
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            return;
        }
        if ($this->_isNotApplicableForRequest()) {
            return;
        }
        $this->_addSpashMessageToSession();
        $this->_setAfterAuthUrl();
        $url = $this->_getRedirectTargetUrl();
        Mage::app()->getResponse()->setRedirect($url);
        $this->_redirectSetFlag = true;
    }
    private function _getRedirectTargetUrl()
    {
        if (Mage::helper('logincatalog')->getConfig('redirect_to_page')) {
            return $this->_getCmsPageRedirectTargetUrl();
        } else {
            return $this->_getLoginPareRedirectTargetUrl();
        }
    }
    private function _isApiRequest()
    {
        return $this->_requestedRouteMatches(array('api'));
    }
    private function _isRedirectDisabledForRoute()
    {
        if (!isset($this->_disabledRoutes)) {
            $this->_initializeListOfDisabledRoutes();
        }
        foreach ($this->_disabledRoutes as $route) {
            if ($this->_requestedRouteMatches($route)) {
                return true;
            }
        }
        return false;
    }
    private function _isLoginPageRequest()
    {
        return
			$this->_requestedRouteMatches(array('wholesale', 'account', 'create')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'login')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'loginPost')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'create')) ||
            $this->_requestedRouteMatches(array('customer', 'account', 'createPost'));
    }
    private function _requestedRouteMatches(array $route)
    {
        switch (count($route)) {
            case 1:
                return $this->_moduleMatches($route);
            case 2:
                return $this->_moduleAndControllerMatches($route);
            case 3:
                return $this->_moduleAndControllerAndActionMatches($route);
            default:
                return false;
        }
    }
    private function _setAfterAuthUrl()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        $currentUrl = Mage::getSingleton('core/url')->sessionUrlVar($currentUrl);
        Mage::getSingleton('customer/session')->setAfterAuthUrl($currentUrl);
    }
    private function _addSpashMessageToSession()
    {
        $message = Mage::helper('logincatalog')->getConfig('message');
        if (mb_strlen($message, 'UTF-8') > 0) {
            Mage::getSingleton('customer/session')->addNotice($message);
        }
    }
    private function _isNotApplicableForRequest()
    {
        return
            $this->_redirectSetFlag ||
            $this->_isLoginPageRequest() ||
            $this->_isApiRequest() ||
            $this->_isRedirectDisabledForRoute();
    }
    private function _moduleMatches(array $route)
    {
        $moduleName = Mage::app()->getRequest()->getModuleName();
        return $moduleName === $route[self::ROUTE_PART_MODULE];
    }
    private function _moduleAndControllerMatches(array $route)
    {
        $controllerName = Mage::app()->getRequest()->getControllerName();
        return $this->_moduleMatches($route) && $controllerName === $route[self::ROUTE_PART_CONTROLLER];
    }
    private function _moduleAndControllerAndActionMatches(array $route)
    {
        $actionName = Mage::app()->getRequest()->getActionName();
        return $this->_moduleAndControllerMatches($route) && $actionName === $route[self::ROUTE_PART_ACTION];
    }
    private function _getCmsPageRedirectTargetUrl()
    {
        $helper = Mage::helper('logincatalog');
        $page = Mage::getModel('cms/page');
        $page->setStoreId(Mage::app()->getStore()->getId())
            ->load($helper->getConfig('cms_page'), 'identifier');
        if (!$page->getId()) {
            $message = $helper->__('Invalid CMS page configured as a redirect landing page.');
            Mage::throwException($message);
        }
        $params = array('_nosid' => true, '_direct' => $page->getIdentifier());
        return Mage::getUrl(null, $params);
    }
    private function _getLoginPareRedirectTargetUrl()
    {
        $route = 'customer/account/login';
        $params = array('_nosid' => true);
        return Mage::getUrl($route, $params);
    }
    private function _shouldRewriteOldNavigationBlock()
    {
        return version_compare(Mage::getVersion(), '1.7', '<') && Mage::helper('logincatalog')->shouldHideCategoryNavigation();
    }
    private function _initializeListOfDisabledRoutes()
    {
        $this->_disabledRoutes = array();
        if ($routes = Mage::helper('logincatalog')->getConfig('disable_on_routes')) {
            foreach (explode("\n", $routes) as $route) {
                $this->_disabledRoutes[] = explode('/', trim($route));
            }
        }
    }
}