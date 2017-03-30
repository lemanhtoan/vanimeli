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
 * Rewardpointsreferfriends Adminhtml Button Block
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReferFriends
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReferFriends_Block_Adminhtml_System_Config_Form_Field_Button extends Mage_Adminhtml_Block_System_Config_Form_Field {

    /**
     * 
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {

        $this->setElement($element);
        $html = $this->getLayout()
                ->createBlock('adminhtml/widget_button', '', array(
                    'type' => 'button',
                    'label' => Mage::helper('adminhtml')->__('Insert policy variable...'),
                    'onclick' => 
                    '
                        function insertText(element)
                        {
//                        var share_message=$(\'rewardpoints_referfriendplugin_sharing_message_for_link\');
var text=\'{{policy_description}}\';                        
if(element.selectionStart)
                        {
                            
                            var startPos = element.selectionStart;
                            var endPos = element.selectionEnd;
                            var scrollTop = element.scrollTop;
                            element.value = element.value.substring(0, startPos) + text + element.value.substring(endPos, element.value.length);
                            element.focus();
                            element.selectionStart = startPos + text.length;
                            element.selectionEnd = startPos + text.length;
                            element.scrollTop = scrollTop;
                        }
                        else element.value+=text;
                        }
                        
                     re=/{{policy_description}}/;
                     if($(\'rewardpoints_referfriendplugin_refer_method\').value==\'link\')
                     {
//                        if(!re.test($(\'rewardpoints_referfriendplugin_sharing_message_for_link\').value))
//                        {
//                          var str=$(\'rewardpoints_referfriendplugin_sharing_message_for_link\').value;
//                          $(\'rewardpoints_referfriendplugin_sharing_message_for_link\').value=str.replace(\':\',\':{{policy_description}}\');
//             
//                        }
//                      else alert(\'The Policy is inserted\');
                        insertText($(\'rewardpoints_referfriendplugin_sharing_message_for_link\'));
                            
                     }
                     else if($(\'rewardpoints_referfriendplugin_refer_method\').value==\'coupon\')
                     {
//                         if(!re.test($(\'rewardpoints_referfriendplugin_sharing_message_for_coupon\').value))
//                        {
//                            var str=$(\'rewardpoints_referfriendplugin_sharing_message_for_coupon\').value;
//                          $(\'rewardpoints_referfriendplugin_sharing_message_for_coupon\').value=str.replace(\':\',\':{{policy_description}}\');
//                        }
//                        else alert(\'The Policy is inserted\');
                        insertText($(\'rewardpoints_referfriendplugin_sharing_message_for_coupon\'));
                     }
                      else {
//                      if(!re.test($(\'rewardpoints_referfriendplugin_sharing_message_for_both\').value))
//                        {
//                          var str=$(\'rewardpoints_referfriendplugin_sharing_message_for_both\').value;
//                          $(\'rewardpoints_referfriendplugin_sharing_message_for_both\').value=str.replace(\':\',\':{{policy_description}}\');
//                        }
//                        else alert(\'The Policy is inserted\');
                        insertText($(\'rewardpoints_referfriendplugin_sharing_message_for_both\'));
                      }
                        
                            ',
                ))
                ->toHtml();

        return $html;
    }

}
