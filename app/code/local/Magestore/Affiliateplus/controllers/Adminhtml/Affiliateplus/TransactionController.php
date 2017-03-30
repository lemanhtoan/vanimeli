<?php
/**
 * Changed By Adam 29/10/2015: Fix issue of SUPEE 6788 - in Magento 1.9.2.2
 */
class Magestore_Affiliateplus_Adminhtml_Affiliateplus_TransactionController extends Mage_Adminhtml_Controller_Action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('affiliateplus/transaction')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));

        return $this;
    }

    public function indexAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Transactions'));
        $this->_initAction()
                ->renderLayout();
    }

    public function gridAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function viewAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $id = $this->getRequest()->getParam('id');
        $transaction = Mage::getModel('affiliateplus/transaction')->load($id);

        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Transactions'))
                ->_title($this->__($transaction->getAccountName()));

        if ($transaction->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $transaction->setData($data);
            }

            Mage::register('transaction_data', $transaction);

            $this->loadLayout();
            $this->_setActiveMenu('affiliateplus/transactions');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('New Transaction'), Mage::helper('adminhtml')->__('New Transaction'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit'))
                    ->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('The transaction does not exist.'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $transactionId = $this->getRequest()->getParam('id');
        if ($transactionId > 0) {
            $model = Mage::getModel('affiliateplus/transaction');
            try {
                $model->load($transactionId)
                        ->cancelTransaction();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Transaction was canceled successfully'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/view', array('id' => $transactionId));
    }

    /* Changed By Adam: Change status from onhold to complete 22/07/2014 */

    public function massStatusAction() {

        $ids = $this->getRequest()->getParam('transaction');

        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select transaction(s)'));
        } else {
            $collection = Mage::getResourceModel('affiliateplus/transaction_collection');
            $collection->addFieldToFilter('transaction_id', array('in' => $ids))
                    ->addFieldToFilter('status', 4)
            ;
            $successed = 0;
            foreach ($collection as $model) {
                try {
                    $model->unHold();
                    $successed++;
                } catch (Exception $e) {
                    print_r($e->getMessage());
                    die('z');
                }
            }
            if ($successed) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        $this->__('Total %s of %s transaction(s) were unholded successfully.', $successed, count($ids))
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addNotice(
                        $this->__('There was no transaction unholded.')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massCancelAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $ids = $this->getRequest()->getParam('transaction');
        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select transaction(s)'));
        } else {
            $collection = Mage::getResourceModel('affiliateplus/transaction_collection');
            $collection->addFieldToFilter('transaction_id', array('in' => $ids));
            $successed = 0;
            foreach ($collection as $model) {
                try {
                    $model->cancelTransaction();
                    $successed++;
                } catch (Exception $e) {
                    
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total %s of %s transaction(s) were canceled successfully.', $successed, count($ids))
            );
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'transaction.csv';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_grid')
                ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        if (!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)) {
            return;
        }
        $fileName = 'transaction.xml';
        $content = $this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_grid')
                ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('affiliateplus/transaction');
    }

    /************************* Added By Adam (27/08/2016) *************************************************/

    /**
     * Changed By Adam (27/08/2016): create transaction from existed order
     */
    public function newAction() {
        if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->editAction();
        // $this->_forward('edit');
    }

    /**
     * Changed By Adam (27/08/2016): create transaction from existed order
     */
    public function editAction() {
        if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $id     = $this->getRequest()->getParam('id');
        $transaction  = Mage::getModel('affiliateplus/transaction')->load($id);

        $this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Transactions'))
            ->_title($this->__($transaction->getAccountName()));

        if ($transaction->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $transaction->setData($data);
            }

            Mage::register('transaction_data', $transaction);

            $this->loadLayout();
            $this->_setActiveMenu('affiliateplus/transactions');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction Manager'), Mage::helper('adminhtml')->__('Transaction Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Transaction News'), Mage::helper('adminhtml')->__('Transaction News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit'))
                ->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_transaction_edit_tabs'));

            $this->renderLayout();

        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Transaction does not exist'));
            $this->_redirect('*/*/');
        }
    }

    /**
     * Changed By Adam (27/08/2016): create transaction from existed order
     */
    public function listorderAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('transaction.edit.tab.listorder');
        // ->setOrders($this->getRequest()->getPost('aorders', null));
        $this->renderLayout();
    }

    /**
     * Changed By Adam (27/08/2016): create transaction from existed order
     */
    public function listorderGridAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('transaction.edit.tab.listorder');
        // ->setOrders($this->getRequest()->getPost('aorders', null));
        $this->renderLayout();
    }

    /**
     *  Changed By Adam (27/08/2016):  Create transaction from existed order
     */
    public function changeorderAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if($orderId)
        {
            $order = Mage::getModel('sales/order')->load($orderId);
            $incrementId = $order->getIncrementId();
            $order_total_amount = $order->getGrandTotal();
            $status = 2;
            if($order->getStatus() == 'complete'){
                $status = 1;
            }elseif($order->getStatus() == 'pending'){
                $status = 2;
            }elseif($order->getStatus() == 'canceled'){
                $status = 3;
            }elseif($order->getStatus() == 'holded'){
                $status = 4;
            }
            $html = '<input type="hidden" id="neworder_id" name="neworder_id" value="'. $orderId .'" >';
            $html .= '<input type="hidden" id="neworder_increment" name="neworder_increment" value="'. $incrementId .'" >';
            $html .= '<input type="hidden" id="neworder_total_amount" name="neworder_total_amount" value="'. $order_total_amount .'" >';
            $html .= '<input type="hidden" id="neworder_status" name="neworder_status" value="'. $status .'" >';
            $this->getResponse()->setHeader('Content-type', 'application/x-json');
            $this->getResponse()->setBody($html);
        }
    }

    /**
     *  Changed By Adam (27/08/2016):  Create transaction from existed order
     */
    public function saveAction() {
        if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        if ($data = $this->getRequest()->getPost()) {

            $order = Mage::getModel('sales/order')->load($data['neworder_id']);
            $accountEmail = $data['account_email'];
            $affiliateAccount = Mage::getModel('affiliateplus/account')->load($accountEmail, 'email');
            $orderInfo = $this->getOrderInfo($data['neworder_id']);
            $itemIds = $orderInfo['ids'];
            $itemNames = $orderInfo['names'];
            $orderStoreId = $orderInfo['store_id'];
            $customerId = $orderInfo['customer_id'];
            $customerEmail = $orderInfo['customer_email'];
            if(!$affiliateAccount->getId()){
                Mage::getSingleton('adminhtml/session')->addError($this->__('Account Email is not correct!'));
                $this->_redirect('*/*/new');
                return;
            }
            /*Changed By Adam to customize*/
            if($affiliateAccount->getCustomerId() == $order->getCustomerId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('You cannot create transaction for the affiliate who is also customer!'));
                $this->_redirect('*/*/new');
                return;
            }

            if(!$data['commission']) {
                $transactionObj = new Varien_Object(array(
                    'transaction' => '',
                ));
                Mage::dispatchEvent('select_order_to_create_transaction', array('order'=>$order, 'affiliate'=>$affiliateAccount, 'transaction'=>$transactionObj));
                $transaction = $transactionObj->getTransaction();
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $transaction->getData('transaction_id')));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } else {

                $transactionId = $this->getRequest()->getParam('id');
                $transaction = Mage::getModel('affiliateplus/transaction');
                $transaction->setData('account_id', $affiliateAccount->getId())
                    ->setData('account_name', $affiliateAccount->getName())
                    ->setData('account_email', $affiliateAccount->getEmail())
                    ->setData('customer_id', $customerId)
                    ->setData('customer_email', $customerEmail)
                    ->setData('order_id', $data['neworder_id'])
                    ->setData('order_number', $data['neworder_increment'])
                    ->setData('total_amount', $data['neworder_total_amount'])
                    ->setData('commission', $data['commission'])
                    ->setData('discount', $data['discount'])
                    ->setData('status', $data['neworder_status'])
                    ->setData('type', 3)
                    ->setData('store_id', $orderStoreId)
                    ->setData('order_item_ids', $itemIds)
                    ->setData('order_item_names', $itemNames)
                    ->setData('created_time', now())
                ;
                try{
                    $transaction->setId($transactionId);
                    $transaction->save();
                    if($transaction->getStatus() == '1'){
                        $transaction->setStatus(2)->complete();
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Transaction was successfully created'));

                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $transaction->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                }catch(Exception $e){
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    Mage::getSingleton('adminhtml/session')->setFormData($data);
                    $this->_redirect('*/*/edit', array('id' => $transactionId));
                    return;
                }
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find transaction to save'));
        $this->_redirect('*/*/');
    }

    /**
     * Changed By Adam (27/08/2016): Create transaction from existed order
     * @param $orderId
     * @return array
     */
    public function getOrderInfo($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderItems = $order->getAllVisibleItems();
        $itemIds = array();
        $itemNames = array();
        foreach($orderItems as $item){
            $itemIds[] = $item->getProductId();
            $itemNames[] = $item->getName();
        }
        $itemIds = implode(',', $itemIds);
        $itemNames = implode( ',', $itemNames);
        $orderInfo = array(
            'store_id' => $order->getStoreId(),
            'customer_id' => $order->getCustomerId(),
            'customer_email' => $order->getCustomerEmail(),
            'ids' => $itemIds,
            'names' => $itemNames,
        );
        return $orderInfo;
    }
    /* End Adding Transaction */

}
