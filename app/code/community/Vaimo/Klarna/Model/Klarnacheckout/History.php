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

class Vaimo_Klarna_Model_Klarnacheckout_History extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('klarna/klarnacheckout_history');
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
        }
    }

    public function loadByIdAndQuote($klarnaCheckoutId, $quoteId = null)
    {
        $this->_getResource()->loadByIdAndQuote($this, $klarnaCheckoutId, $quoteId);
        return $this;
    }

    public function updateKlarnacheckoutHistory($checkoutId, $message, $quoteId, $orderId, $reservationId)
    {
        if ($this->getId()) {
            $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());
        } else {
            $this->setklarnaCheckoutId($checkoutId);
        }
        if ($message) {
            $this->setMessage($message);
        }
        if ($quoteId) {
            $this->setQuoteId($quoteId);
        }
        if ($orderId) {
            $this->setOrderId($orderId);
        }
        if ($reservationId) {
            $this->setReservationId($reservationId);
        }
        $this->save();
    }

}