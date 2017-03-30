<?php
class NextBits_CustomerActivation_Test_Helper_DataTest extends EcomDev_PHPUnit_Test_Case
{
    protected $_mail = null;
    protected $_originalLocale = null;
    protected function setUp()
    {
        if (is_null($this->_originalLocale)) {
            $this->_originalLocale = $this->app()->getLocale()->getLocaleCode();
        }
    }
    protected function tearDown()
    {
        if ($this->_mail) {
            $this->_mail->setSendCount(0);
            $this->_mail->clearRecipients()->setParts(array());
        }
        if (isset($this->_originalLocale)) {
            $this->app()->getLocale()->setLocaleCode($this->_originalLocale);
        }
    }

    protected function _getMockCustomer()
    {
        $mockCustomer = $this->getModelMockBuilder('customer/customer')
            ->disableOriginalConstructor()
            ->setMethods(array('getName', 'getEmail', 'getSendemailStoreId', 'getStoreId', 'getAddressesCollection'))
            ->getMock();
        $mockCustomer->expects($this->any())
            ->method('getSendemailStoreId')
            ->will($this->returnValue(null));
        $mockCustomer->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue($this->app()->getStore('usa')->getId()));
        $mockCustomer->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('John Doe'));
        $mockCustomer->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('John.Doe@example.com'));
        $mockCustomer->expects(($this->any()))
            ->method('getAddressesCollection')
            ->will($this->returnValue(new Varien_Object()));

        return $mockCustomer;
    }

	protected function _prepareEmailTemplateInstance()
    {
        $this->_mail = new NextBits_CustomerActivation_Test_Helper_Data_Mock_Zend_Mail();

        $mailTemplate = Mage::getModel('core/email_template');
        $property = new ReflectionProperty($mailTemplate, '_mail');
        $property->setAccessible(true);
        $property->setValue($mailTemplate, $this->_mail);

        $this->app()->getConfig()->replaceInstanceCreation('model', 'core/email_template', $mailTemplate);
    }
    public function sendCustomerNotificationEmailDataProvider()
    {
        return array(
            array('en_US', 'your account has been activated.'), 
            array('xx_XX', 'your account has been activated.'), 
            array('de_DE', 'ihr Konto wurde aktiviert.'),       
        );
    }

    public function sendCustomerNotificationEmail($locale, $contentPart)
    {
        $mockCustomer = $this->_getMockCustomer();

        $this->app()->getStore($mockCustomer->getStoreId())->setConfig('general/locale/code', $locale);


        $this->_prepareEmailTemplateInstance();

        Mage::helper('customeractivation')->sendCustomerNotificationEmail($mockCustomer);

        $message = sprintf(
            "Expected method send() to be called 1 time but found to be called %s time(s)",
            $this->_mail->getSendCount()
        );
        $this->assertEquals(1, $this->_mail->getSendCount(), $message);

        $this->assertContains(
            $mockCustomer->getEmail(), $this->_mail->getRecipients(),
            "Not found the customer email in recipient list of email instance"
        );

        $this->assertContains($contentPart, $this->_mail->getBodyHtml(true));
    }

    public function sendAdminNotificationEmailDataProvider()
    {
        return array(
            array('en_US', 'en_US', 'New customer registration at'), 
            array('xx_XX', 'en_US', 'New customer registration at'), 
            array('de_DE', 'en_US', 'New customer registration at'), 
            array('de_DE', 'de_DE', 'Neue Kundenregistrierung bei'), 
            array('en_US', 'de_DE', 'Neue Kundenregistrierung bei'), 
        );
    }

    public function sendAdminNotificationEmail($customerLocale, $adminLocale, $contentPart)
    {
        $mockCustomer = $this->_getMockCustomer();

        $this->app()->getStore($mockCustomer->getStoreId())
            ->setConfig('general/locale/code', $customerLocale)
            ->setConfig('customer/customeractivation/alert_admin', 1);
        
        $this->app()->getStore('admin')
            ->setConfig('general/locale/code', $adminLocale);
        
        $this->_prepareEmailTemplateInstance();

        Mage::helper('customeractivation')->sendAdminNotificationEmail($mockCustomer);

        $message = sprintf(
            "Expected method send() to be called 1 time but found to be called %s time(s)",
            $this->_mail->getSendCount()
        );
        $this->assertEquals(1, $this->_mail->getSendCount(), $message);

        $this->assertNotContains(
            $mockCustomer->getEmail(), $this->_mail->getRecipients(),
            "Found the customer email in recipient list for admin email"
        );

        $this->assertContains($contentPart, $this->_mail->getBodyHtml(true));
    }
}