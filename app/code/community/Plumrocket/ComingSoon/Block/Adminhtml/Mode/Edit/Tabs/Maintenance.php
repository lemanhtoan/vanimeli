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

 
class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Tabs_Maintenance
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
        
        $general = $form->addFieldset('maintenance_general_fieldset', array('legend' => $this->__('Maintenance Page'), 'class' => 'fieldset-wide'));

        $general->addField('maintenance_general_note', 'note', array(
            'text'      => '<span>Set your live site in maintenance mode when applying some technical or design changes to your store.</span>',
        ));

        $config = $form->addFieldset('maintenance_config_fieldset', array('legend' => $this->__('Configuration'), 'class' => 'fieldset-wide'));

        $config->addField('maintenance_launch_time', 'date', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'maintenance_launch_time',
            'label'     => $this->__('Launch Date & Time'),
            'required'  => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $helper->getDateTimeFormat(),
            'time'      => true,
            'value'     => Mage::app()->getLocale()->storeTimeStamp($helper->getStoreId()) + (60 * 60 * 24),
            'note'      => $this->__('Scheduled "Opening Day". If entered, launch date will be used for countdown timer'),
        ));

        $config->addField('maintenance_launch_action', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'maintenance_launch_action',
            'label'     => $this->__('Launch Date Action'),
            'required'  => true,
            'values'    => array(
                                'none' => $this->__('Do Nothing'),
                                'live' => $this->__('Switch to "Live Site" Mode'),
                            ),
            'value'     => 'none',
            'note'      => $this->__('Automated job will be executed on launch date (when maintenance is finished)'),
        ));

        $configTimerShow = $config->addField('maintenance_launch_timer_show', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_launch_timer_show',
            'label'     => $this->__('Show Launch Countdown Timer'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 1,
        ));

        $configTimerFormat = $config->addField('maintenance_launch_timer_format', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_launch_timer_format',
            'label'     => $this->__('Launch Timer Format'),
            'required'  => true,
            'value'     => $this->__('dhms'),
            'note'      => '<a href="http://wiki.plumrocket.com/wiki/Magento_Coming_Soon_and_Maintenance_Page_v1.x_Configuration" target="_blank">Click here</a> for more details on available time formats',
        ));

        $socialTweetsCount = $config->addField('maintenance_refresh', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_refresh',
            'label'     => $this->__('Auto Refresh Page Every (minutes)'),
            // 'required'  => true,
            'class'     => 'validate-greater-than-zero',
            'value'     => '2',
            'note'      => $this->__('Leave empty to disable autorefresh'),
        ));

        $config->addField('maintenance_response_header', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'maintenance_response_header',
            'label'     => $this->__('HTTP Response Header'),
            'required'  => true,
            'values'    => array(
                                '503' => $this->__('503 Service Unavailable (Server down for maintenance)'),
                                '200' => $this->__('200 OK (Success)'),
                            ),
            'value'     => '503',
            'note'      => $this->__('Choose if you want to notify search engines that your site is temporary unavailable or not'),
        ));

        $text = $form->addFieldset('maintenance_text_fieldset', array('legend' => $this->__('Text & Labels'), 'class' => 'fieldset-wide'));
        
        $text->addField('maintenance_heading_text', 'editor', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'              => 'maintenance_heading_text',
            'label'             => $this->__('Heading Text'),
            'required'          => true,
            'config'            => $helper->getWysiwygConfig(),
            'value'             => $this->__('We\'re Under Maintenance'),
        ));

        $text->addField('maintenance_description', 'editor', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'              => 'maintenance_description',
            'label'             => $this->__('Description'),
            'required'          => true,
            'config'            => $helper->getWysiwygConfig(),
            'value'             => $this->__('<p>Our store is undergoing a brief bit of maintenance.</p><p>We apologize for the inconvenience, we\'re doing our best to get things back to working order for you.</p>'),
        ));

        $background = $form->addFieldset('maintenance_background_fieldset', array('legend' => $this->__('Background Settings'), 'class' => 'fieldset-wide'));

        $background->addField('maintenance_background_style', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_background_style',
            'label'     => $this->__('Background Style'),
            'required'  => true,
            'values'    => array(
                                'image' => $this->__('Single Image'),
                                'slideshow' => $this->__('Slideshow'),
                                'video' => $this->__('Video Background'),
                            ),
            'value'     => 'image',
        ));

        $background->addField('maintenance_background_image', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_background_image',
            'label'     => $this->__('Images'),
        ));

        
        $renderDate = Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_date');
        $renderDate->setForm($form);

        $form->getElement('maintenance_background_image')
        ->setRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_imageGallery')
        )
        ->getRenderer()
            ->setContainerFieldId('maintenance_background_image')
            ->setRowKey('name')
            ->addColumn('name', array(
                'header'    => $this->__('Image'),
                'index'     => 'name',
                'renderer'  => Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_image'),
            ))
            ->addColumn('label', array(
                'header'    => $this->__('Label'),
                'index'     => 'label',
                'type'      => 'input',
            ))
            ->addColumn('date_from', array(
                'header'    => $this->__('Active From'),
                'index'     => 'date_from',
                'renderer'  => $renderDate,
                'column_css_class' => 'date_from',
            ))
            ->addColumn('date_to', array(
                'header'    => $this->__('Active To'),
                'index'     => 'date_to',
                'renderer'  => clone $renderDate,
                'column_css_class' => 'date_to',
            ))
            ->addColumn('exclude', array(
                'header'    => $this->__('Exclude'),
                'index'     => 'exclude',
                'type'      => 'checkbox',
                // 'value'     => 1,
                'values'    => array(1 => 1),
                'column_css_class' => 'exclude',
            ))
            ->addColumn('remove', array(
                'header'    => $this->__('Remove'),
                'index'     => 'remove',
                'type'      => 'checkbox',
                'value'     => 1,
                'column_css_class' => 'remove',
            ))
            ->setArray( $helper->getBackgroundImages( !empty($data['maintenance_background_image'])? $data['maintenance_background_image'] : array() ));

        $background->addField('maintenance_uploader', 'note', array(
        ));

        $background->addField('maintenance_background_video', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'maintenance_background_video',
            'label'     => $this->__('Videos'),
            'note'      => $this->__('Supports videos from YouTube.com or Vimeo.com. Also direct links to .mp4, .webm, .ogv files are allowed.')
        ));
        
        $renderDate = Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_date');
        $renderDate->setForm($form);

        $form->getElement('maintenance_background_video')
        ->setRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_videoGallery')
        )
        ->getRenderer()
            ->setContainerFieldId('maintenance_background_video')
            ->setRowKey('name')
            ->addColumn('preview', array(
                'header'    => $this->__('Video'),
                'index'     => 'preview',
                'renderer'  => Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_video'),
            ))
            ->addColumn('url', array(
                'header'    => $this->__('Video Url'),
                'index'     => 'url',
                'type'      => 'input',
                'inline_css'=> 'validate-url plcs-video-url',
            ))
            ->addColumn('date_from', array(
                'header'    => $this->__('Active From'),
                'index'     => 'date_from',
                'renderer'  => $renderDate,
                'column_css_class' => 'date_from',
            ))
            ->addColumn('date_to', array(
                'header'    => $this->__('Active To'),
                'index'     => 'date_to',
                'renderer'  => clone $renderDate,
                'column_css_class' => 'date_to',
            ))
            ->addColumn('exclude', array(
                'header'    => $this->__('Exclude'),
                'index'     => 'exclude',
                'type'      => 'checkbox',
                // 'value'     => 1,
                'values'    => array(1 => 1),
                'column_css_class' => 'exclude',
            ))
            ->addColumn('remove', array(
                'header'    => $this->__('Remove'),
                'index'     => 'remove',
                'type'      => 'checkbox',
                'value'     => 1,
                'column_css_class' => 'remove',
            ))
            ->setArray( $helper->getBackgroundVideos( !empty($data['maintenance_background_video'])? $data['maintenance_background_video'] : array('video_1000' => array('url' => 'https://vimeo.com/29950141')) ));
            
            $background->addField('maintenance_background_video_add', 'note', array(
                'after_element_html'      => $this->getLayout()
                    ->createBlock('adminhtml/widget_button')
                    ->addData(array(
                        'id'      => 'maintenance_background_video_add_button',
                        'label'   => $this->__('Add Video'),
                        'type'    => 'button',
                    ))
                    ->toHtml(),
            ));


        if(!empty($data)) {
            $helper->addFormValues($form, $data);
        }
        $this->setForm($form);

        $this->setChild('form_after',
            $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap($configTimerShow->getHtmlId(), $configTimerShow->getName())
                ->addFieldMap($configTimerFormat->getHtmlId(), $configTimerFormat->getName())
                ->addFieldDependence($configTimerFormat->getName(), $configTimerShow->getName(), '1')
        );

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Maintenance Page');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Maintenance Page');
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
