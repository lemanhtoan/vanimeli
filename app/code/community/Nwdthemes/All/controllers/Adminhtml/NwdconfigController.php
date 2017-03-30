<?php

/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_All_Adminhtml_NwdconfigController extends Mage_Adminhtml_Controller_Action {

	protected $_nwdHelper;

	protected function _construct()
	{
		$this->_nwdHelper = Mage::helper('nwdall');
	}

	/**
	 * Check permissions
	 */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('nwdthemes/config');
    }

	/**
	 * Init action
	 */
	protected function _initAction() {
		return $this;
	}

	/**
	 * Init page
	 */
	protected function _initPage() {

		$this->_initAction()
			->loadLayout()
			->_setActiveMenu('nwdthemes/config')
			->_addBreadcrumb( $this->_nwdHelper->__('NWDthemes'), $this->_nwdHelper->__('Save / Load Configuration') );

		$this->getLayout()
			->getBlock('head')
			->setTitle( $this->_nwdHelper->__('NWDthemes > Save / Load Configuration') );
			
		return $this;
	}

    /**
     * Validate Form Key
     *
     * @return bool
     */
    protected function _validateFormKey()
    {
        if (!($formKey = $this->getRequest()->getParam('form_key', null))
            || $formKey != Mage::getSingleton('core/session')->getFormKey()) {
            return false;
        }
        return true;
    }

	public function indexAction() {
		$this->_initPage();
		$this->renderLayout();
	}

    protected function _processItems($items, $store)
    {
        $result = array();
        $modules = $this->_nwdHelper->getInstalledModules();
        foreach ($items as $_item) {
            if ( !in_array($_item, $modules) ) continue;
            $sections = $this->_nwdHelper->getExtConfigSectionName($_item);
            foreach ( $sections as $s ) {
                $result[$_item] = array( $s => Mage::getStoreConfig($s, $store));
            }
        }
        return $result;
    }

    public function saveAction() {

        if (!$this->_validateFormKey()) {
            Mage::getSingleton('adminhtml/session')->addError( $this->_nwdHelper->__('Form key validation failed') );
            $this->_redirect('*/*/');
            return;
        }

        if ($data = $this->getRequest()->getPost()) {

            try {

                $filename = preg_replace("([^a-zA-Z0-9_])", '', $data['filename']);
                if ( empty($filename) ) $filename = 'nwd_settings';

                $result = array();
                if ( !empty($data['Extensions']) )
                    $result['Extensions'] = $this->_processItems($data['Extensions'], $data['store']);
                if ( !empty($data['Themes']) )
                    $result['Themes'] = $this->_processItems($data['Themes'], $data['store']);

                if ( empty($result) ) Mage::throwException('Please select extension(s) / theme(s)');

                $settings = base64_encode(json_encode($result));
                header('Content-Type: application/text');
                header('Content-Length: ' . strlen($settings));
                header('Content-Disposition: attachment; filename="'.$filename.'.cfg"');
                echo $settings;
                exit();

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->_nwdHelper->__(
                    sprintf('Save Configuration ERROR: %s', $e->getMessage())
                ));
            }

        }
        $this->_redirect('*/*/');
    }

    public function loadFormAction() {
        $importBlock = $this->getLayout()->createBlock('nwdall/adminhtml_load');
        $this->getResponse()->setBody($importBlock->toHtml());
    }

    public function loadAction() {
        $data = $this->getRequest()->getPost();
        $_file = 'config';
        if(isset($_FILES[$_file]['name']) and (file_exists($_FILES[$_file]['tmp_name'])))
        {
            try {
	            $msg = $this->_nwdHelper->loadConfigFromFile($_FILES[$_file]['tmp_name'], $data['store']);
                Mage::getSingleton('adminhtml/session')->addSuccess( $this->_nwdHelper->__($msg) );
                $this->_redirect('*/*/');
                return;
            }
            catch(Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError( $this->_nwdHelper->__('No file selected') );
        $this->_redirect('*/*/');
    }
}
