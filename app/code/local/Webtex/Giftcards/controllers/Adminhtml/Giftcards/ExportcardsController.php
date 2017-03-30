<?php
/*
 * Webtex Gift Cards
 *
 * (C) Webtex Software 2015
 *
 */
class Webtex_Giftcards_Adminhtml_Giftcards_ExportcardsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Gift Cards Export'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/giftcards/export_cards');
        $this->_addContent($this->getLayout()->createBlock('giftcards/adminhtml_exportcards'));
        $this->renderLayout();
    }

    public function saveExportAction()
    {
        $form_data   = $this->getRequest()->getParams();
        $export_type = isset($form_data['export_type']) ? $form_data['export_type'] : 'csv';
        $file_path   = isset($form_data['file_path']) ? $form_data['file_path'] : '/var/export/giftcards';
        $delimiter   = isset($form_data['delimiter']) ? $form_data['delimiter'] : ';';
        $enclosure   = isset($form_data['enclosure']) ? $form_data['enclosure'] : '"';
        $date_from   = isset($form_data['date_from']) ? $form_data['date_from'] : null;
        $date_to     = isset($form_data['date_to'])   ? $form_data['date_to']   : null;
        $card_type   = isset($form_data['card_type']) ? (int) $form_data['card_type'] : 0;
        $card_status = isset($form_data['card_status']) ? (int) $form_data['card_status'] : 3;
//        $include_orders = isset($form_data['include_orders']) ? true : false;

        
        $collection = Mage::getModel('giftcards/giftcards')->getCollection();

        if($date_from){
            $collection->addFieldToFilter('main_table.created_time', array('gteq' => date('Y-m-d 00:00:00', strtotime($date_from))));
        };

        if($date_to){
            $collection->addFieldToFilter('main_table.created_time', array('lteq' => date('Y-m-d 00:00:00', strtotime($date_to))));
        };

        if($card_type != 0) {
            $collection->addFieldToFilter('card_type', array('eq' => $card_type));
        }

        if($card_status < 3) {
            $collection->addFieldToFilter('card_status', array('eq' => $card_status));
        }
/*
        if($include_orders) {
            $collection->getSelect()->joinLeft(array('orders' => $collection->getTable('giftcards/order')),
                                               'card_id = orders.id_giftcard',
                                               array('discounted' => 'discounted', 'shipping_discount' => 'shipping_discount', 'order_id' => 'id_order'));
            $collection->getSelect()->joinLeft(array('real_orders' => $collection->getTable('sales/order')),
                                               'entity_id = orders.id_order',
                                               array('increment_id' => 'increment_id'));
            $collection->getSelect()->group('card_id');
        }
*/
        try {
            $io = new Varien_Io_File();
            $fullPath = Mage::getBaseDir() . $file_path .'.'. $export_type;
            $parts = pathinfo($fullPath);

            $io->open(array('path' => $parts['dirname']));
            $io->streamOpen($fullPath, 'w+');
            $io->streamLock(true);

            if($export_type == 'xml'){
                $data = $collection->toXml();
                $io->streamWrite($data);
            } else {
                $header = array('card_id' => 'Card ID',
                                'card_code' => 'Card Code',
                                'card_amount' => 'Card Amount',
                                'card_balance' => 'Card Balance',
                                'card_currency' => 'Card Currency',
                                'card_type' => 'Card Type',
                                'card_status' => 'Card Status',
                                'mail_from' => 'Mail From',
                                'mail_to' => 'Mail To',
                                'mail_to_email' => 'User Email',
                                'mail_message' => 'Message',
                                'offline_country' => 'Country',
                                'offline_state' => 'State',
                                'offline_sity' => 'Sity',
                                'offline_street' => 'Street',
                                'offline_zip' => 'Zip',
                                'offline_phone' => 'Phone',
                                'customer_id' => 'Customer ID',
/*
                                'increment_id' => 'Order #',
                                'discounted' => 'Discount',
                                'shipping_discount' => 'Shipping Discount',
*/
                              'created_time' => 'Created Date',
                    );

                $io->streamWriteCsv($header, $delimiter, $enclosure);

                $content = array();
                foreach($collection as $item){
                    $content['card_id']           = $item['card_id'];
                    $content['card_code']         = $item['card_code'];
                    $content['card_amount']       = $item['card_amount'] ;
                    $content['card_balance']      = $item['card_balance'];
                    $content['card_currency']     = $item['card_currency'];
                    $content['card_type']         = $item['card_type'];
                    $content['card_status']       = ((int)$item['card_status'] == 0) ? 'Inactive' : ((int)$item['card_status'] == 1) ? 'Active' : 'Used';
                    $content['mail_from']         = $item['mail_from'];
                    $content['mail_to']           = $item['mail_to'];
                    $content['mail_to_email']     = $item['mail_to_email'];
                    $content['mail_message']      = $item['mail_message'];
                    $content['offline_country']   = $item['offline_country'];
                    $content['offline_state']     = $item['offline_state'];
                    $content['offline_sity']      = $item['offline_sity'];
                    $content['offline_street']    = $item['offline_street'];
                    $content['offline_zip']       = $item['offline_zip'];
                    $content['offline_phone']     = $item['offline_phone'];
                    $content['customer_id']       = $item['customer_id'];
                    /*
                      $content['increment_id']      = $item['increment_id'];
                      $content['discounted']        = $item['discounted'];
                      $content['shipping_discount'] = $item['shipping_discount'];
                    */
                    $content['created_time']      = $item['created_time'];

                    $io->streamWriteCsv($content, $delimiter, $enclosure);
                }

            }

            $io->streamUnlock();
            $io->streamClose();
        
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Gift Cards succesfully exported"));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return true;
    }


}