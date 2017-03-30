<?php
/**
 * Nwdthemes All Extension
 *
 * @package     All
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2014. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 *
 * Use this code for system.xml
 *
 * <about translate="label">
<label>NWDthemes.com</label>
<frontend_type>text</frontend_type>
<sort_order>0</sort_order>
<show_in_default>1</show_in_default>
<show_in_website>1</show_in_website>
<show_in_store>1</show_in_store>
<expanded>1</expanded>
<fields>
<info translate="label">
<frontend_model>nwdall/adminhtml_system_config_nwdthemes</frontend_model>
<sort_order>1</sort_order>
<show_in_default>1</show_in_default>
<show_in_website>1</show_in_website>
<show_in_store>1</show_in_store>
</info>
</fields>
</about>
 */

class Nwdthemes_All_Block_Adminhtml_System_Config_Nwdthemes
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render Information element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '
<div style="background:#EAF0EE; border:1px solid #CCCCCC; padding:10px 10px 5px;">
    <img src="'.Mage::getBaseUrl('skin') . '/frontend/base/default/nwdthemes/images/nwd_logo.jpg" alt="" style="max-width:100px; float: left; padding: 0 20px 0 0" />

    <div style="overflow: hidden">

        <a href="http://nwdthemes.com" target="_blank">NWDthemes.com</a>

        <p>We provide premium magento themes and extensions to help launch your Magento store quickly and easily. All our
        templates and extensions are specially designed for Magento and are completely customizable.</p>

        <p>Our goal is to create ultimate quality products. Each product is heavily tested, both internally and on
        selected beta customers.</p>

        <p>Our extensions on <a href="http://codecanyon.net/user/nwdthemes/portfolio?ref=nwdthemes" target="_blank">Codecanyon</a><br />
        Should you have any questions email at <a href="mailto:mail@nwdthemes.com">mail@nwdthemes.com</a>
    </div>
    <div class="clear"></div>
</div>';
        return $html;
    }
}