<?php

class Magestore_Affiliateplus_Model_Transaction extends Mage_Core_Model_Abstract {

    const XML_PATH_ADMIN_EMAIL_IDENTITY = 'trans_email/ident_general';
    const XML_PATH_EMAIL_IDENTITY = 'trans_email/ident_sales';
    const XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/new_transaction_account_email_template';
    const XML_PATH_NEW_TRANSACTION_SALES_EMAIL = 'affiliateplus/email/new_transaction_sales_email_template';
    const XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL = 'affiliateplus/email/updated_transaction_account_email_template';
    const XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL = 'affiliateplus/email/reduce_commission_account_email_template';
    const XML_PATH_SENT_MAIL_REFUND = 'affiliateplus/email/sent_mail_refund_email_template';

    /**
     * 
     */
    public function _construct() {

        parent::_construct();
        $this->_init('affiliateplus/transaction');
    }

//    public function complete(){
//        if ($this->canRestore()) return $this;
//    	if (!$this->getId()) return $this;
//    	if ($this->getStatus() != '2') return $this;
//    	// Add commission for affiliate account
//    	$account = Mage::getModel('affiliateplus/account')
//    		->setStoreId($this->getStoreId())
//    		->load($this->getAccountId());
//    	try {
//			$commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
//    		$account->setBalance($account->getBalance() + $commission)//$this->getCommission())
//    			->save();
//				
//    		$this->setStatus('1')->save();
//			
//			//update balance tier affiliate
//			Mage::dispatchEvent('affiliateplus_complete_transaction',array('transaction' => $this));
//			
//	    	// Send email to affiliate account
//	    	$this->sendMailUpdatedTransactionToAccount(true);
//    	} catch (Exception $e){
//    		
//    	}
//    	return $this;
//    }

    /**
     * get Config Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    protected function _getConfigHelper() {
        return Mage::helper('affiliateplus/config');
    }

    
    /**
     * Changed By Adam to solve the problem of invoice tung phan 20/08/2014
     * @return \Magestore_Affiliateplus_Model_Transaction
     */
    public function complete() {
        
        if ($this->canRestore()) {
            return $this;
        }
        if (!$this->getId()) {
            return $this;
        }
        
        //Adam: fix bug cancel transaction but commission is still added to affiliate
        if($this->getStatus() == 3)
            return $this;
        
        // get affiliate account from transaction (account_id)
        $account = Mage::getModel('affiliateplus/account')
                ->setStoreId($this->getStoreId())
                ->load($this->getAccountId());

        if ($this->getStatus() != 1) {

            $additionalCommission = $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
            try {
//                $account->setBalance($account->getBalance()+$additionalCommission)->save();
                // Fixed cho may Kelly Dao
                if ($additionalCommission)
                    $account->setBalance($account->getData('balance') + $additionalCommission)->save();
            } catch (Exception $e) {
                
            }
        }

        // Get Order from transaction (order_id)
        $order = Mage::getModel('sales/order')->load($this->getOrderId());
        // Changed By Adam: Fix loi chua khai bao bien $storeId
        $storeId = $this->getStoreId();
        try {
            $commission = 0;
            $transactionCommission = 0;
            $transactionDiscount = 0;
            $status = $this->getStatus();
            $configOrderStatus = $this->_getConfigHelper()->getCommissionConfig('updatebalance_orderstatus', $storeId);
            $configOrderStatus = $configOrderStatus ? $configOrderStatus : 'processing';

            if ($configOrderStatus == 'complete') {
                // Check if transaction is not completed
                if ($this->getStatus() != 1) {    // Changed By Adam to solve the problem
                    foreach ($order->getAllItems() as $item) {
                        if ($item->getAffiliateplusCommission()) {
                            $affiliateplusCommissionItem = explode(",", $item->getAffiliateplusCommissionItem());

                            $totalComs = array_sum($affiliateplusCommissionItem);
							$totalComs = $totalComs ? $totalComs : $item->getAffiliateplusCommission();
                            $firstComs = $affiliateplusCommissionItem[0];
							if($firstComs) {
								$commission += $firstComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
							} else {
								$commission += $item->getAffiliateplusCommission();
							}

                            $transactionCommission += $totalComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                        }
                        //update tier commission to tier affiliate when partial invoice
                        Mage::dispatchEvent('update_tiercommission_to_tieraffiliate_partial_invoice', array('transaction' => $this, 'item' => $item, 'invoice_item' => ''));
                    }
                }
            } else {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getAffiliateplusCommission()) {
                        $collection = Mage::getModel('sales/order_invoice_item')->getCollection();
                        $collection->getSelect()
                                ->where('affiliateplus_commission_flag = 0')
                                ->where('order_item_id = ' . $item->getId())
                        ;

                        $affiliateplusCommissionItem = explode(",", $item->getAffiliateplusCommissionItem());

                        $totalComs = array_sum($affiliateplusCommissionItem);
						$totalComs = $totalComs ? $totalComs : $item->getAffiliateplusCommission();
                        $firstComs = $affiliateplusCommissionItem[0];
						
                        if ($collection->getSize()) {


                            foreach ($collection as $invoiceItem) {
                                if ($invoiceItem && $invoiceItem->getId()) {
                                    if($firstComs) 
										$commission += $firstComs * $invoiceItem->getQty() / $item->getQtyOrdered();
									else 
										$commission += $item->getAffiliateplusCommission();

                                    $invoiceItem->setAffiliateplusCommissionFlag(1)->save();

                                    //update tier commission to tier affiliate when partial invoice
                                    Mage::dispatchEvent('update_tiercommission_to_tieraffiliate_partial_invoice', array('transaction' => $this, 'item' => $item, 'invoice_item' => $invoiceItem));
                                }
                            }
                        }
                        // check if doesn't subtract commission from affiliate account balance when credit memo is created
                        if (!Mage::helper('affiliateplus/config')->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
                            $transactionCommission += $totalComs * ($item->getQtyInvoiced()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced()) / $item->getQtyOrdered();
                        } else {
                            $transactionCommission += $totalComs * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                            $transactionDiscount += $item->getBaseAffiliateplusAmount() * ($item->getQtyInvoiced() - $item->getQtyRefunded()) / $item->getQtyOrdered();
                        }
                    }
                }
            }
            $commission = $commission ? $commission : $this->getCommission();
            if ($commission) {
                $status = 1;
                $account->setBalance($account->getData('balance') + $commission)
                        ->save();
                if ($transactionCommission) {
                    $this->setCommission($transactionCommission);
                }
                if ($transactionDiscount) {
                    if ($transactionDiscount <= 0)
                        $this->setDiscount(0);
                    else
                        $this->setDiscount(-$transactionDiscount);
                }
                $this->setStatus($status)->save();

                if ($transactionCommission) {

                    //update tiercommission to affiliatepluslevel_transaction table
                    Mage::dispatchEvent('update_tiercommission_to_transaction_partial_invoice', array('transaction' => $this, 'order' => $order));

                    //Update commission to affiliateplusprogram_transaction table
                    Mage::dispatchEvent('update_commission_to_affiliateplusprogram_transaction_partial_invoice', array('transaction' => $this, 'order' => $order));
                }

                //update balance tier affiliate
                //                Mage::dispatchEvent('affiliateplus_complete_transaction',array('transaction' => $this, 'order'=>$order));
                // Send email to affiliate account
                $this->sendMailUpdatedTransactionToAccount(true);
            }
            
        } catch (Exception $e) {
            print_r($e->getMessage());
            die('z');
        }

        return $this;
    }

    /**
     * De lai cho Jack 
     */
//    public function complete() {
//        if ($this->canRestore())
//            return $this;
//        if (!$this->getId())
//            return $this;
//        // Add commission for affiliate account
//        $account = Mage::getModel('affiliateplus/account')
//                ->setStoreId($this->getStoreId())
//                ->load($this->getAccountId());
//        $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
//        try {
//            /* Fix bug commision - Edit By Jack */
//            $updatebalance_orderstatus = Mage::getStoreConfig('affiliateplus/commission/updatebalance_orderstatus');
//            if($updatebalance_orderstatus == 'processing'){ 
//                $newCommission = 0;   
//                $baseItemsPrice = 0;
//                $orderInvoiceId = Mage::app()->getRequest()->getParam('order_id');
//                $orderInvoiceInfo = Mage::getModel('sales/order')->load($orderInvoiceId);
//                foreach($orderInvoiceInfo->getAllVisibleItems() as $item){
//                    if ($item->getHasChildren() && $item->isChildrenCalculated() && $item->getBaseAffiliateplusAmount()) {
//                               foreach ($item->getChildrenItems() as $child) {
//                                   $baseItemsPrice += $item->getQtyOrdered() * ($child->getQtyOrdered() * $child->getBasePrice() - $child->getBaseDiscountAmount() - $child->getBaseAffiliateplusAmount());
//                               }
//                    } 
//                    elseif ($item->getProduct() && $item->getBaseAffiliateplusAmount()) {
//                               $baseItemsPrice += $item->getQtyOrdered() * $item->getBasePrice() - $item->getBaseDiscountAmount() - $item->getBaseAffiliateplusAmount();
//                    }
//                }
//                $postData =   Mage::app()->getRequest()->getPost();
//                 foreach($orderInvoiceInfo->getAllVisibleItems() as  $item){
//                    $itemToInvoice = $postData['invoice']['items'][$item->getId()]; 
//                    if($itemToInvoice > 0 && ($itemToInvoice <= $item->getQtyOrdered()) && $item->getBaseAffiliateplusAmount()){
//                         $newCommission +=  (($itemToInvoice*$item->getBasePrice()) - ($itemToInvoice/$item->getQtyOrdered())*$item->getBaseAffiliateplusAmount())*$commission/$baseItemsPrice;   
//                    }
//                 }
//            }
//            if ($this->getStatus() != '2' && $newCommission == 0)
//               return $this;
//            /* end Fix bug */
//           //Jack Update 27/07/2014
//             $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
//            if(!$orderId){ 
//                if($newCommission == 0 && $account->getStatus() == 1)
//                    $account->setBalance($account->getBalance() + $commission)//$this->getCommission())
//                            ->save();
//            }
//            if($newCommission > 0 && $account->getStatus() == 1){
//                     $account->setBalance($account->getBalance() + $newCommission)//$this->getCommission())
//                            ->save();
//            }
//            $this->setStatus('1')->save();
//
//            //update balance tier affiliate
//            Mage::dispatchEvent('affiliateplus_complete_transaction', array('transaction' => $this));
//
//            // Send email to affiliate account
//            $this->sendMailUpdatedTransactionToAccount(true);
//        } catch (Exception $e) {
//            
//        }
//        return $this;
//    }

    /**
     * 
     * @return \Magestore_Affiliateplus_Model_Transaction
     */
    public function hold() {
        if ($this->canRestore())
            return $this;
        if (!$this->getId())
            return $this;
        // Changed By Adam
        if ($this->getStatus() != '2')
            return $this;
        // Hold transaction 
        try {
            $this->setStatus('4')
                    ->setHoldingFrom(now())
                    ->save();
        } catch (Exception $e) {
            
        }
        return $this;
    }

    /**
     * 
     * @return \Magestore_Affiliateplus_Model_Transaction
     */
    public function unHold() {
        if ($this->canRestore())
            return $this;
        if (!$this->getId())
            return $this;
        if ($this->getStatus() != '4')
            return $this;
        // Un hold and complete transaction
        $this->setStatus('2')->complete();
        return $this;
    }

    
    /**
     * Changed By Adam to solve the problem of partial refund 26/08/2014
     * @param type $creditmemo
     * @return \Magestore_Affiliateplus_Model_Transaction
     */
    public function reduce($creditmemo) {
        if ($this->getStatus() == 1) {
            
            if ($this->canRestore()) {
                return $this;
            }
            //        edit by viet
            if (!$this->getId() || !$creditmemo->getId()) {
                return $this;
            }
            $reducedIds = explode(',', $this->getCreditmemoIds());
            if (is_array($reducedIds) && in_array($creditmemo->getId(), $reducedIds)) {
                return $this;
            }
            $reducedIds[] = $creditmemo->getId();
            // calculate reduced commission
            $reduceCommission = 0;              // Reduce commission for affiliate level 0
            $reduceTransactionCommission = 0;   // Reduce commission for transaction (all affiliate + tier affiliate)
            $reduceTransactionDiscount = 0;
            foreach ($creditmemo->getAllItems() as $item) {

                if ($item->getOrderItem()->isDummy()) {
                    continue;
                }

                // Calculate the reduce commission for affiliate
                if (!$item->getAffiliateplusCommissionFlag()) {
                    $orderItem = $item->getOrderItem();
                    if ($orderItem->getAffiliateplusCommission()) {
                        // Calculate the reduce commission for affiliate
                        $affiliateplusCommissionItem = explode(",", $orderItem->getAffiliateplusCommissionItem());
                        $firstComs = $affiliateplusCommissionItem[0];
                        $reduceCommission += $firstComs * $item->getQty() / $orderItem->getQtyOrdered();

                        // Calculate the reduce commission for transaction
                        $orderItemQty = $orderItem->getQtyOrdered();
                        $orderItemCommission = (float) $orderItem->getAffiliateplusCommission();
                        if ($orderItemCommission && $orderItemQty) {
                            $reduceTransactionCommission += $orderItemCommission * $item->getQty() / $orderItemQty;
                        }

                        $reduceTransactionDiscount += $orderItem->getBaseAffiliateplusAmount() * $item->getQty() / $orderItemQty;

                        $item->setAffiliateplusCommissionFlag(1)->save();

                        Mage::dispatchEvent('update_tiercommission_to_tieraffiliate_partial_refund', array(
                            'transaction' => $this,
                            'creditmemo_item' => $item,
                        ));
                    }
                }
            }

            if ($reduceCommission <= 0) {
                return $this;
            }
            // check reduced commission is over than total commission
            if ($reduceTransactionCommission > $this->getCommission()) {
                $reduceTransactionCommission = $this->getCommission();
            }

            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($this->getStoreId())
                    ->load($this->getAccountId());

            try {

                //          $commission = $reduceCommission + $this->getCommissionPlus() * $reduceCommission / $this->getCommission() + $reduceCommission * $this->getPercentPlus() / 100;
                //          
                $commission = $reduceCommission + $this->getCommissionPlus() * $reduceTransactionCommission / $this->getCommission() + $reduceTransactionCommission * $this->getPercentPlus() / 100;

                if ($commission) {
                    $account->setBalance($account->getData('balance') - $commission)
                            ->save();
                    //                $account->setBalance($account->getBalance() - $commission)
                    //                        ->save();
                }

                $creditMemoIds = implode(',', array_filter($reducedIds));
                $this->setCreditmemoIds($creditMemoIds);
                $this->setCommissionPlus($this->getCommissionPlus() - $this->getCommissionPlus() * $reduceTransactionCommission / $this->getCommission());
                $order = $creditmemo->getOrder();
               if ($reduceTransactionCommission) {
                    if ($this->getCommission() <= $reduceTransactionCommission && $order->getBaseSubtotal() == $order->getBaseSubtotalRefunded()) {
                        $this->setCommission(0)
                                ->setStatus(3)
                        ;
                    } else {
                        $this->setCommission($this->getCommission() - $reduceTransactionCommission);
                    }
                }

                if ($reduceTransactionDiscount) {
                    if ($this->getDiscount() > $reduceTransactionDiscount)
                        $this->setDiscount(0);
                    else
                        $this->setDiscount($this->getDiscount() + $reduceTransactionDiscount);
                }

                $this->save();


                if ($reduceTransactionCommission) {

                    // Update affiliateplusprogram transaction
                    Mage::dispatchEvent('update_affiliateplusprogram_transaction_partial_refund', array(
                        'transaction' => $this,
                        'creditmemo' => $creditmemo,
                    ));

                    // update balance for tier transaction
                    $commissionObj = new Varien_Object(array(
                        'base_reduce' => $reduceTransactionCommission,
                        'total_reduce' => $commission
                    ));
                    Mage::dispatchEvent('affiliateplus_reduce_transaction', array(
                        'transaction' => $this,
                        'creditmemo' => $creditmemo,
                        'commission_obj' => $commissionObj
                    ));

                    $reduceCommission = $commissionObj->getBaseReduce();        // Tong commission se bi tru di o transaction
                    $commission = $commissionObj->getTotalReduce();             // Tong commission se bi tru di khoi tai khoan customer
                    // Send email for affiliate account
                    $this->sendMailReduceCommissionToAccount($reduceCommission, $commission);
                }
            } catch (Exception $e) {
                print_r($e->getMessage());
                die('zzz');
            }
        }
        
        return $this;
    }

//    public function reduce($creditmemo) {
//        if ($this->canRestore())
//            return $this;
////        edit by viet
//        if (!$this->getId() || !$creditmemo->getId()) {
//            return $this;
//        }
//        $reducedIds = explode(',', $this->getCreditmemoIds());
//        if (is_array($reducedIds) && in_array($creditmemo->getId(), $reducedIds)) {
//            return $this;
//        }
//        $reducedIds[] = $creditmemo->getId();
//        // calculate reduced commission
//        $reduceCommission = 0;
//        foreach ($creditmemo->getAllItems() as $item) {
//            if ($item->getOrderItem()->isDummy()) {
//                continue;
//            }
//            $orderItem = $item->getOrderItem();
//            $orderItemCommission = (float) $orderItem->getAffiliateplusCommission();
//            $orderItemQty = $orderItem->getQtyOrdered();
//            if ($orderItemCommission && $orderItemQty) {
//                $reduceCommission += $orderItemCommission * $item->getQty() / $orderItemQty;
//            }
//        }
//        if ($reduceCommission <= 0) {
//            return $this;
//        }
//        // check reduced commission is over than total commission
//        if ($reduceCommission > $this->getCommission()) {
//            $reduceCommission = $this->getCommission();
//        }
//        $account = Mage::getModel('affiliateplus/account')
//                ->setStoreId($this->getStoreId())
//                ->load($this->getAccountId());
//        try {
//            $commission = $reduceCommission + $this->getCommissionPlus() * $reduceCommission / $this->getCommission() + $reduceCommission * $this->getPercentPlus() / 100;
////            edit by viet  
//            if ($this->getStatus() == '1' ) {
//                $account->setBalance($account->getBalance() - $commission)
//                        ->save();
//            }
//            $this->setCreditmemoIds(implode(',', array_filter($reducedIds)))
//                    ->setCommissionPlus($this->getCommissionPlus() - $this->getCommissionPlus() * $reduceCommission / $this->getCommission())
//                    ->setCommission($this->getCommission() - $reduceCommission)
//                    ->save();
//
//            // update balance for tier affiliate
//            $commissionObj = new Varien_Object(array(
//                        'base_reduce' => $reduceCommission,
//                        'total_reduce' => $commission
//                    ));
//            Mage::dispatchEvent('affiliateplus_reduce_transaction', array(
//                'transaction' => $this,
//                // 'creditmemo'    => $creditmemo,
//                'commission_obj' => $commissionObj
//            ));
//
//            $reduceCommission = $commissionObj->getBaseReduce();
//            $commission = $commissionObj->getTotalReduce();
//            // Send email for affiliate account
//            $this->sendMailReduceCommissionToAccount($reduceCommission, $commission);
//        } catch (Exception $e) {
//            
//        }
//        return $this;
//    }

    /**
     * 
     * @return \Magestore_Affiliateplus_Model_Transaction
     */
    public function cancel() {
        if ($this->canRestore()) {
            return $this;
        }
        if (!$this->getId()) {
            return $this;
        }
//        edit by viet
        if ($this->getStatus() == '2' || $this->getStatus() == '4') {
            try {
//                edit by viet set commission when cancel order
                $this->setCommission(0);
                $this->setDiscount(0);
                $status = $this->getStatus();
//                        end by biet
                $this->setStatus('3')->save();

                // Changed By Adam 06/10/2014
                //update balance tier affiliate in affiliatepluslevel_transaction
                Mage::dispatchEvent('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                //update affiliateplusprogram_transaction
                Mage::dispatchEvent('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

                // Changed By Adam 16/10/2014
                // $this->sendMailReduceCommissionToAccount();
                $this->sendMailUpdatedTransactionToAccount(false);
            } catch (Exception $e) {
                
            }
        } elseif ($this->getStatus() == '1') {
            $status = $this->getStatus();
            // Remove commission for affiliate account
            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($this->getStoreId())
                    ->load($this->getAccountId());
            try {

                $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
                //Jack update 27/07
                $orderId = Mage::getSingleton('adminhtml/session_quote')->getOrder()->getId();
                $affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
                $affiliateAccount = '';
                foreach ($affiliateInfo as $info)
                    if ($info['account']) {
                        $affiliateAccount = $info['account'];
                        break;
                    }
                /* update balance after edit Order by Jack  */
                if ($orderId) {
                    
                    $orderNumber = Mage::getModel('sales/order')->load($orderId)->getIncrementId();
                    $transactionInfo = Mage::getModel('affiliateplus/transaction')->getCollection()
                                    ->addFieldToFilter('order_number', $orderNumber)->getFirstItem();
                    if (!$affiliateAccount) {
                        $affiliateAccount = Mage::getModel('affiliateplus/account')
                                ->setStoreId($this->getStoreId())
                                ->load($this->getAccountId());
                    }
                    if ($transactionInfo->getStatus() == 1) {
                        $newCommission = Mage::getSingleton('adminhtml/session_quote')->getCommission();
                        if (($affiliateAccount->getId() && ($affiliateAccount->getId() == $transactionInfo->getAccountId())) || !$affiliateAccount->getId()) {
                            $affiliateAccount->setBalance($affiliateAccount->getBalance() + ($newCommission - ($transactionInfo->getCommission())))->save();
                        } else if ($affiliateAccount->getId() && ($affiliateAccount->getId() != $transactionInfo->getAccountId())) {
                            $lastAffiliateAccount = Mage::getModel('affiliateplus/account')->load($transactionInfo->getAccountId());
                            $lastAffiliateAccount->setBalance($lastAffiliateAccount->getBalance() - ($transactionInfo->getCommission()))->save();
                            $affiliateAccount->setBalance($affiliateAccount->getBalance() + $newCommission)->save();
                        }
                        //unset session
                        Mage::getSingleton('adminhtml/session_quote')->unsCommission();
                    }
                    $this->setCommission(0)
                            ->setDiscount(0)
                            ->setStatus('3')
                            ->save();
                    // Changed By Adam 06/10/2014
                    //update balance tier affiliate in affiliatepluslevel_transaction
                    Mage::dispatchEvent('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                    //update affiliateplusprogram_transaction
                    Mage::dispatchEvent('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

                    // Send email to affiliate account
                    $this->sendMailUpdatedTransactionToAccount(false);
                }
                /* end update balance  */ 
                else {
                    
                    //Changed By Adam to solve the problem
                    $storeId = $this->getStoreId();
                    if (!$this->_getConfigHelper()->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
                        $account->setBalance($account->getData('balance') - $commission)//$this->getCommission())
                                ->save();

                        // Changed By Adam 06/10/2014
                        //update balance tier affiliate in affiliatepluslevel_transaction
                        Mage::dispatchEvent('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

                        //update affiliateplusprogram_transaction
                        Mage::dispatchEvent('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));
                        
                        $this->setCommission(0)
                                ->setDiscount(0)
                                ->setStatus('3')
                                ->save();

                        // Send email to affiliate account
                        $this->sendMailUpdatedTransactionToAccount(false);
                    }
                    
                }
            } catch (Exception $e) {
                
            }
        }
        return $this;
    }

    /**
     * Cancel transaction
     * 
     * @return Magestore_Affiliateplus_Model_Transaction
     * @throws Exception
     */
    public function cancelTransaction() {
        if ($this->canRestore())
            return $this;
        if (!$this->getId())
            return $this;
        // Changed By Adam 06/10/2014
//        if ($this->getStatus() == '3')
//            return $this;

        if ($this->getStatus() == '1') {
            // Remove commission for affiliate account
            $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($this->getStoreId())
                    ->load($this->getAccountId());
            $commission = $this->getCommission() + $this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100;
            if ($account->getBalance() < $commission) {
                throw new Exception(Mage::helper('affiliateplus')->__('Account not enough balance to cancel'));
            }
            $account->setBalance($account->getBalance() - $commission)
                    ->save();
        }

        // Changed By Adam 06/10/2014
        $status = $this->getStatus();
        //update balance tier affiliate in affiliatepluslevel_transaction
        Mage::dispatchEvent('affiliateplus_cancel_transaction', array('transaction' => $this, 'status' => $status));

        //update affiliateplusprogram_transaction
        Mage::dispatchEvent('affiliateplus_cancel_transaction_multipleprogram', array('transaction' => $this, 'status' => $status));

//                edit by viet set commission when cancel order
        $this->setCommission(0)
                ->setDiscount(0);
//                end by biet
        $this->setStatus('3')->save();
        return $this;
    }

    public function canRestore() {
        return $this->getData('transaction_is_deleted');
    }

    public function sendMailNewTransactionToAccount() {
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_new_transaction'))
            return $this;

        $store = Mage::getModel('core/store')->load($this->getStoreId());
        $currentCurrency = $store->getCurrentCurrency();
        $store->setCurrentCurrency($store->getBaseCurrency());

        $account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());

        if (!$account->getNotification())
            return $this;

        //update commission tier affiliate
        Mage::dispatchEvent('affiliateplus_reset_transaction_commission', array('transaction' => $this));

        $this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
                ->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
                ->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
                ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
                ->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
                ->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium'))
        ;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $template = Mage::getStoreConfig(self::XML_PATH_NEW_TRANSACTION_ACCOUNT_EMAIL, $store->getId());

        $sendTo = array(
            array(
                'email' => $account->getEmail(),
                'name' => $account->getName(),
            )
        );
        $mailTemplate = Mage::getModel('core/email_template');
        $sender = Mage::helper('affiliateplus')->getSenderContact();
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()))
                    ->sendTransactional(
                            $template, $sender, $recipient['email'], $recipient['name'], array(
                        'transaction' => $this,
                        'store' => $store,
                        'sender_name' => $sender['name'],
                            )
            );
        }

        $translate->setTranslateInline(true);

        //set current currency
        $store->setCurrentCurrency($currentCurrency);

        return $this;
    }

    public function sendMailNewTransactionToSales() {
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_sales_new_transaction'))
            return $this;

        $store = Mage::getModel('core/store')->load($this->getStoreId());
        $currentCurrency = $store->getCurrentCurrency();
        $store->setCurrentCurrency($store->getBaseCurrency());
        $sales = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store->getId());

        $account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());

        //update commission tier affiliate
        //Mage::dispatchEvent('affiliateplus_reset_transaction_commission',array('transaction' => $this));

        $this->setCustomerName($this->getCustomerName())
                ->setCustomerEmail($this->getCustomerEmail())
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
                ->setProducts(Mage::helper('affiliateplus')->getBackendProductHtmls($this->getOrderItemIds()))
                ->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
                ->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
                ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
                ->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
                ->setDiscountFormated(Mage::helper('core')->currency($this->getDiscount()))
                ->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium'))
                ->setSalesName($sales['name'])
        ;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $template = Mage::getStoreConfig(self::XML_PATH_NEW_TRANSACTION_SALES_EMAIL, $store->getId());

        $sendTo = array(
            array(
                'email' => $sales['email'],
                'name' => $sales['name'],
            )
        );

        $mailTemplate = Mage::getModel('core/email_template');
        $sender = Mage::helper('affiliateplus')->getSenderContact();
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()))
                    ->sendTransactional(
                            $template, $sender, $recipient['email'], $recipient['name'], array(
                        'transaction' => $this,
                        'store' => $store,
                        'sender_name' => $sender['name'],
                            )
            );
        }

        $translate->setTranslateInline(true);
        //set current currency
        $store->setCurrentCurrency($currentCurrency);
        return $this;
    }

    public function sendMailUpdatedTransactionToAccount($isCompleted) {
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_updated_transaction'))
            return $this;

        $store = Mage::getModel('core/store')->load($this->getStoreId());
        $currentCurrency = $store->getCurrentCurrency();
        $store->setCurrentCurrency($store->getBaseCurrency());

        $account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());

        if (!$account->getNotification())
            return $this;

        //update commission tier affiliate
        Mage::dispatchEvent('affiliateplus_reset_transaction_commission', array('transaction' => $this));

        $this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
                ->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
                ->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
                ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
                ->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
                ->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium'))
                ->setIsCompleted($isCompleted)
        ;
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $template = Mage::getStoreConfig(self::XML_PATH_UPDATED_TRANSACTION_ACCOUNT_EMAIL, $store->getId());

        $sendTo = array(
            array(
                'email' => $account->getEmail(),
                'name' => $account->getName(),
            )
        );
        $mailTemplate = Mage::getModel('core/email_template');
        $sender = Mage::helper('affiliateplus')->getSenderContact();
        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()))
                    ->sendTransactional(
                            $template, $sender, $recipient['email'], $recipient['name'], array(
                        'transaction' => $this,
                        'store' => $store,
                        'sender_name' => $sender['name'],
                            )
            );
        }

        $translate->setTranslateInline(true);
        //set current currency
        $store->setCurrentCurrency($currentCurrency);
        return $this;
    }

    /**
     * Send email reduce commission to affiliate account
     * 
     * @param type $reduceCommission
     * @param type $totalReduce
     * @return Magestore_Affiliateplus_Model_Transaction
     */
    public function sendMailReduceCommissionToAccount($reduceCommission, $totalReduce) {
        if (!Mage::getStoreConfig('affiliateplus/email/is_sent_email_account_updated_transaction')) {
            return $this;
        }
        $account = Mage::getModel('affiliateplus/account')->load($this->getAccountId());
        if (!$account->getNotification()) {
            return $this;
        }
        $store = Mage::getModel('core/store')->load($this->getStoreId());
        $currentCurrency = $store->getCurrentCurrency();
        $store->setCurrentCurrency($store->getBaseCurrency());

        Mage::dispatchEvent('affiliateplus_reset_transaction_commission', array('transaction' => $this));
        $this->setProducts(Mage::helper('affiliateplus')->getFrontendProductHtmls($this->getOrderItemIds()))
                ->setTotalAmountFormated(Mage::helper('core')->currency($this->getTotalAmount()))
                ->setCommissionFormated(Mage::helper('core')->currency($this->getCommission()))
                ->setPlusCommission($this->getCommissionPlus() + $this->getCommission() * $this->getPercentPlus() / 100)
                ->setPlusCommissionFormated(Mage::helper('core')->currency($this->getPlusCommission()))
                ->setAccountName($account->getName())
                ->setAccountEmail($account->getEmail())
                ->setCreatedAtFormated(Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium'))
                ->setReducedCommission(Mage::helper('core')->currency($reduceCommission))
                ->setTotalReduced(Mage::helper('core')->currency($totalReduce));
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $template = Mage::getStoreConfig(self::XML_PATH_REDUCE_TRANSACTION_ACOUNT_EMAIL, $store);
        $sendTo = array(array(
                'email' => $account->getEmail(),
                'name' => $account->getName(),
        ));
        $mailTemplate = Mage::getModel('core/email_template')
                ->setDesignConfig(array('area' => 'frontend', 'store' => $store->getId()));
        $sender = Mage::helper('affiliateplus')->getSenderContact();
        foreach ($sendTo as $recipient) {
            $mailTemplate->sendTransactional(
                    $template, $sender, $recipient['email'], $recipient['name'], array(
                'transaction' => $this,
                'store' => $store,
                'sender_name' => $sender['name'],
                    )
            );
        }

        $translate->setTranslateInline(true);
        $store->setCurrentCurrency($currentCurrency);
        return $this;
    }

    /* Changed By Adam: Change status from onhold to complete 22/07/2014 */

    public function getOptionArray() {
        return array(
            '1' => Mage::helper('affiliateplus')->__('Complete')
        );
    }

}
