<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Class Magestore_RewardPoints_Block_Adminhtml_System_Config_Form_Field_Tooffers
 */
class Magestore_RewardPoints_Block_Adminhtml_System_Config_Form_Field_Tooffers extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $fieldConfig = $element->getFieldConfig();
        $htmlId = $element->getHtmlId();
        $html = "<tr id='row_$htmlId'><td class='label' colspan='3'>";
        $html .= '<div style="font-weight: bold;">';
        $html .= $element->getLabel(); 
        $html .= "  <a href='".Mage::helper("adminhtml")->getUrl("*/reward_rewardpointsreferfriends/index")."'>".$this->__('Manage Special Offers')."</a>";
        $html .= '</div></td></tr>';


        return $html;
    }

}
