<?php
/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Rule_Edit_Tab_Conditions extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('productlist_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        /*
         $soure_type = $form->addFieldset('custom_fieldset', array(
            'legend' => $this->__('Product Source')
            ));
        $soure_type->addField('source_type', 'select', array(
            'label'     => $this->__('Select Source'),
            'name'      => 'source_type',
            'options'   => array(
                'latest' => $this->__('Latest'),
                'new_arrival' => $this->__('New Arrival'),
                'special' => $this->__('Special'),
                'most_viewed' => $this->__('Most Viewed'),
                'best_seller' => $this->__('Best Seller'),
                'top_rate' => $this->__('Top Rated')
                ),
            'note' => $this->__('Default is Latest'),
            ));
        */
        
        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
        ->setTemplate('promo/fieldset.phtml')
        ->setNewChildUrl($this->getUrl('*/*/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('productlist')->__('Conditions (leave blank for all products)'))
        )->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('productlist')->__('Conditions'),
            'title' => Mage::helper('productlist')->__('Conditions'),
            'required' => true,
            ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}