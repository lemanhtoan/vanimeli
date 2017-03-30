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

class Vaimo_Klarna_Model_Resource_Klarnacheckout_History extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('klarna/klarnacheckout_history', 'id');
    }

    public function loadByIdAndQuote(Vaimo_Klarna_Model_Klarnacheckout_History $history,
                                     $klarnaCheckoutId, $quoteId)
    {
        $adapter = $this->_getReadAdapter();
        $historyTable   = $this->getTable('klarna/klarnacheckout_history');
        $bind    = array('klarna_checkout_id' => $klarnaCheckoutId);
        $select  = $adapter->select()
            ->from($historyTable)
            ->where('klarna_checkout_id = :klarna_checkout_id');
        if ($quoteId) {
            $bind['quote_id'] = $quoteId;
            $select->where('quote_id = :quote_id');
        }
        $historyId = $adapter->fetchOne($select, $bind);
        if ($historyId) {
            $this->load($history, $historyId);
        } else {
            $history->setData(array());
        }

        return $this;
    }
}