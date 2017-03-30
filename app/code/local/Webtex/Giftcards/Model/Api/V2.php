<?php
/*
 * Webtex Gift Cards
 *
 * Gift Card API
 *
 * (C) WebtexSoftware 2015
 *
 */

class Webtex_Giftcards_Model_Api_V2 extends Mage_Catalog_Model_Api_Resource 
{

    public function cardlist($customerId, $websiteId = 0)
    {
        $collection = Mage::getModel('giftcards/giftcards')->getCollection();

        $result = array();
        
        foreach($collection as $item){
            $result[] = $item->toArray();
        }
        
        return $result;
    }

    public function getcard($card_code)
    {
        $collection = Mage::getModel('customerprices/prices')->getCollection()
            ->addFieldToFilter('card_code', trim($card_code));

        $result = array();
        
        foreach($collection as $item){
            $result[] = $item->toArray();
        }
        
        return $result;
    }

    public function create($card_code = "", $card_amount, $card_currency, $card_type = "email", $card_status = 0, $websiteId = 0)
    {
        if($card_code != "") {
            $model = Mage::getModel('giftcards/giftcards')->setFieldToFilter('card_code', $card_code);

            if($model && $model->getId()){
                return;
            }
        }
       
        $model = Mage::getModel('giftcards/giftcards');

        $data = array();

        $data['card_code']     = $card_code;
        $data['card_amount']   = $card_amount;
        $data['card_currency'] = strlen($card_currency) ? strtoupper(substr($card_currency,0,3)) : "";
        $data['card_type']     = $card_type;
        $data['card_status']   = $card_status;
        $data['website_id']    = $websiteId;

        $model->setData($data);
       
        try {
            $model->save();
        } 
        catch (Exception $e) {
            return ;
        }

        return array('result' => $model->getData());
    }

    public function update($card_code, $card_balance = null, $card_status = null, $card_type = null, $mail_from = null, $mail_to = null, $mail_to_email = null, $mail_message = null,
                           $offline_country = null, $offline_state = null, $offline_sity = null, $offline_street = null, $offline_zip = null, $offline_phone = null,
                           $mail_delivery_date = null, $card_currency = null, $websiteId = 0, $date_end = null)
    {
        $model = Mage::getModel('giftcards/giftcards')->load($card_code, 'card_code');

        if(!$model || !$model->getId()){
            return array('result' => Mage::getModel('giftcards/giftcards')->getData());
        }

        $id = $model->getId();

        $data = array();

        if($card_balance) {
            $data['card_balance'] = $card_balance;
        }

        if($card_status) {
            $data['card_status'] = $card_status;
        }

        if($card_type) {
            $data['card_type'] = $card_type;
        }

        if($mail_from) {
            $data['mail_from'] = $mail_from;
        }

        if($mail_to) {
            $data['mail_to'] = $mail_to;
        }

        if($mail_to_email) {
            $data['mail_to_email'] = $mail_to_email;
        }

        if($mail_message) {
            $data['mail_message'] = $mail_message;
        }

        if($offline_country) {
            $data['offline_country'] = $offline_country;
        }

        if($offline_state) {
            $data['offline_state'] = $offline_state;
        }

        if($offline_sity) {
            $data['offline_sity'] = $offline_sity;
        }

        if($offline_street) {
            $data['offline_street'] = $offline_street;
        }

        if($offline_zip) {
            $data['offline_zip'] = $offline_zip;
        }

        if($offline_phone) {
            $data['offline_phone'] = $offline_phone;
        }

        if($mail_delivery_date) {
            $data['mail_delivery_date'] = $mail_delivery_date;
        }

        if($card_currency) {
            $data['card_currency'] = $card_currency;
        }

        if($websiteId) {
            $data['website_id'] = $websiteId;
        }

        if($date_end) {
            $data['date_end'] = $date_end;
        }


        $model->setData($data);
        if(isset($id)){
            $model->setId($id);
        }
       
        try {
            $model->save();
        } 
        catch (Exception $e) {
            return array('result' => Mage::getModel('giftcards/giftcards')->getData());
        }

        return array('result' => $model->getData());
    }

    public function delete($card_code)
    {
        $model = Mage::getModel('giftcards/giftcards')->load($card_code, 'card_code');
  
        try {
            $model->delete();
        } 
        catch (Exception $e) {
            return false;
        }
        return true;
    }

}