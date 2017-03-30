<?php

class Webtex_Giftcards_Model_Logger extends Mage_Core_Model_Abstract
{
    protected $_data;

    protected function _construct()
    {
        $this->_init('giftcards/logger');
        parent::_construct();
    }
    
    public function writelog($data)
    {
        if(!is_array($data)){
            return;
        }
        
        $this->_data = $data;
        
        foreach($this->_data as $key => $value){
            $this->setData($key, $value);
        }
        
        $this->_beforeSave();
        $this->setId(null);
        $this->save();
    }
    
    public function clearLogs()
    {
        $tablePrefix = (string) Mage::getConfig()->getTablePrefix();
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $query = "delete from ".$tablePrefix."giftcards_log";
        $write->query($query);
        return;
    }


    protected function _beforeSave()
    {
        if(isset($this->_data['$action'])){
            $action = $this->_data['action'];
            $user = isset($this->_data['user']) ? $this->_data['user'] : '';
            $userName = isset($this->_data['user_name']) ? $this->_data['user_name'] : '';
            $orderId = isset($this->_data['order_id']) ? $this->_data['order_id'] : '';
            
            if ($action == 'Created') {
                $comment = 'Gift Card was created by '. $user .' ' .$userName;
            } elseif ($action == 'Used') {
                $comment = 'Gift Card was used in order #'. $orderId .' by '. $user .' ' .$userName;
            } elseif ($action == 'Updated') {
                $comment = 'Gift Card was updated by '. $user .' ' .$userName;
            } elseif ($action == 'Refund') {
                $comment = 'Gift Card was refunded in order #'.$orderId.' by '. $user .' ' .$userName;
            } elseif ($action ==  'Bulk') {
                $comment = 'Gift Card was created in bulk by '. $user .' ' .$userName;
            } else {
                $comment = '';
            }
        
            $this->setCardComment($comment);
        }
    }
}
