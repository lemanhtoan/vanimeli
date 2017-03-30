<?php

class Ves_ProductCarousel_Block_System_Config_Form_Field_Information  extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5" class="ves-description">
					   <h3>	<a href="http://www.venustheme.com"><b>Magento - Venus Product Carousel Block</b></a>  </h3>
								The most elegant way to show list products of your magento store inside the smooth ProductCarousel. 
								the module supports multiple themes for fitting your design, easy to make owner themes by yourself, 
								and many kind of selecting products sources. 
								When you used, sure you will get highest effects while introducing your customers great products, featured products .<br>
							<br>
							
							<h4><b>Guide</b></h4>
							<ul>
								<li><a href="http://www.venustheme.com"> 1) Forum Support</a></li>
								<li><a href="http://www.venustheme.com"> 2) Submit A Request</a></li>
								<li><a href="http://www.venustheme.com"> 3) Submit A Ticket</a></li>
							</ul>
							<div>
								<h4>How to implement</h4>
								<ul>
									<li>&lt block type="venustheme_productcarousel/list" name="productcarousel" /&gt</li>
									<li>{{block type="venustheme_productcarousel/list" name="productcarousel"}}</li>
								</ul>
								
							</div>
							<br>
							<div style="font-size:11px">@Copyright: <i><a href="http://www.venustheme.com" target="_blank">VenusTheme.Com</a></i></div>
					   </td></tr>', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
?>