<?php

class Magestore_Affiliateplus_Helper_Url extends Mage_Core_Helper_Abstract {

    //hainh 29-07-2014
    public function getPersonalUrlParameter() {
        $paramArray = explode(',', Mage::getStoreConfig('affiliateplus/refer/url_param_array'));
        $referParam = $paramArray[count($paramArray) - 1];
        if (!$referParam && ($referParam == ''))
            $referParam = 'acc';
        return $referParam;
    }

    //end editting

    public function getBannerUrl($banner, $store = null) {
        if (is_null($store))
            $store = Mage::app()->getStore();
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();

        $url = $this->getUrlLink($banner->getLink());

        //hainh 29-07-2014 
        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if (Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2)
            $referParamValue = $account->getAccountId();

        if (strpos($url, '?'))
            $url .= '&' . $referParam . '=' . $referParamValue;
        else
            $url .= '?' . $referParam . '=' . $referParamValue;
        //end editing

        // Changed By Adam: 10/11/2014: Fix loi khi chay multiple website nhung ko co default store view
        if (Mage::app()->getDefaultStoreView() && $store->getId() != Mage::app()->getDefaultStoreView()->getId())
            $url .= '&___store=' . $store->getCode();
        /** Thanhpv - add bannerid (2012-10-09) */
        if ($banner->getId())
            $url .= '&bannerid=' . $banner->getId();

        $urlParams = new Varien_Object(array(
            'helper' => $this,
            'params' => array(),
        ));
        Mage::dispatchEvent('affiliateplus_helper_get_banner_url', array(
            'banner' => $banner,
            'url_params' => $urlParams,
        ));

        $params = $urlParams->getParams();
        if (count($params))
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');

        return $url;
    }

    /**
     * get Full link URL
     *
     * @param string $url
     * @return string
     */
    public function getUrlLink($url) {
        if (!preg_match("/^http\:\/\/|https\:\/\//", $url))
            return Mage::getUrl() . trim($url, '/');
        return rtrim($url, '/');
    }

    /**
     * add account param to link
     *
     * @param string $url
     * @param Mage_Core_Model_Store $store
     * @return string
     */
    public function addAccToUrl($url, $store = null) {
        if (is_null($store))
            $store = Mage::app()->getStore();
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();

        $url = $this->getUrlLink($url);

        //hainh 29-07-2014
        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if (Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2)
            $referParamValue = $account->getAccountId();
        if (strpos($url, '?'))
            $url .= '&' . $referParam . '=' . $referParamValue;
        else
            $url .= '?' . $referParam . '=' . $referParamValue;

        //end editing
        
        // Changed By Adam: 10/11/2014: Fix loi khi chay multiple website nhung ko co default store view
        if (Mage::app()->getDefaultStoreView() && $store->getId() != Mage::app()->getDefaultStoreView()->getId())
            $url .= '&___store=' . $store->getCode();

        $urlParams = new Varien_Object(array(
            'helper' => $this,
            'params' => array(),
        ));
        Mage::dispatchEvent('affiliateplus_helper_add_acc_to_url', array(
            // 'banner'	=> $banner,
            'url_params' => $urlParams,
        ));
        $params = $urlParams->getParams();
        if (count($params))
            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');

        return $url;
    }

    /**
     * @author  Richard
     * @update  Adam (27/08/2016): generate url to sub store
     * @param $url
     * @param null $store
     * @return string
     */
    public function addAccToSubstore($url, $store = null) {
        if (is_null($store))
            $store = Mage::app()->getStore();
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();

        $url = $this->getUrlLink($url);

        //hainh 29-07-2014
        $referParam = $this->getPersonalUrlParameter();
        $referParamValue = $account->getIdentifyCode();
        if (Mage::getStoreConfig('affiliateplus/general/url_param_value') == 2)
            $referParamValue = $account->getAccountId();
        $substore = $account->getKeyShop();
        if (strpos($url, '?'))
            $url .= '/'.$substore;
        else
            $url .= '/'.$substore;

        if (Mage::app()->getDefaultStoreView() && $store->getId() != Mage::app()->getDefaultStoreView()->getId())
            $url .= '&___store=' . $store->getCode();

        $urlParams = new Varien_Object(array(
            'helper' => $this,
            'params' => array(),
        ));
        Mage::dispatchEvent('affiliateplus_helper_add_acc_to_url', array(
            'url_params' => $urlParams,
        ));
        $params = $urlParams->getParams();

        if (count($params))

            $url .= '&' . http_build_query($urlParams->getParams(), '', '&');
        return $url;
    }

}
