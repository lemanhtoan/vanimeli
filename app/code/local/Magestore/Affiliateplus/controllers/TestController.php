<?php
class Magestore_Affiliateplus_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction(){
//        $transaction = Mage::getModel('affiliateplus/transaction')->load(43);
//        
//        $transaction->setCommission(100)
//                        ->setDiscount(100)
//                        ->save();
//        print_r($transaction->getData());die('z');
//        $abc = implode(',', array(2));
//        print_r($abc); die('z');
//        $installer = new Mage_Core_Model_Resource_Setup();
//        $installer->startSetup();
//        $installer->getConnection()->addColumn($installer->getTable('affiliateplus/account'), 'refer_by_email', 'varchar(255) default ""');
//
//        $installer->endSetup();
//        $order = Mage::getModel('sales/order')->load(214);
//    
//        foreach($order->getAllItems() as $item) {
//            
//                    $sfoi = Mage::getSingleton('core/resource')->getTableName('sales/order_item');
//                    $sfvi = Mage::getSingleton('core/resource')->getTableName('sales/invoice_item');
//                    $collection = Mage::getModel('sales/order_item')->getCollection();
//                    $collection->getSelect()
//                            ->join($sfvi, $sfvi.'.order_item_id = main_table.item_id', array('invoice_qty'=>$sfvi.'.qty', 'affiliateplus_commission_flag' => 'affiliateplus_commission_flag'))
//                            ->where($sfvi.'.affiliateplus_commission_flag = 0')
//                            ->where('main_table.item_id = '.$item->getId())
//                            ;
//                    $collection->printlogquery(true);
//                    die('x');
//        }
    }
}
        