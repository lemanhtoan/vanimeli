<?php

class NextBits_CustomerActivation_Test_Controller_Adminhtml_CustomerGridTest
    extends NextBits_CustomerActivation_Test_Controller_Adminhtml_AbstractController
{
    protected function _overloadExit()
    {
        if (function_exists('set_exit_overload')) {
            set_exit_overload(function () {
                return false;
            });
            return true;
        } elseif (function_exists('uopz_overload')) {
            if (! ini_get('uopz.overloads')) {
                $this->markTestSkipped('uopz extension installed but uopz.overloads ini setting is disabled.');
            }
            uopz_overload(ZEND_EXIT, function(){});
            return true;
        }
        return false;
    }
    
    protected function _restoreExit()
    {
        if (function_exists('unset_exit_overload')) {
            unset_exit_overload();
        } elseif (function_exists('uopz_overload')) {
            uopz_overload(ZEND_EXIT, null);
        }
    }
    protected function getResponseFromActionWithExit($route)
    {
        if (! $this->_overloadExit()) {
            $this->markTestSkipped("Unable to overload exit(): uopz or phpunit/test_helpers zend extensions not installed.");
        }
        try {
            ob_start();
            $this->dispatch($route);
            $this->_restoreExit();
        } catch (Zend_Controller_Response_Exception $e) {
            $this->_restoreExit();
            if ($e->getMessage() !== 'Cannot send headers; headers already sent') {
                ob_end_clean();
                throw $e;
            }
        }
        $responseBody = ob_get_contents();
        ob_end_clean();
        return $responseBody;
    }
    public function activationStatusGridModifications($action)
    {
        $this->dispatch('adminhtml/customer/' . $action);

        $this->assertLayoutHandleLoaded('adminhtml_customer_' . $action);
        $this->assertEventDispatched('eav_collection_abstract_load_before');

        $gridBlock = $this->_getCustomerGridBlock();
        $this->assertInternalType('object', $gridBlock, "Customer grid block not found");
        $this->assertInstanceOf('Mage_Adminhtml_Block_Customer_Grid', $gridBlock);

        $foundActivationCol = $gridBlock->getColumn('customer_activated') !== false;
        $this->assertTrue($foundActivationCol, "Customer activation column not found in grid");

        $massActionBlock = $gridBlock->getMassactionBlock();
        $massAction = $massActionBlock->getItem('customer_activated');
        $this->assertTrue((bool) $massAction, "Customer activation mass action not found");

        $collection = $gridBlock->getCollection();
        $property = new ReflectionProperty($collection, '_selectAttributes');
        $property->setAccessible(true);
        $selectAttributes = $property->getValue($collection);

        $this->assertArrayHasKey(
            'customer_activated', $selectAttributes, "Customer activation attribute not part of collection"
        );
    }
    protected function _getCustomerGridBlock()
    {
        foreach ($this->app()->getLayout()->getAllBlocks() as $block) {
            if ($block->getType() === 'adminhtml/customer_grid') {
                return $block;
            }
        }
        return null;
    }

    public function activationStatusGridModificationsProvider()
    {
        return array(
            array('index'),
            array('grid'),
        );
    }
    public function activationStatusInCsvExport()
    {
        $body = $this->getResponseFromActionWithExit('adminhtml/customer/exportCsv');

        $this->assertResponseHeaderEquals('content-type', 'application/octet-stream');

        $label = 'Customer Activated';

        list($exportHeaders) = explode("\n", $body);
        $columns = str_getcsv($exportHeaders);

        $this->assertTrue(in_array($label, $columns), "Column \"$label\" not found in CSV export columns");
    }
    public function activationStatusInExcelExport()
    {
        $body = $this->getResponseFromActionWithExit('adminhtml/customer/exportXml');

        $this->assertResponseHeaderEquals('content-type', 'application/octet-stream');

        $label = 'Customer Activated';

        $xml = simplexml_load_string($body);
        $found = false;
        foreach ($xml->Worksheet->children() as $worksheet) {
            foreach ($worksheet->children() as $columns) {
                foreach ($columns->children() as $cell) {
                    $value = (string) $cell->Data;
                    if ($value == $label) {
                        $found = true;
                        break(3);
                    }
                }
                break(2);
            }
        }

        $this->assertTrue($found, "Column \"$label\" not found in Excel export columns");
    }
}