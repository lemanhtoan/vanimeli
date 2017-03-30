<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_ComingSoon_Model_Observer
{

    public function controllerActionPredispatch($observer)
    {
        $helper = Mage::helper('comingsoon');
        if(!$helper->moduleEnabled()) {
            return;
        }

        $config = Mage::helper('comingsoon/config');
        $request = Mage::app()->getRequest();
        $currentTime = Mage::getModel('core/date')->gmtTimestamp();

        $currentPageMode = 'live';
        if($request->getModuleName() == 'comingsoon') {
            if($request->getActionName() == 'preview') {
                return;
            }elseif(in_array($request->getActionName(), array('comingsoon', 'register'))) {
                $currentPageMode = 'comingsoon';
            }elseif (in_array($request->getActionName(), array('maintenance'))) {
                $currentPageMode = 'maintenance';
            }
        }

        // Preview modes.
        $preview = Mage::getSingleton('core/session')->getData(Plumrocket_ComingSoon_Helper_Data::PREVIEW_PARAM_NAME);
        if(!empty($preview[$currentPageMode]) || Mage::getSingleton('plumbase/observer')->customer() != Mage::getSingleton('plumbase/product')->currentCustomer()) {
            return;
        }

        // Check Ip.
        $ip = Mage::helper('core/http')->getRemoteAddr();
        if(in_array($ip, $config->getIpWhitelist())) {
            return;
        }
        
        // Stop if call to api.
        if ($request->getModuleName() == 'api') {
            return;
        }
        
        switch($config->getComingsoonMode()) {
            case 'comingsoon':
                $launchAction = $config->getComingsoonLaunchAction();
                $launchTime = $config->getComingsoonLaunchTime();

                if($launchAction == 'live' &&  $currentTime >= $launchTime) {
                    return;
                }

                $allow = false;

                $allowPages = $config->getComingsoonRestrictionsAccessPages();
                if($config->getComingsoonRestrictionsAccessAllow()) {
                    if($request->getModuleName() == 'cms' && $request->getControllerName() == 'page' && $request->getActionName() == 'view'
                        && in_array(trim($request->getPathInfo(), '/'), $allowPages)) {
                        $allow = true;
                    }elseif($request->getModuleName() == 'cms' && $request->getControllerName() == 'index' && $request->getActionName() == 'index'
                        && in_array('home', $allowPages)) {
                        $allow = true;
                    }
                }

                if($currentPageMode != 'comingsoon' && !$allow ) {
                    // Redirect to home page.
                    $baseUrl = Mage::getBaseUrl();

                    if($request->getModuleName() != 'cms' || $request->getControllerName() != 'index' || $request->getActionName() != 'index') {
                        if ($baseUrl != Mage::helper('core/url')->getCurrentUrl()) { //fix for FPC Cache
                            $helper->redirect($baseUrl);
                            return;
                        }
                    }

                    $this->_forward('comingsoon', 'index', 'comingsoon');
                }
                break;

            case 'maintenance':
                $launchAction = $config->getMaintenanceLaunchAction();
                $launchTime = $config->getMaintenanceLaunchTime();

                if($launchAction == 'live' &&  $currentTime >= $launchTime) {
                    return;
                }

                if($currentPageMode != 'maintenance') {
                    $this->_forward('maintenance', 'index', 'comingsoon');
                }
                break;

            case 'live':
            default:
                return;
        }

    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        $request = Mage::app()->getRequest();

        $request->initForward();

        if (isset($params)) {
            $request->setParams($params);
        }

        if (isset($controller)) {
            $request->setControllerName($controller);

            // Module should only be reset if controller has been specified
            if (isset($module)) {
                $request->setModuleName($module);
            }
        }

        $request->setActionName($action)
            ->setDispatched(false);
    }

    public function customerPostDispach(Varien_Event_Observer $observer)
    {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            && Mage::helper('comingsoon')->moduleEnabled()
            && (Mage::registry('plumrocket_allow_clear_ajax_response') !== true)
            && (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer())
        ) {
            $front = $observer->getEvent()->getControllerAction();
            $front->loadLayout();
            
            $block = $front->getLayout()->getMessagesBlock();
            foreach (array('customer/session', 'catalog/session') as $storageName) {
                $storage = Mage::getSingleton($storageName);
                if ($storage) {
                    $block->addMessages($storage->getMessages(true));
                    $block->setEscapeMessageFlag($storage->getEscapeMessages(true));
                    $block->addStorageType($storageName);
                }
            }
            Mage::register('plumrocket_allow_clear_ajax_response', true);

            ob_clean();
            $front->getResponse()
                ->clearHeader('Location')
                ->clearRawHeader('Location')
                ->setHttpResponseCode(200)
                ->setBody($block->getGroupedHtml());
        } else {
            return $observer;
        }
    }

}