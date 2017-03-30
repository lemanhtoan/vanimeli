<?php

class Ves_Gallery_Adminhtml_GallerybannerController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('file');

        return $this;
    }

    public function indexAction() {
		
        $this->_title($this->__('Gallery Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_gallery/adminhtml_banner') );
        $this->renderLayout();
		
    }
	

	

    public function addAction() {
        $this->_title($this->__('New Record'));
		
        $_model  = Mage::getModel('ves_gallery/banner');
        Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);
		
        $this->_initAction();
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gallery Manager'), Mage::helper('adminhtml')->__('Gallery Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Gallery File'), Mage::helper('adminhtml')->__('Add Record'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('ves_gallery/adminhtml_banner_add'))
                ->_addLeft($this->getLayout()->createBlock('ves_gallery/adminhtml_banner_add_tabs'));
		
        $this->renderLayout();
		
    }

   public function editAction() {		
        $bannerId     = $this->getRequest()->getParam('id');
		
        $_model  = Mage::getModel('ves_gallery/banner')->load($bannerId);

        if ($_model->getId()) {
            $this->_title($_model->getId() ? $_model->getLabel() : $this->__('New Record'));
			
            Mage::register('banner_data', $_model);
            Mage::register('current_banner', $_model);
			
            $this->_initAction();
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Gallery Manager'), Mage::helper('adminhtml')->__('Gallery Manager'), $this->getUrl('*/*/'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Edit Record'), Mage::helper('adminhtml')->__('Edit Record'));
			
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('ves_gallery/adminhtml_banner_edit'))
                    ->_addLeft($this->getLayout()->createBlock('ves_gallery/adminhtml_banner_edit_tabs'));
			
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_gallery')->__('The record does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    public function saveAction() {
	
        if ($data = $this->getRequest()->getPost()) {

			if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {					
					try {	
						/* Starting upload */	
						$uploader = new Varien_File_Uploader('file');
						
						// Any extention would work
						$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
						$uploader->setAllowRenameFiles(false);
						
						// Set the file upload mode 
						// false -> get the file directly in the specified folder
						// true -> get the file in the product like folders 
						//	(file.jpg will go in something like /media/f/i/file.jpg)
						$uploader->setFilesDispersion(false);
								
						// We set media as the upload dir
						$path = Mage::getBaseDir('media') . '/gallery/upload/';
						$uploader->save($path, $_FILES['file']['name'] );
						
					} catch (Exception $e) {
				  
					}
					//this way the name is saved in DB
					$data['file'] = 'gallery/upload/' .preg_replace("#\s+#","_", $_FILES['file']['name']);
				}else{
					$data['file'] = $data['file']['value'];
				}
            //Get extra data options
            
            $extra_data = array();
            foreach($data as $key=>$value) {
                if(strpos($key, "extra__") !== false) {
                    $tmp_key = str_replace("extra__", "", $key);
                    $extra_data[$tmp_key] = $value;
                    unset($data[$key]);
                }
            }

            $data['extra'] = serialize($extra_data);

            $_model = Mage::getModel('ves_gallery/banner');
 
            $_model->setData($data)
                    ->setId($this->getRequest()->getParam('id'));
            try {
			
				$_model->setPrice($this->getRequest()->getParam('price'));
				$created_at = $this->getRequest()->getParam('created_at');
				if(!empty($created_at)){
					$_model->setCreatedAt($created_at);
				}else{
					$date = date("Y-m-d H:i:s");
					$today = strtotime($date);
					$_model->setCreatedAt($today);
				}
				
				
				
                $_model->save();
				
				
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_gallery')->__('Record was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    //$this->_redirect('*/*/edit', array('id' => $_model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                //$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_gallery')->__('Unable to find record to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('ves_gallery/banner');

                $model->setId($this->getRequest()->getParam('id'))
                        ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Record was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('ves_gallery/banner')
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

    public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('ves_gallery/banner')
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

    public function imageAction() {
        $result = array();
        try {
            $uploader = new Ves_Gallery_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('ves_gallery/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('ves_gallery/config')->getMediaUrl($result['file']);
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

    protected function _title($text = null, $resetIfExists = true)
    {
        if (is_string($text)) {
            $this->_titles[] = $text;
        } elseif (-1 === $text) {
            if (empty($this->_titles)) {
                $this->_removeDefaultTitle = true;
            } else {
                array_pop($this->_titles);
            }
        } elseif (empty($this->_titles) || $resetIfExists) {
            if (false === $text) {
                $this->_removeDefaultTitle = false;
                $this->_titles = array();
            } elseif (null === $text) {
                $this->_removeDefaultTitle = true;
                $this->_titles = array();
            }
        }
        return $this;
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
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/gallery/add');
                break;
            case 'edit':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/gallery/save');
                break;
            case 'massDelete':
            case 'delete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/gallery/delete');
                break;    
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/gallery/index');
                break;
        }
    }
}