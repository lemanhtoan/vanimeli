<?php


class Ves_BlockBuilder_Block_Adminhtml_Listwidgets extends Mage_Core_Block_Template
{

    public function __construct($attributes = array())
    {
        $widget_buttons = Mage::getModel("ves_blockbuilder/widget")->loadWidgetButtons();

        $widget_groups = isset($widget_buttons['groups'])?$widget_buttons['groups']: array();
        $widgets = isset($widget_buttons['widgets'])?$widget_buttons['widgets']: array();

        $this->assign("groups", $widget_groups);
        $this->assign("widgets", $widgets);

        $this->setTemplate("ves_blockbuilder/edit/list_widgets.phtml");

        parent::__construct();

    }

}