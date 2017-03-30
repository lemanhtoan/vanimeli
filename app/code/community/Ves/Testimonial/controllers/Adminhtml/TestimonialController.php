<?php 
 /*------------------------------------------------------------------------
  # VenusTheme Testimonial Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Testimonial_Adminhtml_TestimonialController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_testimonial/testimonial');

        return $this;
    }
	
	
	/**
	 * index action
	 */ 
    public function indexAction() {
		
		$this->_title($this->__('Testimonials Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_testimonial/adminhtml_testimonial') );
        $this->renderLayout();
		
    }
	
	public function editAction(){
		$this->_title($this->__('Edit Record'));
		$id     = $this->getRequest()->getParam('id');
        $_model  = Mage::getModel('ves_testimonial/testimonial')->load( $id );

		Mage::register('testimonial_data', $_model);
        Mage::register('current_testimonial', $_model);
		
		$this->loadLayout();
	    $this->_setActiveMenu('ves_testimonial/testimonial');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Testimonial Manager'), Mage::helper('adminhtml')->__('Testimonial Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Testimonial'), Mage::helper('adminhtml')->__('Add Testimonial'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('ves_testimonial/adminhtml_testimonial_edit'))
                ->_addLeft($this->getLayout()->createBlock('ves_testimonial/adminhtml_testimonial_edit_tabs'));
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
		    $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
	    }
        $this->renderLayout();
	}
	
	public function addAction(){
		$this->_forward('edit');
	}
	
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {	    
			$model = Mage::getModel('ves_testimonial/testimonial');

			if(isset($_FILES['avatar']['name']) && $_FILES['avatar']['name'] != '') {
				try {	
					/* Starting upload */	
					$uploader = new Varien_File_Uploader('avatar');
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$path = Mage::getBaseDir('media') . '/vestestimonial/';
					$uploader->save($path, $_FILES['avatar']['name'] );
					
				} catch (Exception $e) {
			  
				}
				//this way the name is saved in DB
				$data['avatar'] = 'vestestimonial/' .preg_replace("#\s+#","_", $_FILES['avatar']['name']);
	
			}else{
				$data['avatar'] = $data['avatar']['value'];
			}

			//$data['stores'] = $this->getRequest()->getParam('stores');
			
			$data = array(
				'profile'         => $data['profile'],
				'description'     => $data['description'],
				'avatar'          => $data['avatar'],
				'video_link'      => $data['video_link'],
				'group_testimonial_id' => $data['group_testimonial_id'],
				'is_active'       => $data['is_active'],
				'position'        => $data['position'],
				'facebook'        => $data['facebook'],
				'twiter'          => $data['twiter'],
				'google'          => $data['google'],
				'youtube'         => $data['youtube'],
				'pinterest'       => $data['pinterest'],
				'vimeo'           => $data['vimeo'],
				'instagram'       => $data['instagram'],
				'linkedIn'        => $data['linkedIn'],
				//'store_id'        => $stores,
				);
			$data['stores'] = $this->getRequest()->getParam('stores');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			try {

				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_testimonial')->__('Testimonial was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				
				// save rewrite url
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_testimonial')->__('Unable to find cat to save'));
		$this->_redirect('*/*/');
    }

   /**
	 * getSocials
	 */
	 public function getSocials($data) {
	 	$socials = "";
	 	if($data['facebook']){
	 		$socials .= 'facebook'.$data['facebook'].',';
	 	}
	 	if($data['twiter']){
	 		$socials .= $data['twiter'].',';
	 	}

	 	return $socials;

	 }

	/**
	 * Delete
	 */
	 public function deleteAction() {
	 
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('ves_testimonial/testimonial');
				 
				$model->setId($this->getRequest()->getParam('id'));
				
				Mage::getModel('core/url_rewrite')->loadByIdPath('ves_testimonial/testimonial/'.$model->getId())->delete();
				
				$model->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This Testimonial Was Deleted Done'));
				$this->_redirect('*/*/');
			
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
    }
	
	
	 public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('testimonial');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('ves_testimonial/testimonial')
                            ->setIsMassStatus(true)
                            ->load($itemId)
                            ->setIsActive($this->getRequest()->getParam('status'))
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
        $IDList = $this->getRequest()->getParam('testimonial');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('ves_testimonial/testimonial')
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
            case 'add':
            case 'edit':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/testimonial/add');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/testimonial/testimonials');
                break;
        }
    }
	
}
?>