<?php

class Magestore_AffiliateplusReferFriend_Model_System_Config_Source_Dynamiccomment_Comment extends Mage_Core_Model_Config_Data {

    public function getCommentText(Mage_Core_Model_Config_Element $element, $currentValue) {
        if(((string)$element->source_model)=='affiliateplusreferfriend/system_config_source_parametervalue'){
        $result = "<p class='note' id='dynamic_comment'></p>";
        $result .= "<script type='text/javascript'>
            function update_commment_content()
            {
             var comment = $('dynamic_comment');
             var content = 42;
             var param = $('affiliateplus_general_url_param').getValue();
             if(param=='')param = 'acc';
             if($('affiliateplus_general_url_param_value').getValue()==1)var content = 'cfcd208495d565ef66e7dff9f98764da' ;
                comment.innerHTML = 'Ex: " . Mage::getUrl() . "?'+ param + '=' + content;        
            }

            function init_comment()
            {
              update_commment_content();
                $('affiliateplus_general_url_param_value').observe('change', function(){
                update_commment_content();
                });
                $('affiliateplus_general_url_param').observe('change', function(){
                update_commment_content();
                });
            }
            document.observe('dom:loaded', function(){init_comment();});
            </script>";
        return $result;
    }
    }

}
