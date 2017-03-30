<?php
class NWT_Languagepack_Block_Adminhtml_Aboutus extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_aboutus';
    $this->_blockGroup = 'languagepack';

    parent::__construct();
  }
}