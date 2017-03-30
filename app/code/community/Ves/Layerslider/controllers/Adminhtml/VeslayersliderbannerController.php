<?php 
 /*------------------------------------------------------------------------
  # VenusTheme Brand Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Layerslider_Adminhtml_VeslayersliderbannerController extends Mage_Adminhtml_Controller_Action {
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('ves_layerslider/banner');

        return $this;
    }
	
	
	/**
	 * index action
	 */ 
    public function indexAction() {
		$this->_title($this->__('Banner Manager'));
        $this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('ves_layerslider/adminhtml_banner') );
        $this->renderLayout();
		
    }
	
	public function editAction(){
		$this->_title($this->__('Edit Record'));
		$id     = $this->getRequest()->getParam('id');
		$id 	= $id?$id: 0;
        $_model  = Mage::getModel('ves_layerslider/banner')->load( $id );

		Mage::register('banner_data', $_model);
        Mage::register('current_banner', $_model);
		
		$this->loadLayout();
	    $this->_setActiveMenu('ves_layerslider/banner');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner Manager'), Mage::helper('adminhtml')->__('Banner Manager'), $this->getUrl('*/*/'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Add Banner'), Mage::helper('adminhtml')->__('Add Banner'));

        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('ves_layerslider/adminhtml_banner_edit'));

		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
		    $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
	    }
        $this->renderLayout();
        return false;
	}

	public function addAction(){
		$this->_redirect('*/*/edit');
	}

	public function ajaxuploadAction() {
		$result = array();
		if ($data = $this->getRequest()->getPost()) {
	        try {
	        	$image_name = isset($data['image_name'])?$data['image_name']:"";
	        	$image_source = isset($data['source'])?$data['source']:"";

	            if($image_name && $image_source) {
					$image = Mage::helper("ves_layerslider/uploadHandler")->saveImage($image_source, $image_name, true);
					$result['status'] = "SUCCESS";
					$result['imageSrc'] = $image;
				}
	        } catch (Exception $e) {
	            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
	        }
		}
		echo Mage::helper('core')->jsonEncode($result);
		return false;
	}
	public function saveAction() {

		if ($data = $this->getRequest()->getPost()) {	

			$model = Mage::getModel('ves_layerslider/banner');
			$action = $this->getRequest()->getParam('action');
			$banner_id = $this->getRequest()->getParam('banner_id');

			if($action != "duplicate"){
				$slider_info = isset($data['slider']) ?$data['slider']:array();
				$slider_info['title'] = isset($slider_info['title'])?$slider_info['title']:"";
				$slider_info['alias'] = isset($slider_info['alias'])?$slider_info['alias']:"";
				/*If empty title or alias, redirect to form to show error*/
				if(!$slider_info['title'] || !$slider_info['alias']) {
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_layerslider')->__('Please input slider title, slider alias.'));
					$this->_redirect('*/*/edit', array('id' => $banner_id));
				}

				$slider_options = isset($data['slider_options']) ?$data['slider_options']:"";
				$slider_data = isset($data['slider_data']) ?$data['slider_data']:"";
				$slider_data = Mage::helper('core')->jsonDecode($slider_data);

				if($slider_data) {
					/*Upload Background Image*/
					$image_background = isset($slider_data['bg'])?$slider_data['bg']:array();
					$image = "";
					if($image_background) {
						/*
						$image_src = $image_background['src'];
						$image_code = $image_background['src64'];
						$image = Mage::helper("ves_layerslider/uploadHandler")->saveImage($image_code, $image_src);*/
						$slider_data['bg']['src64'] = "";
					}

					foreach($slider_data as $key => $slider) {
						if(strpos($key, "slide-container-") !== false) {
							
							foreach($slider as $k => $v) {

								if(isset($v['itemData']) && is_array($v['itemData'])) {
									$image_src = $v['itemData']['src'];
									$image_code = $v['itemData']['src64'];
									
									if($image_code)	{
										$image = Mage::helper("ves_layerslider/uploadHandler")->saveImage($image_code, $image_src);
									}
									$slider_data[$key][$k]['itemData']['src64'] = "";
									if(isset($v['itemData']['videosrc']) && strpos($v['itemData']['videosrc'], "data:image/jpeg;base64") !== false) {
										$base64img = str_replace('data:image/jpeg;base64,', '', $v['itemData']['videosrc']);
										$videosrc = base64_decode($base64img);
										$slider_data[$key][$k]['itemData']['videosrc'] = "";
									}
								}
							}
						}
					}
					/*Upload item image*/
				}
				

				$banner_data = array();

				$banner_data['title'] = isset($slider_info['title'])?$slider_info['title']:"";

				$banner_data['alias'] = isset($slider_info['alias'])?$slider_info['alias']:"";

				$banner_data['is_active'] = isset($slider_info['is_active'])?$slider_info['is_active']:"0";

				$banner_data['is_flexslider'] = isset($slider_info['is_flexslider'])?$slider_info['is_flexslider']:"0";

				$banner_data['position'] = isset($slider_info['position'])?$slider_info['position']:"0";

				$banner_data['stores'] = $this->getRequest()->getParam('stores');

				if(1 == Mage::getStoreConfig("ves_layerslider/general_setting/enable_encode")) {
					$banner_data['params'] = base64_encode(serialize($slider_data));
				} else {
					$banner_data['params'] = serialize($slider_data);
				}
				
				$banner_data['options'] = serialize($slider_options);
			} else {
				$model2 = Mage::getModel('ves_layerslider/banner')->load($banner_id);
				$banner_id = 0;
				$banner_data = array('stores' => $model2->getStoreId(),
									 'params' => $model2->getParams(),
									 'options' => $model2->getOptions(),
									 'title' => $model2->getTitle(),
									 'alias' => $model2->getAlias(),
									 'is_active' => $model2->getIsActive(),
									 'is_flexslider' => $model2->getIsFlexslider(),
									 'position' => $model2->getPosition());

			}

			$model->setData($banner_data);
		
			if($banner_id)
                $model->setId($banner_id);

			try {
				$model->save();

				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_layerslider')->__('Banner was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}

				if($action == "save_stay"){
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }else{
                    $this->_redirect('*/*/index');
                }
				return;
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($banner_data);
				if($banner_id) {
					$this->_redirect('*/*/index');
				} else {
					$this->_redirect('*/*/edit', array('id' => $banner_id));
				}
				
				return;
			}
		}

		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_layerslider')->__('Unable find slider data to save'));
		$this->_redirect('*/*/index');
    }
	
	public function imageAction() {
        $result = array();
        try {
            $uploader = new Venustheme_Brand_Media_Uploader('image');
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $result = $uploader->save(
                    Mage::getSingleton('ves_layerslider/config')->getBaseMediaPath()
            );

            $result['url'] = Mage::getSingleton('ves_layerslider/config')->getMediaUrl($result['file']);
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
				$model = Mage::getModel('ves_layerslider/banner');
				 
				$model->setId($this->getRequest()->getParam('id'));
				
				$model->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('This Brand Was Deleted Done'));
				$this->_redirect('*/*/');
			
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
    }
	
	public function massResizeAction(){
		try {
			$collection = Mage::getModel('ves_layerslider/banner')->getCollection();
			$sizes = array( "brand_imagesize" => "l" );
			
			foreach( $collection as $post ){
				if( $post->getFile() ){
					
					foreach( $sizes as $key => $size ){
						$c = Mage::getStoreConfig( 'ves_layerslider/general_setting/'.$key );
						$tmp = explode( "x", $c );
						if( count($tmp) > 0 && (int)$tmp[0] ){
							
							Mage::helper('ves_layerslider')->resizeImage( $post->getFile(), (int)$tmp[0], (int)$tmp[1] );
						}
					}	
				}
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Images Of All Brands are resized successful'));
		} catch ( Exception $e ) {
			  Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/');
	}
	
	 public function massStatusAction() {
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getSingleton('ves_layerslider/banner')
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
        $IDList = $this->getRequest()->getParam('banner');
        if(!is_array($IDList)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select record(s)'));
        } else {
            try {
                foreach ($IDList as $itemId) {
                    $_model = Mage::getModel('ves_layerslider/banner')
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
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/ves_layerslider/add');
                break;
            case 'duplicate':
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/ves_layerslider/save');
                break;

            case 'delete':
            case 'massDelete':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/ves_layerslider/delete');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/ves_layerslider/banners');
                break;
        }
    }
	
}
?>