<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please 
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Coming_Soon
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */

 
class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Tabs_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_field')
        );

        $data = Mage::registry('comingsoon_config_default');
        $helper = Mage::helper('comingsoon');

        $fieldset = $form->addFieldset('general_fieldset', array('legend' => $this->__('Website Mode'), 'class' => 'fieldset-wide'));

        $fieldset->addType('comingsoon_mode_checker', 'Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Renderer_ModeChecker');
        $signupFields = $fieldset->addField('comingsoon_mode_checker', 'comingsoon_mode_checker', array(
            'name'      => 'comingsoon_mode_checker',
            'label'     => $this->__('Website Mode'),
            'required'  => true,
        ));

        $fieldset->addField('comingsoon_mode', 'radios', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'comingsoon_mode',
            'label'     => $this->__('Website Mode'),
            'values'    => array(
                                array('value' => 'live',        'label' => $this->__('Live Site') ),
                                array('value' => 'comingsoon',  'label' => $this->__('Coming Soon') ),
                                array('value' => 'maintenance', 'label' => $this->__('Under Maintenance') ),
                            ),
            'value'     => 'live',
            'note'      => $this->__('Select website mode above and then press "Save" to activate it. Admin can preview each page and even access "live site" while store is in launching soon or maintenance mode'),
        ));

        if(!empty($data)) {
            $helper->addFormValues($form, $data);
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Website Mode');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Website Mode');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

}
