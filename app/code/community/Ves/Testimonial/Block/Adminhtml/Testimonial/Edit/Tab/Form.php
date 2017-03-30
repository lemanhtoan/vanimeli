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

class Ves_Testimonial_Block_Adminhtml_Testimonial_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    
    protected function _prepareForm()
    {

        $_model = Mage::registry('testimonial_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('testimonial_form', array('legend'=>Mage::helper('ves_testimonial')->__('General Information')));
        
		$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('ves_testimonial')->__('Is Active'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray()
        ));

        $fieldset->addField('avatar', 'image', array(
            'label'     => Mage::helper('ves_testimonial')->__('Avatar'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'avatar'
        ));

		$fieldset->addField('video_link', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Video Testimonial Link (youtube or vimeo link)'),
            'class'     => '',
            'required'  => false,
            'name'      => 'video_link'
        ));
		
		$fieldset->addField('position', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Position'),
            'class'     => '',
            'required'  => false,
            'name'      => 'position'
        ));

		$fieldset->addField('note1', 'note',array( 
            'text' => Mage::helper('ves_testimonial')->__('Social for a testimonial If Empty for hide '), 
        ));
        //------------------------------------- 

        $facebook = $fieldset->addField('facebook', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Link Facebook'),
            'class'     => '',
            'required'  => false,
            'name'      => 'facebook',
            'after_element_html' => '<br><small>facebook.com/</small>',
        ));
     
        $twiter = $fieldset->addField('twiter', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Twiter'),
            'class'     => '',
            'required'  => false,
            'name'      => 'twiter',
            'after_element_html' => '<br><small>twitter.com/</small>',
        ));
        $fieldset->addField('google', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Google'),
            'class'     => '',
            'required'  => false,
            'name'      => 'google',
            'after_element_html' => '<br><small>plus.google.com/</small>',
        ));
        $fieldset->addField('youtube', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Youtube'),
            'class'     => '',
            'required'  => false,
            'name'      => 'youtube',
            'after_element_html' => '<br><small>youtube.com/user/</small>',
        ));
        $fieldset->addField('pinterest', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Pinterest '),
            'class'     => '',
            'required'  => false,
            'name'      => 'pinterest',
            'after_element_html' => '<br><small>pinterest.com/ </small>',
        ));
        $fieldset->addField('vimeo', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Vimeo '),
            'class'     => '',
            'required'  => false,
            'name'      => 'vimeo',
            'after_element_html' => '<br><small>vimeo.com/ </small>',
        ));
        $fieldset->addField('instagram', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Instagram'),
            'class'     => '',
            'required'  => false,
            'name'      => 'instagram',
            'after_element_html' => '<br><small>instagram.com/</small>',
        ));
        $fieldset->addField('linkedIn', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('LinkedIn'),
            'class'     => '',
            'required'  => false,
            'name'      => 'linkedIn',
            'after_element_html' => '<br><small>linkedin.com/in/</small>',
        ));

       $fieldset->addField('group_testimonial_id', 'select', array(
            'label'     => Mage::helper('ves_testimonial')->__('Group Testimonial'),
            'name'      => 'group_testimonial_id',
            'class'     => 'required-entry',
            'required'  => true,
            'values'    => $this->getGroupToOptionArray(),
            //'value'     => $_model->getStatus(),
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

			$config->setData(Mage::helper('ves_testimonial')->recursiveReplace(
					'/ves_testimonial/',
					'/'.(string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName').'/',
					$config->getData()
				)
			);
				
		}
        catch (Exception $ex){
            $config = null;
        }		
		$fieldset->addField('profile', 'editor', array(
            'label'     => Mage::helper('ves_testimonial')->__('Profile Information'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'profile',
			'style'     => 'width:600px;height:300px;',
            'wysiwyg'   => true,
			 'config'    =>  $config
        ));
		
        $fieldset->addField('description', 'editor', array(
            'label'     => Mage::helper('ves_testimonial')->__('Description'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'description',
            'style'     => 'width:600px;height:300px;',
            'wysiwyg'   => true,
             'config'    =>  $config
        ));
		
        
		if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'stores[]',
                'label' => Mage::helper('ves_testimonial')->__('Store View'),
                'title' => Mage::helper('ves_testimonial')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')
                             ->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'stores[]',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }
		 
        
		if ( Mage::getSingleton('adminhtml/session')->getTestimonialData() )
		  {
			  $form->setValues(Mage::getSingleton('adminhtml/session')->getTestimonialData());
			  Mage::getSingleton('adminhtml/session')->getTestimonialData(null);
		  } elseif ( Mage::registry('testimonial_data') ) {
			  $form->setValues(Mage::registry('testimonial_data')->getData());
		  }

        // Depency Hide Row
        // $this->setForm($form);
        // $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
        //     ->addFieldMap($facebooks->getHtmlId(), $facebooks->getName())
        //     ->addFieldMap($twiters->getHtmlId(), $twiters->getName())
        //     ->addFieldMap($facebook->getHtmlId(), $facebook->getName())
        //     ->addFieldMap($twiter->getHtmlId(), $twiter->getName())
        //     ->addFieldDependence(
        //         $facebook->getName(),
        //         $facebooks->getName(),
        //         '1'
        //     )
        //     ->addFieldDependence(
        //         $twiter->getName(),
        //         $twiters->getName(),
        //         '2'
        //     )
        // );

        return parent::_prepareForm();
    }

    public function getGroupToOptionArray() {
        $catCollection = Mage::getModel('ves_testimonial/group')
                    ->getCollection();
        $option = array();
        $option[] = array( 'value' => "", 
                           'label' => 'Select Group Testimonial');
        foreach($catCollection as $cat) {
            $option[] = array( 'value' => $cat->getId(), 
                               'label' => $cat->getName() );
        }
        return $option;
    }
	
	  public function getParentToOptionArray() {
		$catCollection = Mage::getModel('ves_testimonial/testimonial')
					->getCollection();
		$id = Mage::registry('testimonial_data')->getId();
		if($id) {
			$catCollection->addFieldToFilter('testimonial_id', array('neq' => $id));
		}
		$option = array();
		$option[] = array( 'value' => 0, 
						   'label' => 'Top Level');
		foreach($catCollection as $cat) {
			$option[] = array( 'value' => $cat->getId(), 
							   'label' => $cat->getTitle() );
		}
		return $option;
    }
}
