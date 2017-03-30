<?php
 /*------------------------------------------------------------------------
  # Ves Testimonial Module 
  # ------------------------------------------------------------------------
  # author:    Venustheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_Testimonial_Adminhtml_TestimonialgroupController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_testimonial/group');

        return $this;
    }

    public function indexAction() {
        $this->_title($this->__('Testimonials Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_testimonial/adminhtml_group') );
        $this->renderLayout();
		
    }
     /**
    * function call model pagebuider
    * @param none
    * 
    */
    public function group() {
     return Mage::getModel('ves_testimonial/group');
    }

    public function editAction(){
    $this->_title($this->__('Edit Record'));
    $id     = $this->getRequest()->getParam('id');
    $id   = $id?$id: 0;
    $_model  = Mage::getModel('ves_testimonial/group')->load( $id );
    Mage::register('group_data', $_model);
    $this->loadLayout();
    $this->_setActiveMenu('ves_testimonial/group');
    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Group Manager'), Mage::helper('adminhtml')->__('Group Manager'), $this->getUrl('*/*/'));
    $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add group'), Mage::helper('adminhtml')->__('Add group'));
    $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

    $this->_addContent($this->getLayout()->createBlock('ves_testimonial/adminhtml_group_edit'))
        ->_addLeft($this->getLayout()->createBlock('ves_testimonial/adminhtml_group_edit_tabs'));
    if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
      }
      $this->renderLayout();
/*
        $this->_addContent($this->getLayout()->createBlock('ves_testimonial/adminhtml_group_edit'));

        $this->renderLayout();*/
        return false;
  }

  public function addAction(){
    $this->_redirect('*/*/edit');
  }

   /**
    * function Add New or Save  group 
    * @param name POST
    * @param status POST
    */
    public function savegroupAction() {
       
        $name          = $this->getRequest()->getPost('name');
        $status        = $this->getRequest()->getPost('status');
        
        $postData = $this->getRequest()->getPost();
        $imgFilename = NULL;
        $iconFilename = NULL;
        $redirectPath   = '*/*/index';
        $time = rand(1,time());
        $redirectParams = array();
        try{
          if($this->getRequest()->getPost('group_id')){ // check group_id
            $group_id = $this->group()->update($this->getRequest()->getPost('group_id'),$name,$status);
          }else{
            $group_id = $this->group()->add($name ,$status);
          }
        } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('ves_testimonial')->__('An error occurred while saving the group item.')
                );
        }
        if ($hasError) {
                $this->_getSession()->setFormData($postData);
                $redirectPath   = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('id'));
        }
        $this->_redirect($redirectPath, $redirectParams);
    }// End Add or Update
  /**
   * Delete
   */
   public function deleteAction() {
   
    if( $this->getRequest()->getParam('id') > 0 ) {
      try {
        $model = Mage::getModel('ves_testimonial/group');
         
        $model->setId($this->getRequest()->getParam('id'));
        
        $model->delete();
           
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This Group Location Was Deleted Done'));
        $this->_redirect('*/*/');
      
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
      }
    }
    $this->_redirect('*/*/');
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
            case 'add':
            case 'edit':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/testimonial/addgroup');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/testimonial/testimonialgroup');
                break;
        }
    }
}// End Class
  