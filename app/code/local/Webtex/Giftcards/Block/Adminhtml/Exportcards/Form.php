<?php
/*
 * Webtex Gift Cards
 *
 * Export Gift Cards Form
 *
 * (C) WebtexSoftware 2015
 */
class Webtex_Giftcards_Block_Adminhtml_Exportcards_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('giftcards')->__('Export Settings')));

        $fieldset->addField('export_type', 'select', array(
                                'name'  	=> 'export_type',
                                'label' 	=> Mage::helper('giftcards')->__('File type for export'),
                                'title' 	=> Mage::helper('giftcards')->__('File type for export'),
                                'required'	=> true,
                                'options'   => array('csv' => 'CSV',
                                                     'xml' => 'XML',
                                    )
                                )
            );

        $fieldset->addField('file_path', 'text', array(
                                'name'  	=> 'file_path',
                                'label' 	=> Mage::helper('giftcards')->__('Path to file'),
                                'title' 	=> Mage::helper('giftcards')->__('Path to file'),
                                'required'	=> true
                                )
            );

		$fieldset->addField('delimiter', 'text', array(
                                'name'  	=> 'delimiter',
                                'label' 	=> Mage::helper('giftcards')->__('Value delimiter'),
                                'title' 	=> Mage::helper('giftcards')->__('Value delimiter'),
                                'required'	=> true
                                )
            );

		$fieldset->addField('enclosure', 'text', array(
                                'name'  	=> 'enclosure',
                                'label' 	=> Mage::helper('giftcards')->__('Enclose Values In'),
                                'title' 	=> Mage::helper('giftcards')->__('Enclose Values In'),
                                'required'	=> true
                                )
            );


        // $fieldset = $form->addFieldset('params_fieldset', array('legend'=>Mage::helper('giftcards')->__('Export Parameters')));

        $fieldset->addField('card_type', 'select', array(
                                'name'  	=> 'card_type',
                                'label' 	=> Mage::helper('giftcards')->__('Cards type for export'),
                                'title' 	=> Mage::helper('giftcards')->__('Cards type for export'),
                                'required'	=> true,
                                'options'   => array('0' => Mage::helper('giftcards')->__('All'),
                                                     'email' => Mage::helper('giftcards')->__('Email'),
                                                     'print' => Mage::helper('giftcards')->__('Print'),
                                                     'offline' => Mage::helper('giftcards')->__('Offline'),
                                    )
                                )
            );

        $fieldset->addField('card_status', 'select', array(
                                'name'  	=> 'card_status',
                                'label' 	=> Mage::helper('giftcards')->__('Cards status for export'),
                                'title' 	=> Mage::helper('giftcards')->__('Cards status for export'),
                                'required'	=> true,
                                'options'   => array('3' => Mage::helper('giftcards')->__('All'),
                                                     '0' => Mage::helper('giftcards')->__('Inactive'),
                                                     '1' => Mage::helper('giftcards')->__('Active'),
                                                     '2' => Mage::helper('giftcards')->__('Used'),
                                    ),
                                )
            );
        

        $fieldset->addField('date_from', 'date', array(
            'label'     => 'Gift Card Create Date From',
            'title'     => 'Gift Card Create Date From',
            'required'  => false,
            'name'      => 'date_from',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'M/d/y',
        ));

        $fieldset->addField('date_to', 'date', array(
            'label'     => 'Gift Card Create Date To',
            'title'     => 'Gift Card Create Date To',
            'required'  => false,
            'name'      => 'date_to',
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            'format' => 'M/d/y',
        ));
/*        
        $fieldset->addField('include_orders', 'checkbox', array(
                                'name'  	=> 'include_orders',
                                'label' 	=> Mage::helper('giftcards')->__('Include Orders Information'),
                                'title' 	=> Mage::helper('giftcards')->__('Include Orders information'),
                                )
            );
*/        
        $exportConfig = array('file_path' => '/var/export/giftcards',
                              'delimiter' => ';',
                              'enclosure' => '"',
                              'export_type' => 'csv',
            );

        $form->setValues($exportConfig);
        $form->setAction($this->getUrl('adminhtml/giftcards_exportcards/saveExport'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
