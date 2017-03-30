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
class Ves_BlockBuilder_Adminhtml_BlockbuilderController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_blockbuilder/blockbuilder');

        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {

        $this->_title($this->__("Blocks Builder"));
        $this->_title($this->__("Manager Blocks"));

        $this->_initAction();
        $this->renderLayout();

    }

    public function setupProfile($profile = "") {
        $filepath = Mage::helper("ves_blockbuilder")->getBlockProfilePath( $profile );

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
            if($profiles = Mage::helper("ves_blockbuilder")->getBlockProfiles()) {
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

    public function sampleAction() {
        $this->_title($this->__('Sample Profiles For Block Builder'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blocksample'));

        $this->renderLayout();

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
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Blocks Manager"), Mage::helper("adminhtml")->__("Blocks Manager"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Blocks Description"), Mage::helper("adminhtml")->__("Blocks Description"));
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
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

        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Blocks Manager"), Mage::helper("adminhtml")->__("Blocks Manager"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Blocks Description"), Mage::helper("adminhtml")->__("Blocks Description"));


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

                if(isset($post_data['store_ids'])) {
                    $post_data['store_ids'] = implode(',', $post_data['store_ids']);
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
                                     'created' => date( 'Y-m-d H:i:s' ),
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
                    $post_data['block_type'] = isset($post_data['block_type'])?$post_data['block_type']:'block';
                    $post_data['container'] = isset($post_data['container'])?$post_data['container']:'1';
                    $post_data['customer_group'] = implode(',', $post_data['customer_group']);
                    $post_data['params'] = str_replace(array("<p>","</p>"), "", $post_data['params'] );
                    $post_data['params'] = trim($post_data['params']);

                    $settings['template'] = isset($post_data['template'])?$post_data['template']:'';
                    $settings['code'] = isset($post_data['alias'])?$post_data['alias']:'';
                    $post_data['shortcode'] = Mage::helper("ves_blockbuilder")->getShortCode("ves_blockbuilder/widget_builder", $this->getRequest()->getParam("id"), $settings);

                    if($this->getRequest()->getParam("id")) {
                        $post_data['modified'] = date( 'Y-m-d H:i:s' );
                    } else {
                        $post_data['created'] = date( 'Y-m-d H:i:s' );
                    }

                    if ($this->getRequest()->getParam("back")) {

                    }
                    $model = Mage::getModel("ves_blockbuilder/block");
                    $model->addData($post_data)
                        ->setId($this->getRequest()->getParam("id"))
                        ->save();

                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Profile was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setFormData(false);

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

                $model->load($this->getRequest()->getParam('id'));

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
        $fileName = 'blockbuilder_profiles.csv';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blockbuilder_exportgrid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'blockbuilder_profiles.xml';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_blockbuilder_exportgrid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
    }

    public function importCsvAction() {
         // get uploaded file
        $profile = $this->getRequest()->getParam('profile');
        $sub_folder = $this->getRequest()->getParam('subfolder');

        $filepath = Mage::helper("ves_blockbuilder")->getUploadedFile( $profile, false, $sub_folder );

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
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/blockbuilder/edit');
                break;
            case 'uploadCsv':
            case 'sample':
            case 'installsample':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/blockbuilder/sample');
                break;
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/blockbuilder/save');
                break;
            case 'massDelete':
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/blockbuilder/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/blockbuilder');
                break;
        }
    }

}
?>