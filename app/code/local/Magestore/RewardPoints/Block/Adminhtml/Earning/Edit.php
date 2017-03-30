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

/**
 * Rewardpoints Earning Rate Edit Block
 * 
 * @category     Magestore
 * @package     Magestore_RewardPoints
 * @author      Magestore Developer
 */
class Magestore_RewardPoints_Block_Adminhtml_Earning_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    /**
     * Magestore_RewardPoints_Block_Adminhtml_Earning_Edit constructor.
     */
    public function __construct() {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'rewardpoints';
        $this->_controller = 'adminhtml_earning';

        $this->_updateButton('save', 'label', Mage::helper('rewardpoints')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('rewardpoints')->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
                ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            Event.observe(window, 'load', function(){
                if($('use_level')){
                    hiddenLoyaltyLevel();
                }
            });
            function hiddenLoyaltyLevel(){
                if($('use_level').value==1){
                    $('level_id').up('tr').show();
                }
                else  $('level_id').up('tr').hide();
            }
        ";
    }

    /**
     * get text to show in header when edit an item
     *
     * @return string
     */
    public function getHeaderText() {
        if (Mage::registry('rate_data') && Mage::registry('rate_data')->getId()
        ) {
            return Mage::helper('rewardpoints')->__("Edit Earning Rate #%s", Mage::registry('rate_data')->getId()
            );
        }
        return Mage::helper('rewardpoints')->__('Add Earning Rate');
    }

}
