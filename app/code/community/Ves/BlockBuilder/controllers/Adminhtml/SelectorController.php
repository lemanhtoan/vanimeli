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
class Ves_BlockBuilder_Adminhtml_SelectorController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_blockbuilder/livecss');

        return $this;
    }
    
    /**
     * index action
     */ 
    public function indexAction() {
        
        $this->_title($this->__("Css Selector Elements"));
        $this->_title($this->__("Manager Css Selector Elements"));
        
        $this->_initAction();
        $this->renderLayout();
    }

    public function editAction()
    {   
        if(!Mage::helper("ves_blockbuilder")->checkModuleInstalled("Ves_Base")) {
            Mage::getSingleton("adminhtml/session")->addError(Mage::helper("ves_blockbuilder")->__("The module required Ves_Base module was installed. Please install and active the module Ves_Base."));
            $this->_redirect("*/*/");
            return;
        }
        $this->_title($this->__("Css Selector"));
        $this->_title($this->__("Selector"));
        $this->_title($this->__("Edit Item"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ves_blockbuilder/selector")->load($id);

        if ($model->getId()) {
            Mage::register("selector_data", $model);
            $this->loadLayout();
            $this->_setActiveMenu("ves_blockbuilder/blockbuilder");
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Css Selector Elements"), Mage::helper("adminhtml")->__("Manage Css Selector Elements"));
            $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Css Selector Elements"), Mage::helper("adminhtml")->__("Manage Css Selector Elements"));
           
            $this->_addContent($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_selector_edit"))->_addLeft($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_selector_edit_tabs"));
            
            $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
            if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
                $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
            }
            if ($head = $this->getLayout()->getBlock('head')) {
                $head->addItem('js', 'prototype/window.js')
                ->addItem('js_css', 'prototype/windows/themes/default.css')
                ->addCss('lib/prototype/windows/themes/magento.css')
                ->addItem('js', 'mage/adminhtml/variables.js')
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
    
        $this->_title($this->__("Manage Css Selector Elements"));
        $this->_title($this->__("Selector"));
        $this->_title($this->__("New Item"));

        $id = $this->getRequest()->getParam("id");
        $model = Mage::getModel("ves_blockbuilder/selector")->load($id);

        $data = Mage::getSingleton("adminhtml/session")->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        Mage::register("selector_data", $model);

        $this->loadLayout();
        $this->_setActiveMenu("ves_blockbuilder/blockbuilder");

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Css Selector Elements"), Mage::helper("adminhtml")->__("Manage Css Selector Elements"));
        $this->_addBreadcrumb(Mage::helper("adminhtml")->__("Manage Css Selector Elements"), Mage::helper("adminhtml")->__("Manage Css Selector Elements"));


        $this->_addContent($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_selector_edit"))->_addLeft($this->getLayout()->createBlock("ves_blockbuilder/adminhtml_selector_edit_tabs"));

        $this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addItem('js', 'prototype/window.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css')
            ->addCss('lib/prototype/windows/themes/magento.css')
            ->addItem('js', 'mage/adminhtml/variables.js')
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
                //Duplicate Block Builder Profile
                if ($this->getRequest()->getParam("duplicate")) {
                    $model_clone = Mage::getModel('ves_blockbuilder/selector');
                    $model = Mage::getModel("ves_blockbuilder/selector")
                                    ->load($this->getRequest()->getParam("id"));

                    $selector_id = 0;
                    $selector_data = array('element_name' => $model->getData('element_name')."-clone",
                                     'element_tab' => $model->getData('element_tab'),
                                     'element_group' => $model->getData('element_group'),
                                     'element_type' => $model->getData('element_type'),
                                     'element_selector' => $model->getData('element_selector'),
                                     'element_attrs' => $model->getData('element_attrs'),
                                     'template' => $model->getData('template'),
                                     'position' => $model->getData('position'),
                                     'status' => $model->getData('status')
                                    );

                    $model_clone->setData($selector_data);

                    try {
                        $model_clone->save();

                        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_blockbuilder')->__('Profile was successfully duplicated'));
                        Mage::getSingleton('adminhtml/session')->setFormData(false);

                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        Mage::getSingleton('adminhtml/session')->setFormData($selector_data);
                    }
                } else {
                    $model = Mage::getModel("ves_blockbuilder/selector")
                        ->addData($post_data)
                        ->setId($this->getRequest()->getParam("id"))
                        ->save();

                    Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Profile was successfully saved"));
                    Mage::getSingleton("adminhtml/session")->setBlockData(false);
                }

                if ($this->getRequest()->getParam("back")) {
                    $this->_redirect("*/*/edit", array("id" => $model->getId()));
                    return;
                }

                $this->_redirect("*/*/");
                return;

            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                Mage::getSingleton("adminhtml/session")->setSelectorData($this->getRequest()->getPost());
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
                return;
            }

        }
        $this->_redirect("*/*/");
    }

    public function batchSaveAction() {
        $post_data = $this->getRequest()->getPost();

        if ($post_data) {
            try {
                $selector_id = 0;

                $element_types = isset($post_data['element_type'])?$post_data['element_type']: array();
                $element_names = isset($post_data['element_name'])?$post_data['element_name']: array();
                $element_selectors = isset($post_data['element_selector'])?$post_data['element_selector']: array();
                $element_attrs = isset($post_data['element_attrs'])?$post_data['element_attrs']: array();

                $selectors = array();
                if($element_attrs) {
                    foreach($element_attrs as $key => $val ) {

                        $selector_data = array();
                        $selector_data['element_name'] = isset($element_names[$key])?$element_names[$key]:(Mage::helper("ves_blockbuilder")->__("Selector ")." ".$key);
                        $selector_data['element_tab'] = "general";
                        $selector_data['element_group'] = "body";
                        $selector_data['element_type'] = isset($element_types[$key])?$element_types[$key]:"raw-text";
                        $selector_data['element_selector'] = isset($element_selectors[$key])?$element_selectors[$key]:"body";
                        $selector_data['element_attrs'] = isset($element_attrs[$key])?$element_attrs[$key]:"color";
                        $selector_data['template'] = "";
                        $selector_data['position'] = 0;
                        $selector_data['status'] = 1;

                        $model = Mage::getModel("ves_blockbuilder/selector")
                                        ->addData($selector_data)
                                        ->save();
                    }
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                return;
            }
  
        }
        echo Mage::helper("ves_blockbuilder")->__("Saved Batch Successfully!");
    }

    /**
     * Delete
     */
     public function deleteAction() {
     
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('ves_blockbuilder/selector');
                 
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
        $fileName = 'css_selector_profiles.csv';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_selector_exportgrid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     *  Export order grid to Excel XML format
     */
    public function exportExcelAction()
    {
        $fileName = 'css_selector_profiles.xml';
        $grid = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_selector_exportgrid');
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
                Mage::getSingleton('ves_blockbuilder/import_selector')->process($filepath, $stores);

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
        $selector = $this->getLayout()->createBlock('ves_blockbuilder/adminhtml_selector_upload');
        $this->getLayout()->getBlock('content')->append($selector);
        $this->renderLayout();
    }

    public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('ids');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('ves_blockbuilder/selector')
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
                    $_model = Mage::getModel('ves_blockbuilder/selector')
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
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/selector/edit');
                break;
            case 'uploadCsv':
            case 'sample':
            case 'installsample':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/selector/sample');
                break;
            case 'batchSave':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/selector/save');
                break;
            case 'massDelete':
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/selector/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/selector');
                break;
        }
    }
}
?>