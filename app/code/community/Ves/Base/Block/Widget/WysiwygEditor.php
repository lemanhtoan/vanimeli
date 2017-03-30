<?php
class Ves_Base_Block_Widget_WysiwygEditor extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element
{
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $storeId = 0;
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
        $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array(
                        'wysiwyg'                   => true,
                        'add_widgets'               => false,
                        'add_variables'             => false,
                        'add_images'                => true,
                        'encode_directives'         => true,
                        'document_base_url'         => $storeMediaUrl,
                        'store_id'                  => $storeId,
                        'add_directives'            => true,
                        'directives_url'            => Mage::getSingleton('adminhtml/url')->getUrl('*/cms_wysiwyg/directive'),
                        'files_browser_window_url'  => Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'),
                        'files_browser_window_width' => (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_width'),
                        'files_browser_window_height'=> (int) Mage::getConfig()->getNode('adminhtml/cms/browser/window_height')
                    )
                );

        $editor = new Varien_Data_Form_Element_Editor($element->getData());

        //$editor->setData("config", $config);
        // Prevent foreach error
        $editor->getConfig()->setPlugins(array());

        $editor->setId($element->getId());
        $editor->setForm($element->getForm());
        $editor->setWysiwyg(true);
        $editor->setForceLoad(true);
        $editor->setValue(base64_decode($editor->getValue()));

        return parent::render($editor).'<script type="text/javascript>turnoffTinyMCEs.push(function(){ tinyMceEditors.get("'.$element->getId().'").turnOff();});getContentTinyMCEs.push(function(){ return (typeof tinyMCE != \'undefined\') && tinyMCE.get("'.$element->getId().'")? tinyMCE.get("'.$element->getId().'").getContent({format : "raw"}):document.getElementById("'.$element->getId().'").value;});getTinyMCEFields.push(function(){ return "'.$element->getName().'";});</script>';
    }
}