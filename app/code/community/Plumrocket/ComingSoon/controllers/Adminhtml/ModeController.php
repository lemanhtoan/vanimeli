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


class Plumrocket_ComingSoon_Adminhtml_ModeController extends Mage_Adminhtml_Controller_Action
{

    public function editAction()
    {
        list($scope, $scopeId) = Mage::helper('comingsoon')->getScope();
        $config =  Mage::getModel('comingsoon/config')
            ->setScope($scopeId, $scope, true);
        $data = $config
            //->prepareTime(true, true, false)
            ->loadParams();

        $config->prepareTime($data, 'admin_form');

        Mage::register('comingsoon_config_default', $data);

    	$this->loadLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        }
		$this->renderLayout();
    }

    public function saveAction()
    {
        if(!is_array($params = $this->getRequest()->getParams())) {
            $params = array();
        }

        $storeId = !empty($params['store'])? $params['store'] : 0;
        if($storeId && !Mage::app()->getStore($storeId)) {
            $storeId = 0;
        }

        $forms = array();
        $layout = $this->getLayout();
        foreach(array('general', 'comingsoon', 'maintenance') as $key) {
            $tab = $layout->createBlock('comingsoon/adminhtml_mode_edit_tabs_'.$key);
            $tab->toHtml();
            $forms[$key] = $tab->getForm();
        }

        // Remove files.
        $filesFields = array(
            'comingsoon_background_image',
            'comingsoon_background_video',
            'maintenance_background_image',
            'maintenance_background_video',
        );

        foreach ($filesFields as $_field) {
            if(!empty($params[$_field]) && is_array($params[$_field]) && empty($params[$_field]['inherit'])) {

                foreach ($params[$_field] as $key => &$file) {
                    if (isset($file['exclude'])) {
                        $file['exclude'] = 1;
                    }

                    if(!empty($file['remove']) || $key == '_TMPNAME_' || (isset($file['url']) && empty($file['url']))) {
                        // ..remove file
                        /*if(false !== strrpos($_field, '_image')) {
                            $filePath = Mage::getBaseDir('media') . DS .'comingsoon'. DS . $key;
                            if(file_exists($filePath)) {
                                $io = new Varien_Io_File();
                                $io->rm($filePath);
                            }
                        }*/
                        unset($params[$_field][$key]);
                        continue;
                    }
                    unset($file['remove']);
                }
            }else{
                unset($params[$_field]);
            }
        }

        foreach ($params as $key => &$value) {
            $inForm = false;
            foreach($forms as $form) {
                $element = $form->getElement($key);
                if ($element && $element->getType() != 'hidden') {
                    $inForm = true;
                    break;
                }
            }

            if(!$inForm) {
                unset($params[$key]);
                continue;
            }

        }

        list($scope, $scopeId) = Mage::helper('comingsoon')->getScope();
        Mage::getModel('comingsoon/config')
            ->setScope($scopeId, $scope, true)
            //->prepareTime(true, true, true)
            ->saveParams($params);
        
        $this->_redirectReferer();
    }

    public function uploadAction()
    {   
        $type = 'file';
        $tmpPath = Mage::getBaseDir('media'). DS .'comingsoon';
        $result = array();
        try {
            $uploader = new Mage_Core_Model_File_Uploader($type);
            $uploader->setAllowRenameFiles(true);
            // $uploader->setFilesDispersion(true);
            $result = $uploader->save($tmpPath);

            /**
             * Workaround for prototype 1.7 methods "isJSON", "evalJSON" on Windows OS
             */
            $result['tmp_name'] = str_replace(DS, "/", $result['tmp_name']);
            $result['path'] = str_replace(DS, "/", $result['path']);

            if (isset($result['file'])) {
                $fullPath = rtrim($tmpPath, DS) . DS . ltrim($result['file'], DS);
                Mage::helper('core/file_storage_database')->saveFile($fullPath);
            }

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function previewAction()
    {
        $helper = Mage::helper('comingsoon');
        list($scope, $scopeId) = $helper->getScope();

        if($scope == 'stores') {
            if($store = Mage::app()->getStore($scopeId)) {
                $storeId = $store->getId();
            }
        }elseif($scope == 'websites') {
            if($website = Mage::app()->getWebsite($scopeId)) {
                $storeId = $website->getDefaultGroup()->getDefaultStoreId();
            }
        }

        if(empty($storeId)) {
            $storeId = Mage::app()
                ->getWebsite(true)
                ->getDefaultGroup()
                ->getDefaultStoreId();
        }

        $url = Mage::getUrl("comingsoon/index/preview", array(
            'mode'      => $this->getRequest()->getParam('mode'),
            'action'    => md5($helper->getCustomerDate()),
            '_store'    => $storeId,
        ));
        $helper->redirect($url);
    }
	
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('plumrocket/comingsoon');
    }
    
}