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


class Plumrocket_ComingSoon_Helper_Data extends Plumrocket_ComingSoon_Helper_Main
{
    const PREVIEW_PARAM_NAME    = 'comingsoon_preview';
    const YOUTUBE_IMAGE_MEDIUM  = 'http://img.youtube.com/vi/_VIDEO_ID_/mqdefault.jpg';
    const YOUTUBE_IMAGE_BIG     = 'http://img.youtube.com/vi/_VIDEO_ID_/sddefault.jpg';
    const YOUTUBE_IMAGE_MAX     = 'http://img.youtube.com/vi/_VIDEO_ID_/maxresdefault.jpg';

    protected static $_underscoreCache = array();
    protected $_mailchimp = false;

    public function underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    public function getPageUrlByIdentifier($identifier)
    {
        return Mage::getUrl(null, array('_direct' => $identifier));
    }

    public function getStoreId()
    {
        // return Mage::app()->getRequest()->getParam('store');
        list($scope, $scopeId) = $this->getScope();
        if($scope == 'stores') {
            return $scopeId;
        }
    }

    public function getScope()
    {
        $request = Mage::app()->getRequest();
        if($storeName = $request->getParam('store')) {
            if($store = Mage::app()->getStore($storeName)) {
                $scope = array('stores', $store->getId());
            }
        }elseif($websiteName = $request->getParam('website')) {
            if($website = Mage::app()->getWebsite($websiteName)) {
                $scope = array('websites', $website->getId());
            }
        }

        if(empty($scope)) {
            $scope = array('default', 0);
        }

        return $scope;
    }

    public function getSignupFields($data = array())
    {
        $_fields = array(
            'firstname'         => $this->__('First Name'),
            'middlename'        => $this->__('Middle Name'),
            'lastname'          => $this->__('Last Name'),
            'email'             => $this->__('Email'),
            'confirm_email'     => $this->__('Confirm Email'),
            'password'          => $this->__('Password'),
            'confirm_password'  => $this->__('Confirm Password'),
            'dob'               => $this->__('Date of Birth'),
            'gender'            => $this->__('Gender'),
            'prefix'            => $this->__('Prefix'),
            'suffix'            => $this->__('Suffix'),
            'taxvat'            => $this->__('Tax/VAT Number'),
            
            // address
            'telephone'         => $this->__('Telephone'),
            'fax'               => $this->__('Fax'),
            'company'           => $this->__('Company'),
            'street'            => $this->__('Street Address'),
            'city'              => $this->__('City'),
            'country_id'        => $this->__('Country'),
            'region'            => $this->__('State/Province'),
            //'region_id'           => 'State/Province',
            'postcode'          => $this->__('Zip/Postal Code'),
        );

        if($data && is_array($data)) {
            if(isset($data['value']) && is_array($data['value'])) {
                $data = $data['value'];
            }
        }

        $fields = array();
        $s = 1;
        foreach ($_fields as $name => $label) {
            $fields[$name] = array(
                'name'      => $name,
                'orig_label'=> $label,
                'label'     => (isset($data[$name]['label']) ? $data[$name]['label'] : $label),
                'sort_order'=> (isset($data[$name]['sort_order']) ? intval($data[$name]['sort_order']) : $s * 10),
            );

            if(isset($data[$name]['enable'])) {
                $fields[$name]['enable'] = 1;
            }
            
            $s++;
        }

        $fields['email']['enable'] = 1;

        uasort($fields, create_function('$a, $b', 'return ($a["sort_order"] <= $b["sort_order"])? -1 : 1;'));

        return $fields;
    }

    public function moduleEnabled($store = null)
    {
        return (bool)Mage::getStoreConfig('comingsoon/general/enable', $store);
    }


    public function filterByActive($fields)
    {
        if(is_array($fields)) {
           $fields = array_filter($fields, array('self', '_filterByActive'));
        }
        return $fields;
    }

    protected function _filterByActive($field)
    {
        $now = Mage::getModel('core/date')->gmtTimestamp();
        if(!empty($field['date_from']) && $field['date_from'] > $now) {
            return false;
        }

        if(!empty($field['date_to']) && $field['date_to'] < $now) {
            return false;
        }

        if(!empty($field['exclude'])) {
            return false;
        }

        return true;
    }

    public function disableExtension()
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');
        $connection->delete($resource->getTableName('core/config_data'), array($connection->quoteInto('path IN (?)', array('comingsoon/general/enable', 'comingsoon/mailchimp/enable'))));
        $config = Mage::getConfig();
        $config->reinit();
        Mage::app()->reinitStores();
    }

    public function getRandomField($fields, $col = null, $type = 'image')
    {
        if(is_array($fields)) {
            $session = Mage::getSingleton('core/session');
            if(count($fields) > 1 && $prev = $session->getData("comingsoon_prev_{$type}")) {
                unset($fields[$prev]);
                $session->unsetData("comingsoon_prev_{$type}");
            }
            
            $field = array_rand($fields);
            if(isset($fields[$field])) {
                $session->setData("comingsoon_prev_{$type}", $field);
                if(!is_null($col) && isset($fields[$field][$col])) {
                    return $fields[$field][$col];
                }
                return $fields[$field];
            }
        }
    }

    public function getYoutubeId($url)
    {
        $id = null;
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
            $id = $match[1];
        }
        return $id;
    }

    public function getYoutubeImageUrl($id, $size = null)
    {
        if(!$id = is_numeric($id)? $id : $this->getYoutubeId($id)) {
            return;
        }

        $url = self::YOUTUBE_IMAGE_MEDIUM;
        if($size == 'big') {
            $url = self::YOUTUBE_IMAGE_BIG;
        }

        return str_replace('_VIDEO_ID_', $id, $url);
    }

    public function getVimeoId($url)
    {
        $id = null;
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:[a-z]*\/)*([0-9]{6,11})[?]?.*/', $url, $match)) {
            $id = $match[1];
        }
        return $id;
    }

    public function getVimeoImageUrl($id, $size = null) {
        if(!$id = is_numeric($id)? $id : $this->getVimeoId($id)) {
            return;
        }

        $get = "http://vimeo.com/api/v2/video/$id.json";
        if(function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $get);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($curl);
            curl_close($curl);
        }else{
            $data = @file_get_contents($get);
        }
        $data = json_decode($data);
        
        $url = '';
        if(!empty($data[0]->thumbnail_medium)) {
            $url = $data[0]->thumbnail_medium;
        }
        if($size == 'big' && !empty($data[0]->thumbnail_large)) {
            $url = $data[0]->thumbnail_large;
        }

        return $url;
    }

    public function isYoutubeOrVimeo($url)
    {
        if (preg_match('/\b(?:vimeo|youtube)\.com\b/i', $url)) {
           return true;
        }
    }

    public function getCustomerDate()
    {
        return 'Date: '. date('Y-m-d') . 'Customer: '. $this->getCustomerKey()  . sha1((string)Mage::getConfig()->getNode('global/crypt/key'));
    }

    public function getMcapi()
    {
        $config = Mage::helper('comingsoon/config');
        if (!$this->_mailchimp) {
            if ($config->enabledMailchimp()) {
                $this->_mailchimp = new Plumrocket_ComingSoon_Model_Mcapi(
                    $config->getMailchimpKey(),
                    true
                );
            }
        }
        return $this->_mailchimp;
    }
    
    public function getAddressFieldsCodes()
    {
        return array('telephone', 'fax', 'company', 'street', 'city', 'country_id', 'region', 'region_id', 'postcode',);
    }

    public function redirect($url)
    {
        Mage::app()->getResponse()->setRedirect($url)->sendHeaders();
        exit;
    }

    public function getDateTimeFormat()
    {
        // return 'M/d/yyyy hh:mm a';
        return 'M/d/yyyy H:mm';
    }

    public function getDateTimeInternal($dateTime)
    {
        list($date, $time) = explode(' ', trim($dateTime), 2);
        list($M, $d, $yyyy) = explode('/', $date, 3);

        return $yyyy . '-' . $M . '-' . $d . ' ' . $time . ':00';
    }

    public function addFormValues($form, $values) {
        if (!is_array($values)) {
            return;
        }

        foreach ($values as $elementId => $value) {
            if ($element = $form->getElement($elementId)) {
                $_value = $value;
                $_inherit = true;
                if (is_array($value)) {
                    $_value = isset($value['value'])? $value['value'] : $value;
                    $_inherit = isset($value['inherit'])? (bool)$value['inherit'] : $_inherit;
                }
                
                $element
                    ->setValue($_value)
                    ->setInherit($_inherit);
            }
        }
    }

    public function getBackgroundImages($data = array())
    {
        $rows = array(
            '_TMPNAME_' => array('label' => ''),
        );

        if($data && is_array($data)) {
            if(isset($data['value'])) {
                if(is_array($data['value'])) {
                    $rows = array_merge($rows, $data['value']);
                }
            }else{
                $rows = array_merge($rows, $data);
            }
        }
        
        foreach ($rows as $name => &$row) {
            $row = array_merge($row, array(
                'name'      => $name,
                'exclude'   => empty($row['exclude']) ? 0 : 1,
                'remove'    => '1',
            ));
        }

        return $rows;
    }

    public function getBackgroundVideos($data = array())
    {
        $rows = array(
            '_TMPNAME_' => array('url' => ''),
        );

        if($data && is_array($data)) {
            if(isset($data['value'])) {
                if(is_array($data['value'])) {
                    $rows = array_merge($rows, $data['value']);
                }
            }else{
                $rows = array_merge($rows, $data);
            }
        }
        
        foreach ($rows as $name => &$row) {
            $row = array_merge($row, array(
                'name'      => $name,
                'exclude'   => empty($row['exclude']) ? 0 : 1,
                'remove'    => '1',
            ));
        }

        return $rows;
    }

    public function getWysiwygConfig()
    {
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
        $config['directives_url'] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive');
        if (isset($config['files_browser_window_url'])) {
            $config['files_browser_window_url'] = Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index');
        }
        return $config;
    }

    public function getFormElementsValues()
    {
        $values = array();
        $layout = Mage::app()->getLayout();
        foreach(array('general', 'comingsoon', 'maintenance') as $key) {
            $tab = $layout->createBlock('comingsoon/adminhtml_mode_edit_tabs_'.$key);
            $tab->toHtml();
            $form = $tab->getForm();

            foreach ($form->getElements() as $fieldset) {
                foreach ($fieldset->getElements() as $element) {
                    $values[$element->getId()] = $element->getValue();
                }
            }
        }

        return $values;
    }
    
}