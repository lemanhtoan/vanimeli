<?php

class Ves_Layerslider_Block_System_Config_Form_Field_Information  extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
	public function render(Varien_Data_Form_Element_Abstract $element)
    {
	    
	
      
		
        $useContainerId = $element->getData('use_container_id');
        return  sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5" class="ves-description">
					   <h3>	 Venustheme Layerslider Module </h3>
							<h4><b>Guide</b></h4>
							<ul>
								<li><a href="http://www.venustheme.com/"> 1) Forum Support</a></li>
								<li><a href="http://www.venustheme.com/"> 2) Submit A Request</a></li>
								<li><a href="http://www.venustheme.com/"> 3) Submit A Ticket</a></li>
							</ul>
							<br>
							<div style="font-size:11px">@Copyright: <i><a href="http://www.venustheme.com" target="_blank">VenusTheme.Com</a></i></div>
					   </td></tr>', $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
?>