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
 * @package     Magestore_RewardPointsReferFriends
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpointsreferfriends Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_Rewardpointsreferfriends_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'rewardpointsreferfriends';
        $this->_controller = 'adminhtml_rewardpointsreferfriends';
        
        $this->_updateButton('save', 'label', Mage::helper('rewardpointsreferfriends')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('rewardpointsreferfriends')->__('Delete'));
        
        $this->_addButton('saveandcontinue', array(
            'label'        => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'    => 'saveAndContinueEdit()',
            'class'        => 'save',
        ), -100);

        $this->_formScripts[] = "
			Event.observe(window, 'load', function(){toggleSimpleAction();});
            function toggleSimpleAction(){
                if ($('offer_commission_action').value == '1') {
                    $('offer_money_step').up(1).hide();
//                    $('offer_max_points_earned').up(1).hide();
                    $('offer_qty_step').up(1).hide();
                } else if ($('offer_commission_action').value == '2') {
                    $('offer_money_step').up(1).show();
                    $('offer_qty_step').up(1).hide();
//                    $('rule_max_points_earned').up(1).show();
                } else {
                    $('offer_qty_step').up(1).show();
                    $('offer_money_step').up(1).hide();
//                    $('rule_max_points_earned').up(1).show();
                }
            }
				
            function toggleEditor() {
                if (tinyMCE.getInstanceById('rewardpointsreferfriends_content') == null)
                    tinyMCE.execCommand('mceAddControl', false, 'rewardpointsreferfriends_content');
                else
                    tinyMCE.execCommand('mceRemoveControl', false, 'rewardpointsreferfriends_content');
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    
    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('offer_data')
            && Mage::registry('offer_data')->getId()
        ) {
            return Mage::helper('rewardpointsreferfriends')->__("Edit Special Offer '%s'",
                                                $this->htmlEscape(Mage::registry('offer_data')->getTitle())
            );
        }
        return Mage::helper('rewardpointsreferfriends')->__('Add Special Offer');
    }
}	