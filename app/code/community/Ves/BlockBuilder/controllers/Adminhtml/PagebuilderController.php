<?php 
 /*------------------------------------------------------------------------
  # VenusTheme Block Builder Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_PageBuilder_Install extends Mage_Catalog_Model_Resource_Eav_Mysql4_Setup {
    public function install($file_name = ""){
        if($file_name) {
            $sqldir = Mage::getModuleDir('sql', 'Ves_BlockBuilder');
            $sqldir = $sqldir.DS."ves_blockbuilder_setup".DS;
            include( $sqldir.$file_name );
        }
    }
}
class Ves_BlockBuilder_Adminhtml_PagebuilderController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_blockbuilder/blockbuilder');

        return $this;
    }
    
    /**
     * index action
     */ 
    public function indexAction() {
        //$setup = new  Ves_PageBuilder_Install();
        //$setup->install("mysql4-upgrade-1.1.2-1.1.3.php");
        $this->_title($this->__("Pages Builder"));
        $this->_title($this->__("Manager Page Profiles"));
        
        $this->_initAction();
        $this->renderLayout();
    }

    public function fixCmsPageAction() {
        try {
            $resource = Mage::getSingleton('core/resource');
            /**
                * Retrieve the write connection
                */
            $writeConnection = $resource->getConnection('core_write');
            $cms_page_store_table = $resource->getTableName("cms/page_store");
            $core_store = $resource->getTableName("core/store");
            $writeConnection->query("DELETE FROM `".$cms_page_store_table."` WHERE store_id NOT IN (SELECT store_id FROM `".$core_store."`)");

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('404 not found CMS page was fixed successfully!'));

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured fix 404 not found.'));
        }
    }

   public function sampleAction() {
        $this->_title($this->__('Sample Profiles For Page Builder'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_blockbuilder/adminhtml_pagesample'));

        $this->renderLayout();
        
    }
    public function setupProfile($profile = "") {
        $filepath = Mage::helper("ves_blockbuilder")->getPageProfilePath( $profile );
        
        if ($filepath != null && $filepath) {

            try {
                $stores = Mage::helper("ves_blockbuilder")->getAllStores();
                // import into model
                Mage::getSingleton('ves_blockbuilder/import_block')->process($filepath, $stores);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('CSV Imported Successfully'));

            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured importing CSV.'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } // end if
        }
    }

    public function installsampleAction() {
        $profile = $this->getRequest()->getParam("profile");
        $is_all = $this->getRequest()->getParam("all");
        if($is_all == 1) {
            if($profiles = Mage::helper("ves_blockbuilder")->getPageProfiles()) {
                foreach($profiles as $profile) {
                    $this->setupProfile( $profile );
                }
            }
            
        } else {
            $this->setupProfile( $profile );
        }

        $this->_redirect("*/*/");
        return;
    }
    
    public function editAction()
    {   
        if(!Mage::helper("ves_blockbuilder")->checkModuleInstalled("Ves_Base")) {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ves_blockbuilder")->__("The module required Ves_Base module was installed. Please install and active the module Ves_Base."));
            $this->_redirect("*/*/");
            return;
        }
        $this->_title($this->__("Block Builder"));
        $this->_title($this->__("Block"));
        $this->_title($this->__("Edit Item"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ves_blockbuilder/block")->load($id);

        if ($model->getId()) {
            Mage::register("block_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("ves_blockbuilder/blockbuilder");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pages Manager"), Mage::helper("adminhtml")->__("Pages Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pages Description"), Mage::helper("adminhtml")->__("Pages Description"));
           
            $this->_addContent($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit"))->_addLeft($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tabs"));
            
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            if ($head = $this->getLayout()->getBlock('head')) {
                $head->addItem('js', 'prototype/window.js')
                ->addItem('js_css', 'prototype/windows/themes/default.css')
                ->addCss('lib/prototype/windows/themes/magento.css')
                ->addItem('js', 'mage/adminhtml/variables.js')
                ->addItem('js', 'ves_base/builder/widget.js')
                ->addItem('js', 'lib/flex.js')
                ->addItem('js', 'lib/FABridge.js')
                ->addItem('js', 'mage/adminhtml/flexuploader.js')
                ->addItem('js', 'mage/adminhtml/browser.js');
            }
            $this->renderLayout();
        } else {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ves_blockbuilder")->__("Item does not exist."));
            $this->_redirect("*/*/");
        }
    }

    public function newAction()
    {
        if(!Mage::helper("ves_blockbuilder")->checkModuleInstalled("Ves_Base")) {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ves_blockbuilder")->__("The module required Ves_Base module was installed. Please install and active the module Ves_Base."));
            $this->_redirect("*/*/");
            return;
        }
    
        $this->_title($this->__("Block Builder"));
        $this->_title($this->__("Block"));
        $this->_title($this->__("New Item"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ves_blockbuilder/block")->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        Mage::register("block_data", $model);

        $this->loadLayout();
        $this->_setActiveMenu("ves_blockbuilder/blockbuilder");

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pages Manager"), Mage::helper("adminhtml")->__("Pages Manager"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Pages Description"), Mage::helper("adminhtml")->__("Pages Description"));


        $this->_addContent($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit"))->_addLeft($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_blockbuilder_edit_tabs"));

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addItem('js', 'prototype/window.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css')
            ->addCss('lib/prototype/windows/themes/magento.css')
            ->addItem('js', 'mage/adminhtml/variables.js')
            ->addItem('js', 'ves_base/builder/widget.js')
            ->addItem('js', 'lib/flex.js')
            ->addItem('js', 'lib/FABridge.js')
            ->addItem('js', 'mage/adminhtml/flexuploader.js')
            ->addItem('js', 'mage/adminhtml/browser.js');
        }

        $this->renderLayout();

    }

    public function saveAction()
    {

        $post_data = $this->getRequest()->getPost();

        if ($post_data) {

            try {

                if(isset($post_data['stores'])) {
                    $post_data['store_ids'] = implode(',', $post_data['stores']);
                }
                //Duplicate Block Builder Profile
                if ($this->getRequest()->getParam("duplicate")) {
                    $model_clone = Mage::getModel('ves_blockbuilder/block');
                    $model = Mage::getModel("ves_blockbuilder/block")
                        ->load($this->getRequest()->getParam("id"));

                    $block_id = 0;
                    $block_data = array('shortcode' => $model->getShortcode(),
                                     'params' => $model->getParams(),
                                     'layout_html' => $model->getLayoutHtml(),
                                     'title' => $model->getTitle()."-clone",
                                     'alias' => $model->getAlias()."-clone",
                                     'status' => $model->getStatus(),
                                     'block_type' => $model->getBlockType(),
                                     'container' => $model->getContainer(),
                                     'prefix_class' => $model->getPrefixClass(),
                                     'show_from' => $model->getShowFrom(),
                                     'show_to' => $model->getShowTo(),
                                     'customer_group' => $model->getCustomerGroup(),
                                     'settings' => $model->getSettings(),
                                     'created' => date( 'Y-m-d H:i:s' ),
                                     'stores' => $post_data['stores'],
                                     'position' => $model->getPosition());

                    $model_clone->setData($block_data);

                    try {
                        $model_clone->save();

                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_blockbuilder')->__('Profile was successfully duplicated'));
                        Mage::getSingleton('adminhtml/session')->setFormData(false);

                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        Mage::getSingleton('adminhtml/session')->setFormData($block_data);
                    }
                } else {
                    $settings = array();
                    $post_data['settings'] = array();
                    $post_data['settings']['custom_css'] = isset($post_data['custom_css'])?$post_data['custom_css']:'';
                    $post_data['settings']['custom_js'] = isset($post_data['custom_js'])?$post_data['custom_js']:'';
                    $post_data['settings']['enable_wrapper'] = isset($post_data['enable_wrapper'])?$post_data['enable_wrapper']:'2';
                    $post_data['settings']['wrapper_class'] = isset($post_data['wrapper_class'])?$post_data['wrapper_class']:'';
                    $post_data['settings']['select_wrapper_class'] = isset($post_data['select_wrapper_class'])?$post_data['select_wrapper_class']:'';
                    $post_data['settings']['template'] = isset($post_data['template'])?$post_data['template']:'';

                    $post_data['settings'] = serialize($post_data['settings']);
                    $post_data['block_type'] = isset($post_data['block_type'])?$post_data['block_type']:'page';
                    $post_data['container'] = isset($post_data['container'])?$post_data['container']:'1';
                    $post_data['customer_group'] = isset($post_data['customer_group'])?implode(',', $post_data['customer_group']):"";
                    $post_data['params'] = str_replace(array("<p>","</p>"), "", $post_data['params'] );
                    $post_data['params'] = trim($post_data['params']);
                    
                    $settings['template'] = isset($post_data['template'])?$post_data['template']:'';
                    $settings['code'] = isset($post_data['alias'])?$post_data['alias']:'';
                    $post_data['shortcode'] = Mage::helper("ves_blockbuilder")->getShortCode("ves_blockbuilder/widget_page", $this->getRequest()->getParam("id"), $settings);

                    if($this->getRequest()->getParam("id")) {
                        $post_data['modified'] = date( 'Y-m-d H:i:s' );
                    } else {
                        $post_data['created'] = date( 'Y-m-d H:i:s' );
                    }
                    
                    if ($this->getRequest()->getParam("back")) {

                    }
                    
                    $model = Mage::getModel("ves_blockbuilder/block")
                        ->addData($post_data)
                        ->setId($this->getRequest()->getParam("id"))
                        ->save();

                    if(Mage::getStoreConfig("ves_blockbuilder/ves_blockbuilder/auto_backup_profile")) {
                        Mage::helper("ves_blockbuilder")->autoBackupLayoutProfile( $post_data, "vespagebuilder" );
                    }

                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Profile was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setBlockData(false);

                    /*Create Or Updated CMS Page*/
                    $data = array();
                    $page = Mage::getModel("ves_blockbuilder/block")->loadCMSPage($post_data['alias'], "identifier", $post_data['stores']);
   

                    $data['page_id'] = $page->getPageId();
                    $data['title'] = $post_data['title'];
                    $data['identifier'] = $post_data['alias'];
                    $data['is_active'] = $post_data['status'];
                    $data['stores'] = $post_data['stores'];
                    if(isset($post_data['content_heading'])) {
                        $data['content_heading'] = "";
                    }
                    $shortcode = $model->getShortcode();
                    $shortcode = str_replace(array("<p>","</p>"), "", $shortcode);
                    $data['content'] = $shortcode;
                    $data['root_template'] = $post_data['root_template'];
                    $data['layout_update_xml'] = $post_data['layout_update_xml'];
                    $data['meta_keywords'] = $post_data['meta_keywords'];
                    $data['meta_description'] = $post_data['meta_description'];
                    if(isset($post_data['custom_theme_from'])) {
                        $data['custom_theme_from'] = $post_data['custom_theme_from'];
                    }
                    if(isset($post_data['custom_theme_to'])) {
                        $data['custom_theme_to'] = $post_data['custom_theme_to'];
                    }
                    if(isset($post_data['custom_theme'])) {
                        $data['custom_theme'] = $post_data['custom_theme'];
                    }
                    if(isset($post_data['custom_root_template'])) {
                        $data['custom_root_template'] = $post_data['custom_root_template'];
                    }
                    if(isset($post_data['custom_layout_update_xml'])) {
                        $data['custom_layout_update_xml'] = $post_data['custom_layout_update_xml'];
                    }

                    $data = $this->_filterPostData($data);
                    //init model and set data
                    $page_model = Mage::getModel('cms/page');
                    if ($id = $data['page_id']) {
                        $page_model->load($id);
                    }

                    $page_model->setData($data);

                    Mage::dispatchEvent('cms_page_prepare_save', array('page' => $page_model, 'request' => $this->getRequest()));

                    //validating
                    if (!$this->_validatePostData($data)) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
                        return;
                    }
                    // try to save it
                    try {
                        // save the data
                        $page_model->save();

                        // display success message
                        Mage::getSingleton('adminhtml/session')->addSuccess(
                            Mage::helper('cms')->__('The page has been saved.'));
                    } catch (Mage_Core_Exception $e) {
                        $this->_getSession()->addError($e->getMessage());
                    }
                    catch (Exception $e) {
                        $this->_getSession()->addException($e,
                            Mage::helper('cms')->__('An error occurred while saving the page.'));
                    }
                    /*End Update CMS Page*/
                    
                    if ($this->getRequest()->getParam("back")) {
                        $this->_redirect("*/*/edit", array("id" => $model->getId()));
                        return;
                    }
                }
                
                $this->_redirect("*/*/");
                return;
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setBlockData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }

        }
        $this->_redirect("*/*/");
    }

    
    public function imageAction() {
        $result = array();
        try {
            $uploader = new Venustheme_Brand_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('ves_blockbuilder/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('ves_blockbuilder/config')->getMediaUrl($result['file']);
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

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
    /**
     * Delete
     */
     public function deleteAction() {
     
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('ves_blockbuilder/block');
                 
                $model->setId($this->getRequest()->getParam('id'));
                
                $model->delete();
                     
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This Profile Was Deleted Done'));
                $this->_redirect('*/*/');
            
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    /**
     * Export order grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName = 'pagebuilder_profiles.csv';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blockbuilder_exportpagegrid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'pagebuilder_profiles.xml';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blockbuilder_exportpagegrid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function importCsvAction() {
         // get uploaded file
        $profile = $this->getRequest()->getParam('profile');
        $sub_folder = $this->getRequest()->getParam('subfolder');
        $filepath = Mage::helper("ves_blockbuilder")->getUploadedFile( $profile, true, $sub_folder );
        
        if ($filepath != null) {

            try {
                $stores = Mage::helper("ves_blockbuilder")->getAllStores();
                // import into model
                Mage::getSingleton('ves_blockbuilder/import_block')->process($filepath, $stores);

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('cms')->__('CSV Imported Successfully'));

            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured importing CSV.'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } // end if
        }

        // redirect to grid page.
        $this->_redirect('*/*/index');
    }

    public function uploadCsvAction() {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blockbuilder_upload');
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('ids');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('ves_blockbuilder/block')
                            ->setIsMassStatus(true)
                            ->load($itemId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($IDList))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massDeleteAction() {
        $IDList = $this->getRequest()->getParam('ids');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('ves_blockbuilder/block')
                            ->setIsMassDelete(true)->load($itemId);
                    $_model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($IDList)
                        )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('custom_theme_from', 'custom_theme_to'));
        return $data;
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            /** @var $validatorCustomLayout Mage_Adminhtml_Model_LayoutUpdate_Validator */
            $validatorCustomLayout = Mage::getModel('adminhtml/layoutUpdate_validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml'])
            && !$validatorCustomLayout->isValid($data['custom_layout_update_xml'])) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_getSession()->addError($message);
            }
        }
        return $errorNo;
    }

     /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'new':
            case 'edit':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/pagebuilder/edit');
                break;
            case 'uploadCsv':
            case 'sample':
            case 'installsample':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/pagebuilder/sample');
                break;
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/pagebuilder/save');
                break;
            case 'massDelete':
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/pagebuilder/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/pagebuilder');
                break;
        }
    }
    
}
?>