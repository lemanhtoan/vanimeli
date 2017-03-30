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

class Vaimo_Klarna_Model_Resource_Klarnacheckout_Semaphore extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('klarna/klarnacheckout_semaphore', 'id');
    }

    public function loadActiveSemaphore(Vaimo_Klarna_Model_Klarnacheckout_Semaphore $semaphore, $klarnaCheckoutId)
    {
        $adapter = $this->_getReadAdapter();
        $semaphoreTable = $this->getTable('klarna/klarnacheckout_semaphore');
        $bind = array('klarna_checkout_id' => $klarnaCheckoutId,
            'status' => 'active'
        );
        $select = $adapter->select()
            ->from($semaphoreTable)
            ->where('status = :status')
            ->where('klarna_checkout_id = :klarna_checkout_id');

        $semaphoreId = $adapter->fetchOne($select, $bind);
        if ($semaphoreId) {
            $this->load($semaphore, $semaphoreId);
        } else {
            $semaphore->setData(array());
        }

        return $this;
    }
}