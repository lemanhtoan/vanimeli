<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

$installer = $this;
$installer->startSetup();
$installer->run( Mage::helper('nwdrevslider/install')->getV5InstallSQL() );
$installer->endSetup();
