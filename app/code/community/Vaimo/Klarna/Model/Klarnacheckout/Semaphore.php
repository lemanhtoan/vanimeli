<?php
/**
 * Copyright (c) 2009-2016 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_Klarna
 * @copyright   Copyright (c) 2009-2016 Vaimo AB
 */

class Vaimo_Klarna_Model_Klarnacheckout_Semaphore extends Mage_Core_Model_Abstract
{
    protected $_semaphoreTimeout = 90; // 1.5 minute

    protected function _construct()
    {
        parent::_construct();
        $this->_init('klarna/klarnacheckout_semaphore');
    }

    /**
     * Register queue creation date
     *
     * @return Mage_Core_Model_Abstract|void
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
            $this->setTimestamp(time());
            $this->setStatus('active');
        }
    }

    public function loadActiveSemaphore($klarnaCheckoutId)
    {
        $this->_getResource()->loadActiveSemaphore($this, $klarnaCheckoutId);
        return $this;
    }

    public function addSemaphore($checkoutId)
    {
        $res = true;
        $this->loadActiveSemaphore($checkoutId);
        if ($this->getId()) {
            if (time() > ($this->getTimestamp() + $this->_semaphoreTimeout)) {
                $message = 'Semaphore timed out, resetting it';
                Mage::helper('klarna')->logKlarnaApi($message);
                $this->failedSemaphore(array('message' => $message));
                $this->clearInstance();
                $this->unsetData();
                $this->setklarnaCheckoutId($checkoutId);
            } else {
                $this->setRetryAttempts($this->getRetryAttempts() + 1);
                $res = false;
            }
        } else {
            $this->setklarnaCheckoutId($checkoutId);
        }
        try {
            $this->save();
        } catch (Exception $e) {
            Mage::helper('klarna')->logKlarnaApi('Semaphore collision detected, need to wait to acquire it...');
            $res = false;
        }
        return $res;
    }

    public function updateSemaphore($fieldArr = null)
    {
        if ($fieldArr) {
            foreach ($fieldArr as $code => $value) {
                $this->setData($code, $value);
            }
            try {
                $this->save();
            } catch (Exception $e) {
                Mage::helper('klarna')->logKlarnaApi('Update of Semaphore failed: ' . $e->getMessage());
            }
        }
    }

    public function failedSemaphore($fieldArr = null)
    {
        $arr = array_merge($fieldArr, array('status' => 'failed ' . Mage::getSingleton('core/date')->gmtDate()));
        $this->updateSemaphore($arr);
    }

    public function waitSemaphore($checkoutId, $seconds = 60)
    {
        Mage::helper('klarna')->logKlarnaApi('Semaphore locked, waiting for release (max ' . $seconds . ' seconds)...');
        for ($i = 0; $i < $seconds; $i++) {
            sleep(1);
            if ($this->addSemaphore($checkoutId)) {
                Mage::helper('klarna')->logKlarnaApi('Semaphore released after ' . $i . ' seconds, continuing...');
                return true;
            }
        }
        return $this->addSemaphore($checkoutId);
    }

    public function deleteSemaphore()
    {
        $this->delete();
    }
}
