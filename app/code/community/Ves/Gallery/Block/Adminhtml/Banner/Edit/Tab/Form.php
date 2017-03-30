<?php


class Ves_Gallery_Block_Adminhtml_Banner_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('banner_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('slider_form', array('legend'=>Mage::helper('ves_gallery')->__('General Information')));
        
		
		$fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('ves_gallery')->__('Is Active'),
            'name'      => 'is_active',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            //'value'     => $_model->getIsActive()
        ));
		$fieldset->addField('title', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));

		$fieldset->addField('file', 'image', array(
            'label'     => Mage::helper('ves_gallery')->__('Image'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'file',
        ));
		
		$fieldset->addField('label', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Group'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'label',
            //'value'     => $_model->getLabel()
        ));

        $fieldset->addField('classes', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Css Classes'),
            'class'     => '',
            'required'  => false,
            'name'      => 'classes',
            //'value'     => $_model->getPosition()
        ));

         $fieldset->addField('crop_mode', 'select', array(
            'label'     => Mage::helper('ves_gallery')->__('Thumbnail Crop Mode'),
            'note'      => Mage::helper('ves_gallery')->__('Choose a resize and crop mode when resize thumbnail in gallery.'),
            'class'     => '',
            'required'  => false,
            'name'      => 'crop_mode',
            'values'      =>array('top'=>Mage::helper('ves_gallery')->__('Top'), 'bottom'=>Mage::helper('ves_gallery')->__('Bottom'), 'center' => Mage::helper('ves_gallery')->__('Center'))
        ));
		
        $fieldset->addField('extra__thumb_width', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Thumbnail Width'),
            'note'      => Mage::helper('ves_gallery')->__('Input a integer value of thumbnail width which you want to custom resize thumbnail width option for the image.<br/>For example: <strong>300</strong> (px)'),
            'class'     => '',
            'required'  => false,
            'name'      => 'extra__thumb_width',
            //'value'     => $_model->getPosition()
        ));

        $fieldset->addField('extra__thumb_height', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Thumbnail Height'),
            'note'      => Mage::helper('ves_gallery')->__('Input a integer value of thumbnail height which you want to custom resize thumbnail height option for the image.<br/>For example: <strong>300</strong> (px)'),
            'class'     => '',
            'required'  => false,
            'name'      => 'extra__thumb_height',
            //'value'     => $_model->getPosition()
        ));

        $fieldset->addField('links', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('links'),
            'class'     => '',
            'required'  => false,
            'name'      => 'links',
            //'value'     => $_model->getPosition()
        ));
		
		$fieldset->addField('position', 'text', array(
            'label'     => Mage::helper('ves_gallery')->__('Position'),
            'class'     => 'required-entry',
            'required'  => false,
            'name'      => 'position',
			//'value'     => $_model->getPosition()
        ));
	
		
		$fieldset->addField('description', 'editor', array(
            'label'     => Mage::helper('ves_gallery')->__('Description'),
            'class'     => '',
            'required'  => false,
            'name'      => 'description',
			'style'     => 'width:600px;height:300px;',
            'wysiwyg'   => false,
			//'value'     => $_model->getDescription()
			//'config'    => Mage::getVersion() > '1.4' ? @Mage::getSingleton('cms/wysiwyg_config')->getConfig() : false,
        ));
		

        
		

        
		if ( Mage::getSingleton('adminhtml/session')->getBannerData() )
		  {
			  $form->setValues(Mage::getSingleton('adminhtml/session')->getBannerData());
			  Mage::getSingleton('adminhtml/session')->getBannerData(null);
		  } elseif ( Mage::registry('banner_data') ) {
			  $form->setValues(Mage::registry('banner_data')->getData());
		  }
        
        return parent::_prepareForm();
    }
}
