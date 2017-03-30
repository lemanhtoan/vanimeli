<?php
class NextBits_LoginCatalog_Test_Model_ObserverTest extends EcomDev_PHPUnit_Test_Case_Controller
{
    public function redirectOnProductLoad($storeCode)
    {
        $this->dispatch('catalog/product/view', array('id' => 1, '_store' => $storeCode));

        $this->assertEventDispatched('catalog_product_load_after');

        $expected = $this->expected('%s', $storeCode);

        if ($expected->getRedirect()) {
            $message = sprintf(
                'Expected but did not find redirect to "%s" in store "%s"',
                Mage::getUrl($expected->getRoute(), $expected->getParams()), $storeCode
            );
            $this->assertRedirect($message);
            $expectedUrl = Mage::getUrl($expected->getRoute(), $expected->getparams());
            $redirectTarget = $this->_getRedirectTarget($this->app()->getResponse());
            $message = sprintf(
                'Expected redirect to "%s" but found target "%s"', $expectedUrl, $redirectTarget
            );
            $this->assertRedirectToUrl($expectedUrl, $message);
        } else {
            $redirectTarget = $this->_getRedirectTarget($this->app()->getResponse());
            $message = sprintf('Unexpected redirect for store "%s" to "%s"', $storeCode, $redirectTarget);
            $this->assertNotRedirect($message);
        }
    }
    protected function _getRedirectTarget($response)
    {
        $headers = $response->getHeaders();
        if ($headers) {
            foreach ($headers as $header) {
                if ('Location' === $header['name']) {
                    return $header['value'];
                }
            }
        }
        return '';
    }
    public function noRedirectOnHome()
    {
        $this->dispatch('cms/index/index', array('_store' => 'usa'));

        $this->assertEventNotDispatched('catalog_product_load_after');
        $this->assertNotRedirect();
    }
}