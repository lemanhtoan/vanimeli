<?php

class NWT_Languagepack_Adminhtml_AboutusController extends Mage_Adminhtml_Controller_action
{

    public function indexAction()
    {
        $this->loadLayout();

        //create a text block with the name of "example-block"
        $block = $this->getLayout()
        ->createBlock('core/text', 'example-block')
        ->setText('<div class="content_aboutus"><span>About Nordic Web Team</span><div class="content_data"><div class="image_holder"></div>Östermalmsgatan 21, Stockholm</br><em>Telefon</em>08-518 172 00</br><em>E-Post</em><a target="_blank" href="http://nordicwebteam.se/om-oss/kontakt/">info@nordicwebteam.se</a></div></div>');

        $this->_addContent($block);

        $this->renderLayout();
    }

}