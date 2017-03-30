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


$installer = $this;
$installer->startSetup();

$installer->run("
	CREATE TABLE IF NOT EXISTS `{$this->getTable('plumrocket_comingsoon_config')}` (
	  `config_id` int(11) NOT NULL AUTO_INCREMENT,
	  `path` char(100) NOT NULL,
	  `value` text NOT NULL,
	  `scope` char(8) NOT NULL DEFAULT 'default',
	  `scope_id` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`config_id`),
	  UNIQUE KEY `idx_path_scope` (`path`,`scope`,`scope_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
");

$installer->run('
	INSERT INTO `'.$this->getTable('plumrocket_comingsoon_config').'` (`config_id`, `path`, `value`, `scope`, `scope_id`) VALUES
		(1, \'comingsoon_mode\', \'live\', \'default\', 0),
		(2, \'comingsoon_signup_enable\', \'1\', \'default\', 0),
		(3, \'comingsoon_signup_method\', \'signup\', \'default\', 0),
		(4, \'comingsoon_launch_time\', \''.(time()+30*86400).'\', \'default\', 0),
		(5, \'comingsoon_launch_action\', \'none\', \'default\', 0),
		(6, \'comingsoon_launch_timer_show\', \'1\', \'default\', 0),
		(7, \'comingsoon_launch_timer_format\', \'dhms\', \'default\', 0),
		(8, \'comingsoon_heading_text\', \'Launching Soon\', \'default\', 0),
		(9, \'comingsoon_welcome_text\', \'We are currently working on something really awesome. Stay tuned!\', \'default\', 0),
		(10, \'comingsoon_registration_text\', \'Thank you for subscribing! We will notify you when our site is up and running!\', \'default\', 0),
		(11, \'comingsoon_restrictions_access_allow\', \'0\', \'default\', 0),
		(12, \'comingsoon_social_tweets_enable\', \'0\', \'default\', 0),
		(13, \'comingsoon_social_twitter_widget_code\', \'<a class="twitter-timeline" href="https://twitter.com/plumrocket" data-widget-id="618412482718248960">Tweets by @plumrocket</a>\r\n<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'\'http\'\':\'\'https\'\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>\', \'default\', 0),
		(14, \'comingsoon_social_share_buttons_show\', \'1\', \'default\', 0),
		(15, \'comingsoon_social_links_show\', \'1\', \'default\', 0),
		(16, \'comingsoon_social_facebook_url\', \'https://www.facebook.com/plumrocket/\', \'default\', 0),
		(17, \'comingsoon_social_twitter_url\', \'https://twitter.com/plumrocket/\', \'default\', 0),
		(18, \'comingsoon_social_linkedin_url\', \'https://www.linkedin.com/company/plumrocket-inc/\', \'default\', 0),
		(19, \'comingsoon_social_googleplus_url\', \'https://plus.google.com/+Plumrocket/\', \'default\', 0),
		(20, \'comingsoon_social_youtube_url\', \'https://www.youtube.com/user/plumrocket/\', \'default\', 0),
		(21, \'comingsoon_social_github_url\', \'https://github.com/plumrocket/\', \'default\', 0),
		(22, \'comingsoon_social_flickr_url\', \'\', \'default\', 0),
		(23, \'comingsoon_social_pinterest_url\', \'\', \'default\', 0),
		(24, \'comingsoon_meta_page_title\', \'Our store is coming soon\', \'default\', 0),
		(25, \'comingsoon_meta_description\', \'Subscribe to our newsletter and get notified when we launch the website.\', \'default\', 0),
		(26, \'comingsoon_meta_keywords\', \'magento store, coming soon, launching soon\', \'default\', 0),
		(27, \'comingsoon_background_style\', \'image\', \'default\', 0),
		(28, \'comingsoon_background_image\', \'a:1:{s:19:"default_image_1.jpg";a:4:{s:4:"name";s:19:"default_image_1.jpg";s:5:"label";s:0:"";s:9:"date_from";s:0:"";s:7:"date_to";s:0:"";}}\', \'default\', 0),
		(29, \'comingsoon_background_video\', \'a:1:{s:10:"video_1000";a:3:{s:3:"url";s:26:"https://vimeo.com/29950141";s:9:"date_from";s:0:"";s:7:"date_to";s:0:"";}}\', \'default\', 0),
		(30, \'maintenance_launch_time\', \''.(time()+86400).'\', \'default\', 0),
		(31, \'maintenance_launch_action\', \'none\', \'default\', 0),
		(32, \'maintenance_launch_timer_show\', \'1\', \'default\', 0),
		(33, \'maintenance_launch_timer_format\', \'dhms\', \'default\', 0),
		(34, \'maintenance_refresh\', \'2\', \'default\', 0),
		(35, \'maintenance_response_header\', \'503\', \'default\', 0),
		(36, \'maintenance_heading_text\', \'We\'\'re Under Maintenance\', \'default\', 0),
		(37, \'maintenance_description\', \'<p>Our store is undergoing a brief bit of maintenance.</p><p>We apologize for the inconvenience, we\'\'re doing our best to get things back to working order for you.</p>\', \'default\', 0),
		(38, \'maintenance_background_style\', \'image\', \'default\', 0),
		(39, \'maintenance_background_image\', \'a:1:{s:19:"default_image_2.jpg";a:4:{s:4:"name";s:19:"default_image_2.jpg";s:5:"label";s:0:"";s:9:"date_from";s:0:"";s:7:"date_to";s:0:"";}}\', \'default\', 0),
		(40, \'maintenance_background_video\', \'a:1:{s:10:"video_1000";a:3:{s:3:"url";s:26:"https://vimeo.com/29950141";s:9:"date_from";s:0:"";s:7:"date_to";s:0:"";}}\', \'default\', 0);'
);

$installer->endSetup();