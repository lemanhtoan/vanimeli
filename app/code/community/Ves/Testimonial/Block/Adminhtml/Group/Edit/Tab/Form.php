<?php
 /*------------------------------------------------------------------------
  # VenusTheme group Module 
  # ------------------------------------------------------------------------
  # author:    VenusTheme.Com
  # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
  # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
  # Websites: http://www.venustheme.com
  # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/

class Ves_Testimonial_Block_Adminhtml_Group_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $_model = Mage::registry('group_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('group_form', array('legend'=>Mage::helper('ves_testimonial')->__('General Information')));
        
		$fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('ves_testimonial')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'name',
            //'value'     => $_model->getData('name'),
        ));

		$fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('ves_testimonial')->__('Is Active'),
            'name'      => 'status',
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            //'value'     => $_model->getStatus(),
        ));

        $fieldset->addField('group_id', 'hidden', array(
            'label'     => Mage::helper('ves_testimonial')->__('Title'),
            'name'      => 'group_id',
            'value'     => $_model->getId(),
        ));
		if ( Mage::getSingleton('adminhtml/session')->getGroupData() )
		  {
			  $form->setValues(Mage::getSingleton('adminhtml/session')->getgroupData());
			  Mage::getSingleton('adminhtml/session')->getgroupData(null);
		  } elseif ( Mage::registry('group_data') ) {
			  $form->setValues(Mage::registry('group_data')->getData());
		  }
        
        return parent::_prepareForm();
    }
	
	  public function getParentToOptionArray() {
		$catCollection = Mage::getModel('ves_testimonial/group')
					->getCollection();
		$id = Mage::registry('group_data')->getId();
		if($id) {
			$catCollection->addFieldToFilter('group_id', array('neq' => $id));
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
