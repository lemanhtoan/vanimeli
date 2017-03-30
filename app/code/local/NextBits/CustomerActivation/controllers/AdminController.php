<?php
class NextBits_CustomerActivation_AdminController extends Mage_Adminhtml_Controller_Action
{
	public function massActivationAction()
    {
        $customerIds = $this->getRequest()->getParam('customer');

        if (!is_array($customerIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('customeractivation')->__('Please select item(s)')
            );
        } else {
            $paramValue = $this->getRequest()->getParam('customer_activated');

            try {
                $updatedCustomerIds = Mage::getResourceModel('customeractivation/customer')
                    ->massSetActivationStatus(
                        $customerIds, $this->_shouldSetToActivated($paramValue)
                    );

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('customeractivation')->__(
                        'Total of %d record(s) were successfully saved', count($updatedCustomerIds)
                    )
                );

                if ($this->_shouldSendActivationNotification($paramValue)) {
                    $this->_sendActivationNotificationEmails($updatedCustomerIds);
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/customer');
    }
    protected function _shouldSetToActivated($paramValue)
    {
        switch ($paramValue) {
            case NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITH_EMAIL:
            case NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITHOUT_EMAIL:
                $activationStatus = 1;
                break;
            case NextBits_CustomerActivation_Helper_Data::STATUS_DEACTIVATE:
            default:
                $activationStatus = 0;
                break;
        }
        return $activationStatus;
    }
    protected function _shouldSendActivationNotification($paramValue)
    {
        switch ($paramValue) {
            case NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITH_EMAIL:
                $sendEmail = true;
                break;
            case NextBits_CustomerActivation_Helper_Data::STATUS_ACTIVATE_WITHOUT_EMAIL:
            case NextBits_CustomerActivation_Helper_Data::STATUS_DEACTIVATE:
            default:
                $sendEmail = false;
                break;
        }
        return $sendEmail;
    }
    protected function _sendActivationNotificationEmails(array $customerIds)
    {
        $helper = Mage::helper('customeractivation');
        $customers = Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToFilter('entity_id', array('in' => $customerIds))
            ->addAttributeToSelect('*')
            ->addNameToSelect();
        foreach ($customers as $customer) {
            $helper->sendCustomerNotificationEmail($customer);
        }
    }
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}