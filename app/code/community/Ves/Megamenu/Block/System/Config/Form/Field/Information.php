<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Renderer for lable in fieldset
 *
 * @author LandOfCoder <venustheme@gmail.com>
 */
class Ves_Megamenu_Block_System_Config_Form_Field_Information  extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf(' 	
    	<ul>
    	     <li>+ <a target="_blank" href="http://www.venustheme.com">Detail Information</a></li><a target="_blank" href="http://venustheme.com/prestashop/slider/lof-carousel.html">
             </a><li><a target="_blank" href="http://venustheme.com/">+ </a><a target="_blank" href="http://venustheme.com/supports/forum.html?id=87">Forum support</a></li>
             <li>+ <a target="_blank" href="http://www.venustheme.com/">Customization/Technical Support Via Email.</a></li>
             <li>+ <a target="_blank" href="http://www.venustheme.com/">UserGuide </a></li>
        </ul>
        <br>
        @Copyright: <a href="http://wwww.venustheme.com">VenusTheme.Com</a>
    ',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
?>