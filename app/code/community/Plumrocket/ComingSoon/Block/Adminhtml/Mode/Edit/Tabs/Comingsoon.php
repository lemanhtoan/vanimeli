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

 
class Plumrocket_ComingSoon_Block_Adminhtml_Mode_Edit_Tabs_Comingsoon
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

        $general = $form->addFieldset('comingsoon_general_fieldset', array('legend' => $this->__('Coming Soon Page'), 'class' => 'fieldset-wide'));

        $general->addField('comingsoon_general_note', 'note', array(
            'text'      => '<span>Great to start collecting emails from visitors or registering new members before the site is fully open.</span>',
        ));

        list($scope, $scopeId) = $helper->getScope();
        $scope = substr($scope, 0, -1);
        $general->addField($scope, 'hidden', array(
            'name'      => $scope,
            'value'     => $scopeId,
        ));

        $signup = $form->addFieldset('comingsoon_signup_fieldset', array('legend' => $this->__('Sign-up Form'), 'class' => 'fieldset-wide'));
        
        $signupEnable = $signup->addField('comingsoon_signup_enable', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_signup_enable',
            'label'     => $this->__('Enable Sign-up Form'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 1,
        ));

        $signupMethod = $signup->addField('comingsoon_signup_method', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_signup_method',
            'label'     => $this->__('User Sign-Up Method'),
            'required'  => true,
            'values'    => array(
                                'signup' => $this->__('Sign-up for email newsletter only'),
                                'register_signup' => $this->__('Register customer account & sign-up for newsletter'),
                            ),
            'value'     => 'signup',
            'note'      => $this->__('Please note: if customer registration is selected and password field is not enabled above, then passwords will be generated automatically for each user and sent by email'),
        ));

        $signupFields = $signup->addField('comingsoon_signup_fields', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_signup_fields',
            'label'     => $this->__('Enable Form Fields'),
            'note'      => $this->__('Selected field will be displayed on sign-up form. Please note, "Email" is required field')
        ));

        $signupFieldsValues = $helper->getSignupFields( !empty($data['comingsoon_signup_fields'])? $data['comingsoon_signup_fields'] : array() );

        $form->getElement('comingsoon_signup_fields')
        ->setRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_inputTable')
        )
        ->getRenderer()
            ->setContainerFieldId('comingsoon_signup_fields')
            ->setRowKey('name')
            ->addColumn('orig_label', array(
                'header'    => $this->__('Field'),
                'index'     => 'orig_label',
                'type'      => 'label',
            ))
            ->addColumn('label', array(
                'header'    => $this->__('Displayed Name'),
                'index'     => 'label',
                'type'      => 'input',
            ))
            ->addColumn('sort_order', array(
                'header'    => $this->__('Sort Order'),
                'index'     => 'sort_order',
                'type'      => 'input',
                // 'inline_css'=> 'validate-zero-or-greater',
            ))
            ->addColumn('enable', array(
                'header'    => $this->__('Enable'),
                'index'     => 'enable',
                'type'      => 'checkbox',
                'value'     => 1,
            ))
            ->setArray($signupFieldsValues);

        $launch = $form->addFieldset('comingsoon_launch_fieldset', array('legend' => $this->__('Launch Settings'), 'class' => 'fieldset-wide'));

        $launch->addField('comingsoon_launch_time', 'date', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'comingsoon_launch_time',
            'label'     => $this->__('Launch Date & Time'),
            'required'  => true,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => $helper->getDateTimeFormat(),
            'time'      => true,
            'value'     => Mage::app()->getLocale()->storeTimeStamp($helper->getStoreId()) + (60 * 60 * 24 * 30),
            'note'      => $this->__('Scheduled "Opening Day". If entered, launch date will be used for countdown timer'),
        ));

        $launch->addField('comingsoon_launch_action', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'name'      => 'comingsoon_launch_action',
            'label'     => $this->__('Launch Date Action'),
            'required'  => true,
            'values'    => array(
                                'none' => $this->__('Do Nothing'),
                                'live' => $this->__('Switch to "Live Site" Mode'),
                            ),
            'value'     => 'none',
            'note'      => $this->__('Automated job will be executed on launch date'),
        ));

        $launchTimerShow = $launch->addField('comingsoon_launch_timer_show', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_launch_timer_show',
            'label'     => $this->__('Show Launch Countdown Timer'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 1,
        ));

        $launchTimerFormat = $launch->addField('comingsoon_launch_timer_format', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_launch_timer_format',
            'label'     => $this->__('Countdown Timer Format'),
            'required'  => true,
            'value'     => $this->__('dhms'),
            'note'      => '<a href="http://wiki.plumrocket.com/wiki/Magento_Coming_Soon_and_Maintenance_Page_v1.x_Configuration" target="_blank">Click here</a> for more details on available time formats',
        ));

        $text = $form->addFieldset('comingsoon_text_fieldset', array('legend' => $this->__('Text & Labels'), 'class' => 'fieldset-wide'));
        
        $text->addField('comingsoon_heading_text', 'editor', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'              => 'comingsoon_heading_text',
            'label'             => $this->__('Heading Text'),
            'required'          => true,
            'config'            => $helper->getWysiwygConfig(),
            'value'             => $this->__('Launching Soon'),
        ));

        $text->addField('comingsoon_welcome_text', 'editor', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'              => 'comingsoon_welcome_text',
            'label'             => $this->__('Welcome Text'),
            'required'          => true,
            'config'            => $helper->getWysiwygConfig(),
            'value'             => $this->__('We are currently working on something really awesome. Stay tuned!'),
        ));

        $text->addField('comingsoon_registration_text', 'editor', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'              => 'comingsoon_registration_text',
            'label'             => $this->__('Registration Confirmation Text'),
            'required'          => true,
            'config'            => $helper->getWysiwygConfig(),
            'value'             => $this->__('Thank you for subscribing! We will notify you when our site is up and running!'),
        ));

        $restrictions = $form->addFieldset('comingsoon_restrictions_fieldset', array('legend' => $this->__('Site Restrictions'), 'class' => 'fieldset-wide'));

        $restrictionsAccessAllow = $restrictions->addField('comingsoon_restrictions_access_allow', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_restrictions_access_allow',
            'label'     => $this->__('Allow Access to Site Pages'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 0,
        ));

        $restrictionsAccessPages = $restrictions->addField('comingsoon_restrictions_access_pages', 'multiselect', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_restrictions_access_pages',
            'label'     => $this->__('Accessible Pages'),
            'required'  => true,
            'class'     => 'validate-select',
            // 'values'    => Mage::getSingleton('adminhtml/system_config_source_cms_page')->toOptionArray(),
            'values'    => Mage::getSingleton('comingsoon/system_config_source_page')->toOptionArray(),
            'note'      => $this->__('Enable access to CMS and other store pages listed above'),
        ));

        $social = $form->addFieldset('comingsoon_social_fieldset', array('legend' => $this->__('Social'), 'class' => 'fieldset-wide'));

        $socialTweetsEnable = $social->addField('comingsoon_social_tweets_enable', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_tweets_enable',
            'label'     => $this->__('Display Recent Tweets'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 0,
        ));

        $socialTwitterWidgetCode = $social->addField('comingsoon_social_twitter_widget_code', 'textarea', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_twitter_widget_code',
            'label'     => $this->__('Twitter Widget Code'),
            'required'  => true,
            'value'     => '<a class="twitter-timeline" href="https://twitter.com/plumrocket" data-widget-id="618412482718248960">Tweets by @plumrocket</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>',
            'note'      => '<a href="https://twitter.com/settings/widgets" target="_blank">Create widget</a> in your Twitter account and copy the code here',
        ));

        $social->addField('comingsoon_social_share_buttons_show', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_share_buttons_show',
            'label'     => $this->__('Show Share Buttons'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 1,
        ));

        $socialLinksShow = $social->addField('comingsoon_social_links_show', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_links_show',
            'label'     => $this->__('Show Social Links'),
            'required'  => true,
            'values'    => Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray(),
            'value'     => 1,
        ));

        $socialFacebookUrl = $social->addField('comingsoon_social_facebook_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_facebook_url',
            'label'     => $this->__('Facebook Page'),
            'class'     => 'validate-url',
            'value'     => 'https://www.facebook.com/plumrocket/',
        ));

        $socialTwitterUrl = $social->addField('comingsoon_social_twitter_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_twitter_url',
            'label'     => $this->__('Twitter Page'),
            'class'     => 'validate-url',
            'value'     => 'https://twitter.com/plumrocket/',
        ));

        $socialLinkedinUrl = $social->addField('comingsoon_social_linkedin_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_linkedin_url',
            'label'     => $this->__('LinkedIn Page'),
            'class'     => 'validate-url',
            'value'     => 'https://www.linkedin.com/company/plumrocket-inc/',
        ));

        $socialGoogleplusUrl = $social->addField('comingsoon_social_googleplus_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_googleplus_url',
            'label'     => $this->__('Google+ Page'),
            'class'     => 'validate-url',
            'value'     => 'https://plus.google.com/+Plumrocket/',
        ));

        $socialYoutubeUrl = $social->addField('comingsoon_social_youtube_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_youtube_url',
            'label'     => $this->__('Youtube Page'),
            'class'     => 'validate-url',
            'value'     => 'https://www.youtube.com/user/plumrocket/',
        ));

        $socialGithubUrl = $social->addField('comingsoon_social_github_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_github_url',
            'label'     => $this->__('Github Page'),
            'class'     => 'validate-url',
            'value'     => 'https://github.com/plumrocket/',
        ));

        $socialFlickrUrl = $social->addField('comingsoon_social_flickr_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_flickr_url',
            'label'     => $this->__('Flickr Page'),
            'class'     => 'validate-url',
            'value'     => '',
        ));

        $socialPinterestUrl = $social->addField('comingsoon_social_pinterest_url', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_social_pinterest_url',
            'label'     => $this->__('Pinterest Page'),
            'class'     => 'validate-url',
            'value'     => '',
        ));

        $meta = $form->addFieldset('comingsoon_meta_fieldset', array('legend' => $this->__('Meta Data'), 'class' => 'fieldset-wide'));

        $meta->addField('comingsoon_meta_page_title', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_meta_page_title',
            'label'     => $this->__('Page Title'),
            'required'  => true,
            'value'     => 'Our store is coming soon',
        ));

        $meta->addField('comingsoon_meta_description', 'textarea', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_meta_description',
            'label'     => $this->__('Meta Description'),
            'required'  => true,
            'value'     => 'Subscribe to our newsletter and get notified when we launch the website.',
        ));

        $meta->addField('comingsoon_meta_keywords', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_meta_keywords',
            'label'     => $this->__('Meta Keywords'),
            'required'  => true,
            'value'     => 'magento store, coming soon, launching soon',
        ));

        $background = $form->addFieldset('comingsoon_background_fieldset', array('legend' => $this->__('Background Settings'), 'class' => 'fieldset-wide'));

        $background->addField('comingsoon_background_style', 'select', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_background_style',
            'label'     => $this->__('Background Style'),
            'required'  => true,
            'values'    => array(
                                'image' => $this->__('Single Image'),
                                'slideshow' => $this->__('Slideshow'),
                                'video' => $this->__('Video Background'),
                            ),
            'value'     => 'image',
        ));

        $background->addField('comingsoon_background_image', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_background_image',
            'label'     => $this->__('Images'),
        ));

        $renderDate = Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_date');
        $renderDate->setForm($form);

        $form->getElement('comingsoon_background_image')
        ->setRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_imageGallery')
        )
        ->getRenderer()
            ->setContainerFieldId('comingsoon_background_image')
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
                //'value'     => 1,
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
            ->setArray( $helper->getBackgroundImages( !empty($data['comingsoon_background_image'])? $data['comingsoon_background_image'] : array() ));

        $background->addField('comingsoon_uploader', 'note', array(
            'after_element_html'      => $this->getLayout()->createBlock('adminhtml/media_uploader')->toHtml(),
        ));

        $background->addField('comingsoon_background_video', 'text', array(
            'scope'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
            'name'      => 'comingsoon_background_video',
            'label'     => $this->__('Videos'),
            'note'      => $this->__('Supports videos from YouTube.com or Vimeo.com. Also direct links to .mp4, .webm, .ogv files are allowed.')
        ));
        
        $renderDate = Mage::getBlockSingleton('comingsoon/adminhtml_mode_edit_renderer_column_date');
        $renderDate->setForm($form);

        $form->getElement('comingsoon_background_video')
        ->setRenderer(
            $this->getLayout()->createBlock('comingsoon/adminhtml_mode_edit_renderer_videoGallery')
        )
        ->getRenderer()
            ->setContainerFieldId('comingsoon_background_video')
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
                //'value'     => 1,
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
            ->setArray( $helper->getBackgroundVideos( !empty($data['comingsoon_background_video'])? $data['comingsoon_background_video'] : array('video_1000' => array('url' => 'https://vimeo.com/29950141')) ));
            
            $background->addField('comingsoon_background_video_add', 'note', array(
                'after_element_html'      => $this->getLayout()
                    ->createBlock('adminhtml/widget_button')
                    ->addData(array(
                        'id'      => 'comingsoon_background_video_add_button',
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
                ->addFieldMap($signupEnable->getHtmlId(), $signupEnable->getName())
                ->addFieldMap($signupMethod->getHtmlId(), $signupMethod->getName())
                ->addFieldMap($signupFields->getHtmlId(), $signupFields->getName())
                ->addFieldDependence($signupMethod->getName(), $signupEnable->getName(), '1')
                ->addFieldDependence($signupFields->getName(), $signupEnable->getName(), '1')
                ->addFieldDependence($signupFields->getName(), $signupMethod->getName(), 'register_signup')

                ->addFieldMap($launchTimerShow->getHtmlId(), $launchTimerShow->getName())
                ->addFieldMap($launchTimerFormat->getHtmlId(), $launchTimerFormat->getName())
                ->addFieldDependence($launchTimerFormat->getName(), $launchTimerShow->getName(), '1')

                ->addFieldMap($restrictionsAccessAllow->getHtmlId(), $restrictionsAccessAllow->getName())
                ->addFieldMap($restrictionsAccessPages->getHtmlId(), $restrictionsAccessPages->getName())
                ->addFieldDependence($restrictionsAccessPages->getName(), $restrictionsAccessAllow->getName(), '1')

                ->addFieldMap($socialTweetsEnable->getHtmlId(), $socialTweetsEnable->getName())
                ->addFieldMap($socialTwitterWidgetCode->getHtmlId(), $socialTwitterWidgetCode->getName())
                ->addFieldDependence($socialTwitterWidgetCode->getName(), $socialTweetsEnable->getName(), '1')

                ->addFieldMap($socialLinksShow->getHtmlId(), $socialLinksShow->getName())
                ->addFieldMap($socialFacebookUrl->getHtmlId(), $socialFacebookUrl->getName())
                ->addFieldMap($socialTwitterUrl->getHtmlId(), $socialTwitterUrl->getName())
                ->addFieldMap($socialLinkedinUrl->getHtmlId(), $socialLinkedinUrl->getName())
                ->addFieldMap($socialGoogleplusUrl->getHtmlId(), $socialGoogleplusUrl->getName())
                ->addFieldMap($socialYoutubeUrl->getHtmlId(), $socialYoutubeUrl->getName())
                ->addFieldMap($socialGithubUrl->getHtmlId(), $socialGithubUrl->getName())
                ->addFieldMap($socialFlickrUrl->getHtmlId(), $socialFlickrUrl->getName())
                ->addFieldMap($socialPinterestUrl->getHtmlId(), $socialPinterestUrl->getName())
                ->addFieldDependence($socialFacebookUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialTwitterUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialLinkedinUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialGoogleplusUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialYoutubeUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialGithubUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialFlickrUrl->getName(), $socialLinksShow->getName(), '1')
                ->addFieldDependence($socialPinterestUrl->getName(), $socialLinksShow->getName(), '1')
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
        return $this->__('Coming Soon Page');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Coming Soon Page');
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
