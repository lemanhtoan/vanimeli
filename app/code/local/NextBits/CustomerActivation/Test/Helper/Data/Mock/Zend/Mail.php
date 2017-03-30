<?php
class NextBits_CustomerActivation_Test_Helper_Data_Mock_Zend_Mail
    extends Zend_Mail
{
    protected $_sendCount = 0;

    public function send($transport = null)
    {
        $transport;
        $this->_sendCount++;
        return $this;
    }

    public function getSendCount()
    {
        return $this->_sendCount;
    }

    public function setSendCount($count)
    {
        $this->_sendCount = (int) $count;
    }
}