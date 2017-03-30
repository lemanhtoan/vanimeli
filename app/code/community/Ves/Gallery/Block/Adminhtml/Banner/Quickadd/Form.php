<?php

class Ves_Gallery_Block_Adminhtml_Banner_Quickadd_Form extends Mage_Adminhtml_Block_Widget_Form {
    
	 public function __construct(){
		
		echo 'hahah'; die;
        parent::__construct();
        $this->setTemplate('ves_megamenu/edit/form.phtml');
		
    }
	
	protected function _prepareForm() {
		
		
        $form = new Varien_Data_Form(
                array(
                        'id' => 'edit_form',
                        'action' => $this->getUrl('*/*/save'),
                        'method' => 'post',
						'enctype' => 'multipart/form-data'
                )
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}