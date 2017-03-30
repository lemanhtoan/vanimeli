<?php
/******************************************************
 * @package Ves Magento Theme Framework for Magento 1.4.x or latest
 * @version 1.1
 * @author http://www.venusthemes.com
 * @copyright	Copyright (C) Feb 2013 VenusThemes.com <@emai:venusthemes@gmail.com>.All rights reserved.
 * @license		GNU General Public License version 2
*******************************************************/
class Ves_BlockBuilder_Block_Adminhtml_Selector_Upload_Form extends Ves_BlockBuilder_Block_Adminhtml_Selector_Abstract_Upload_Form {

	protected function _prepareForm() {
		$form = new Varien_Data_Form(array(
				'id' => 'upload_form',
				'action' => $this->getUrl('*/*/importCsv'),
				'method' => 'post',
				'enctype' => 'multipart/form-data'
		)
		);

		$fieldset = $form->addFieldset('upload_json', array('legend' => Mage::helper('ves_blockbuilder')->__('Import Profile From CSV')));

		$fieldset->addField('importfile', 'file', array(
				'label'     => Mage::helper('ves_blockbuilder')->__('Upload CSV File'),
				'required'  => true,
				'name'      => 'importfile',
		));


		$fieldset->addField('submit', 'note', array(
				'type' => 'submit',
				'text' => $this->getButtonHtml(
					Mage::helper('ves_blockbuilder')->__('Upload & Import'),
					"upload_form.submit();",
					'upload'
				)
		));

        $form->setUseContainer(true);
        $this->setForm($form);

		return parent::_prepareForm();

	} // end fun

}