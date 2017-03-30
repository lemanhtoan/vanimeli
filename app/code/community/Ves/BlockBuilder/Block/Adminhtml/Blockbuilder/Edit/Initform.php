<?php
class Ves_BlockBuilder_Block_Adminhtml_Blockbuilder_Edit_Initform extends Mage_Adminhtml_Block_Widget_Container
{

    public function getBlockData()
    {
        return Mage::registry('block_data');
    }
}
