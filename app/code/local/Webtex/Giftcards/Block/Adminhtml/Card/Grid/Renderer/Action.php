<?php

class Webtex_Giftcards_Block_Adminhtml_Card_Grid_Renderer_Action extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $printHref = $this->getUrl('*/*/print', array('id' => $row->getCardId()));
        $resendHref = ($row->getCardType() == 'email' && strlen($row->getMailToEmail())) ? $this->getUrl('*/*/resend', array('id' => $row->getCardId())) : '';
        //$deleteHref = $this->getUrl('*/*/delete', array('id' => $row->getCardId()));
        $html  = '<a href="'.$printHref.'" target="_blank">'.$this->__('Print').'</a>';
        if(strlen($resendHref)){
            $html .= ' <a href="'.$resendHref.'">'.$this->__('Resend').'</a>';
        }
        $html .= ' <a onclick="confirmSetLocation(\'Are you sure that you want delete Gift Card?\',\''.$this->getUrl('*/*/delete', array('id' => $row->getCardId())).'\')">'.$this->__('Delete').'</a>';
        return $html;
    }
}
