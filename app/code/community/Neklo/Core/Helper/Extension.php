<?php
/*
NOTICE OF LICENSE

This source file is subject to the NekloEULA that is bundled with this package in the file ICENSE.txt.

It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt

Copyright (c)  Neklo (http://store.neklo.com/)
*/


class Neklo_Core_Helper_Extension extends Mage_Core_Helper_Abstract
{
    protected $_protectedModuleCodeList = array(
        'Neklo_Core'
    );

    protected $_cacheKey = null;
    protected $_moduleConfigList = null;
    protected $_moduleList = null;

    public function getModuleList()
    {
        if (is_null($this->_moduleList)) {
            $moduleList = array();
            foreach ($this->getModuleConfigList() as $moduleCode => $moduleConfig) {
                $moduleList[$moduleCode] = array(
                    'name'    => $moduleConfig->extension_name ? $moduleConfig->extension_name : $moduleCode,
                    'version' => $moduleConfig->version,
                );
            }
            $this->_moduleList = $moduleList;
        }
        return $this->_moduleList;
    }

    public function getModuleConfigList()
    {
        if (is_null($this->_moduleConfigList)) {
            $moduleConfigList = (array)Mage::getConfig()->getNode('modules')->children();
            ksort($moduleConfigList);
            $moduleList = array();
            foreach ($moduleConfigList as $moduleCode => $moduleConfig) {
                if (!$this->_canShowExtension($moduleCode, $moduleConfig)) {
                    continue;
                }
                $moduleList[$moduleCode] = $moduleConfig;
            }
            $this->_moduleConfigList = $moduleList;
        }
        return $this->_moduleConfigList;
    }

    public function getCacheKey()
    {
        if (is_null($this->_cacheKey)) {
            $cacheList = array();
            foreach ($this->getModuleConfigList() as $moduleCode => $moduleConfig) {
                $version = explode('.', $moduleConfig->version);
                $version = (intval($version[0]) - 1) << 12 | intval($version[1]) << 6 | intval($version[2]) << 0;
                $cacheList[] = dechex(intval($moduleConfig->build)) . 't' . dechex(intval($moduleConfig->build) - hexdec($moduleConfig->encoding)) . 't' . substr(md5(strtolower($moduleCode)), 0, 2) . $version;
            }
            $this->_cacheKey = implode('n', $cacheList);
        }
        return $this->_cacheKey;
    }

    /**
     * @param string $code
     * @param Mage_Core_Model_Config_Element $config
     *
     * @return bool
     */
    protected function _canShowExtension($code, $config)
    {
        if (!$code || !$config) {
            return false;
        }
        if (!($config instanceof Mage_Core_Model_Config_Element)) {
            return false;
        }
        if (!is_object($config) || !method_exists($config, 'is') || !$config->is('active', 'true')) {
            return false;
        }
        if (!$this->_isNekloExtension($code)) {
            return false;
        }
        if ($this->_isProtectedExtension($code)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    protected function _isNekloExtension($code)
    {
        return (strstr($code,'Neklo_') !== false);
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    protected function _isProtectedExtension($code)
    {
        return in_array($code, $this->_protectedModuleCodeList);
    }
}