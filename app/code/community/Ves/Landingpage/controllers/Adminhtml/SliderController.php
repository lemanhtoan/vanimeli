<?php
 /*------------------------------------------------------------------------
  # Ves contenttab Module 
  # ------------------------------------------------------------------------
  # author:    Venustheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Landingpage_Adminhtml_SliderController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_landingpage/slider');
        return $this;
    }

    /**
    * function call model slider
    * @param none
    * 
    */
    public function slider() {
     return Mage::getModel('ves_landingpage/slider');
    }

    public function indexAction() {
        $this->_title($this->__('Landing Page Slider Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_landingpage/adminhtml_slider') );
        $this->renderLayout();
		
    }

    public function editAction(){
    $this->_title($this->__('Edit Record'));
    $id     = $this->getRequest()->getParam('id');
    $id   = $id?$id: 0;
    $_model  = Mage::getModel('ves_landingpage/slider')->load( $id );
    Mage::register('slider_data', $_model);
    $this->loadLayout();
      $this->_setActiveMenu('ves_landingpage/slider');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Slider Manager'), Mage::helper('adminhtml')->__('slider Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Slider'), Mage::helper('adminhtml')->__('Add slider'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('ves_landingpage/adminhtml_slider_edit'))
              ->_addLeft($this->getLayout()->createBlock('ves_landingpage/adminhtml_slider_edit_slider'));

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
        return false;
  }

  public function addAction(){
    $this->_redirect('*/*/edit');
  }

   /**
    * function Add New or Save  slider 
    * 
    */
    public function savesliderAction() {
      if($this->getRequest()->getPost()){
        //
        $postData           = $this->getRequest()->getPost();
        //
        $caption_1          = trim( $postData['caption_1']);
        $class_1            = trim( $postData['class1']);
        $effect_1           = $postData['effect_1'] ;
        $caption_2          = trim( $postData['caption_2']);
        $class_2            = trim( $postData['class_2']);
        $effect_2           = $postData['effect_2'] ;
        $caption_3          = trim( $postData['caption_3']);
        $class_3            = trim( $postData['class_3']);
        $effect_3           = $postData['effect_3'] ;
        $status             = $postData['status'];
        //

        //echo $caption_3.'||'.$class_3.'||'.$effect_3; die;
        $imgFilename = NULL;
        $iconFilename = NULL;
        $redirectPath   = '*/*/index';
        $time = rand(1,time());
        $redirectParams = array();
        try {
            $hasError = false;
            if($postData['slider_id']){ // check slider_id
              $slider_id = $this->slider()->update($postData['slider_id'],$caption_1 , $class_1 ,$effect_1 ,$caption_2 , $class_2 ,$effect_2 ,$caption_3 , $class_3 ,$effect_3 ,$status);
            }else{
              $slider_id = $this->slider()->add($caption_1 , $class_1 ,$effect_1 ,$caption_2 , $class_2 ,$effect_2 ,$caption_3 , $class_3 ,$effect_3 ,$status);
            }
        } catch (Mage_Core_Exception $e) {
                $hasError = true;
                $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
                $hasError = true;
                $this->_getSession()->addException($e,
                    Mage::helper('ves_landingpage')->__('An error occurred while saving the slider item.')
                );
        }
        if ($hasError) {
                $this->_getSession()->setFormData($postData);
                $redirectPath   = '*/*/edit';
                $redirectParams = array('id' => $this->getRequest()->getParam('id'));
        }
      }
        $this->_redirect($redirectPath, $redirectParams);
    }// End Add or Update
  /**
   * Delete
   */
   public function deleteAction() {
   
    if( $this->getRequest()->getParam('id') > 0 ) {
      try {
        $model = Mage::getModel('ves_landingpage/slider');
         
        $model->setId($this->getRequest()->getParam('id'));
        
        $model->delete();
           
        Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This slider Was Deleted Done'));
        $this->_redirect('*/*/');
      
      } catch (Exception $e) {
        Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
      }
    }
    $this->_redirect('*/*/');
  }
}// End Class