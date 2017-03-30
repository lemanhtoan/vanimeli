<?php
 /*------------------------------------------------------------------------
  # VenusTheme slider Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_Landingpage_Block_Adminhtml_Slider_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('slider_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $configSettings = array( 'add_widgets' => false, 'add_variables' => false, 'add_images' => false, 'files_browser_window_url'=> $this->getBaseUrl().'admin/cms_wysiwyg_images/index/');
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array('add_variables' => false,
         'add_widgets' => false,
          'add_images' => true,
          'files_browser_window_url' => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
          'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
          'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height')
         ));
        
        try{
          $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                    array(
                            'add_widgets' => false,
                            'add_variables' => false,
                        )
                    );
          if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
          }  

          $config->setData(Mage::helper('ves_landingpage')->recursiveReplace(
              '/ves_landingpage/',
              '/'.(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName').'/',
              $config->getData()
            )
          );
            
        }catch (Exception $ex){
                $config = null;
        }

        $fieldset = $form->addFieldset('slider_general_form', array('legend'=>Mage::helper('ves_landingpage')->__('General Setting')));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('ves_landingpage')->__('Is Active'),
            'name'      => 'status',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            //'value'     => $_model->getStatus(),
        ));

        $fieldset->addField('slider_id', 'hidden', array(
            'label'     => Mage::helper('ves_landingpage')->__('Slider ID'),
            'name'      => 'slider_id',
            'value'     => $_model->getId(),
        ));


        $slider_item_1 = $form->addFieldset('slider_1_form', array('legend'=>Mage::helper('ves_landingpage')->__('Slider 1')));
        
		$slider_item_1->addField('caption_1', 'textarea', array(
            'label'     => Mage::helper('ves_landingpage')->__('Caption'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'caption_1',
            'style'     => 'width:600px;height:100px;',
            'config'    => $wysiwygConfig,
            'wysiwyg'   => true,
        ));
        $slider_item_1->addField('class1', 'text', array(
                'label'     => Mage::helper('ves_landingpage')->__('Css Class'),
                'class'     => '',
                'required'  => false,
                'name'      => 'class1',
            ));
        $slider_item_1->addField('effect_1', 'select', array(
                'label'     => Mage::helper('ves_landingpage')->__('Effect'),
                'class'     => '',
                'required'  => false,
                'name'      => 'effect_1',
                'values'   => Mage::helper('ves_landingpage')->getEffectList()
            ));

        $slider_item_2 = $form->addFieldset('slider_2_form', array('legend'=>Mage::helper('ves_landingpage')->__('Slider 2')));
    //----------------------------------------
    $slider_item_2->addField('caption_2', 'textarea', array(
            'label'     => Mage::helper('ves_landingpage')->__('Caption'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'caption_2',
            'style'     => 'width:600px;height:100px;',
            'config'    => $wysiwygConfig,
            'wysiwyg'   => true,
        ));
    $slider_item_2->addField('class_2', 'text', array(
            'label'     => Mage::helper('ves_landingpage')->__('Css Class'),
            'class'     => '',
            'required'  => false,
            'name'      => 'class_2',
        ));
    $slider_item_2->addField('effect_2', 'select', array(
            'label'     => Mage::helper('ves_landingpage')->__('Effect'),
            'class'     => '',
            'required'  => false,
            'name'      => 'effect_2',
            'values'   => Mage::helper('ves_landingpage')->getEffectList()
        ));

    $slider_item_3 = $form->addFieldset('slider_3_form', array('legend'=>Mage::helper('ves_landingpage')->__('Slider 3')));
    //----------------------------------------
    $slider_item_3->addField('caption_3', 'textarea', array(
            'label'     => Mage::helper('ves_landingpage')->__('Caption'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'caption_3',
            'style'     => 'width:600px;height:100px;',
            'config'    => $wysiwygConfig,
            'wysiwyg'   => true,
        ));
    $slider_item_3->addField('class_3', 'text', array(
            'label'     => Mage::helper('ves_landingpage')->__('Css Class'),
            'class'     => '',
            'required'  => false,
            'name'      => 'class_3',
        ));
    $slider_item_3->addField('effect_3', 'select', array(
            'label'     => Mage::helper('ves_landingpage')->__('Effect'),
            'class'     => '',
            'required'  => false,
            'name'      => 'effect_3',
            'values'   => Mage::helper('ves_landingpage')->getEffectList()
        ));
		
		if ( Mage::getSingleton('adminhtml/session')->getsliderData() )
		  {
			  $form->setValues(Mage::getSingleton('adminhtml/session')->getsliderData());
			  Mage::getSingleton('adminhtml/session')->getsliderData(null);
		  } elseif ( Mage::registry('slider_data') ) {
			  $form->setValues(Mage::registry('slider_data')->getData());
		  }
        
        return parent::_prepareForm();
    }
	
	  public function getGroupToOptionArray() {
		$catCollection = Mage::getModel('ves_landingpage/group')
					->getCollection();
		//$id = Mage::registry('slider_data')->getData('group_slider_id');
		//if($id) {
			//$catCollection->addFieldToFilter('group_slider_id', array('eq' => $id));
		//}
		$option = array();
		$option[] = array( 'value' => 0, 
						   'label' => 'Select Group slider');
		foreach($catCollection as $cat) {
			$option[] = array( 'value' => $cat->getId(), 
							   'label' => $cat->getName() );
		}
		return $option;
    }

    public function getAllStaticBlocksArray() {
    $cmsblocks = array();
    $blocks = Mage::getModel('cms/block')->getCollection()
                                            ->addFilter("is_active", 1)
                                            ->getItems();
        $cmsblocks[] = array('value' => 0, 'label' => Mage::helper('ves_customslider')->__("---- Select a Static Block ----"));
        if(!empty($blocks)){
            foreach($blocks as $block){
                $cmsblocks[] = array('value' => $block->getId(), 'label' => $block->getTitle());
            }
        }
        return $cmsblocks;
  }
}
