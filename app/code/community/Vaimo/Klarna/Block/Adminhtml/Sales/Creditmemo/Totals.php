<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Vaimo_Klarna_Block_Adminhtml_Sales_Creditmemo_Totals extends Mage_Adminhtml_Block_Sales_Order_Totals
{
    /**
     * Tax configuration model
     *
     * @var Mage_Tax_Model_Config
     */
    protected $_config;
    protected $_order;
    protected $_source;

    /**
     * Initialize configuration object
     */
    protected function _construct()
    {
        $this->_config = Mage::getSingleton('tax/config');
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return $this->_config->displaySalesFullSummary($this->getOrder()->getStore());
    }

    /**
     * Get data (totals) source model
     *
     * @return Varien_Object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function initTotals()
    {        
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $creditmemo = $parent->getCreditmemo();
        if ($creditmemo) {
            if ($creditmemo->getVaimoKlarnaFee()) {
                $fee = new Varien_Object();
                $fee->setLabel(Mage::helper('klarna')->getKlarnaFeeLabel($creditmemo->getStore()));
                $config = Mage::getSingleton('klarna/tax_config');
                if ($config->displaySalesKlarnaFeeInclTax($creditmemo->getStoreId())) {
                    $fee->setValue($creditmemo->getVaimoKlarnaFee() + $creditmemo->getVaimoKlarnaFeeTax());
                    $fee->setBaseValue($creditmemo->getVaimoKlarnaBaseFee() + $creditmemo->getVaimoKlarnaBaseFeeTax());
                } else {
                    $fee->setValue($creditmemo->getVaimoKlarnaFee());
                    $fee->setBaseValue($creditmemo->getVaimoKlarnaBaseFee());
                }
                $fee->setCode('vaimo_klarna_fee');
                $parent->addTotal($fee, 'subtotal');
            }

            $this->_initSubtotal();
        }

        return $this;
    }

    /**
     * Get order store object
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Correction of subtotal after Mage_Tax_Block_Sales_Order_Tax::_initSubtotal()
     * Correction will be applied only in case if subtotal with tax is equal to 0
     *
     * @return Vaimo_Klarna_Block_Adminhtml_Sales_Creditmemo_Totals
     */
    protected function _initSubtotal()
    {
        $store  = $this->getStore();
        $parent = $this->getParentBlock();
        $subtotal = $parent->getTotal('subtotal');
        if (!$subtotal) {
            return $this;
        }
        if ($this->_config->displaySalesSubtotalBoth($store)) {
            $subtotal       = (float) $this->_source->getSubtotal();
            $baseSubtotal   = (float) $this->_source->getBaseSubtotal();
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl || !$baseSubtotalIncl) {
                //Calculate the subtotal if not set
                $subtotalIncl = $subtotal + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount()
                    - $this->_source->getVaimoKlarnaFeeTax();
                $baseSubtotalIncl = $baseSubtotal + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount()
                    - $this->_source->getVaimoKlarnaBaseFeeTax();

                if ($this->_source instanceof Mage_Sales_Model_Order) {
                    //Adjust the discount amounts for the base and well as the weee to display the right totals
                    foreach ($this->_source->getAllItems() as $item) {
                        $subtotalIncl += $item->getHiddenTaxAmount() + $item->getDiscountAppliedForWeeeTax();
                        $baseSubtotalIncl += $item->getBaseHiddenTaxAmount() +
                            $item->getBaseDiscountAppliedForWeeeTax();
                    }
                }
            }

            $taxHelper = Mage::helper('tax');

            $subtotalIncl = max(0, $subtotalIncl);
            $baseSubtotalIncl = max(0, $baseSubtotalIncl);
            $totalExcl = new Varien_Object(array(
                'code'      => 'subtotal_excl',
                'value'     => $subtotal,
                'base_value'=> $baseSubtotal,
                'label'     => $taxHelper->__('Subtotal (Excl.Tax)')
            ));
            $totalIncl = new Varien_Object(array(
                'code'      => 'subtotal_incl',
                'value'     => $subtotalIncl,
                'base_value'=> $baseSubtotalIncl,
                'label'     => $taxHelper->__('Subtotal (Incl.Tax)')
            ));
            $parent->addTotal($totalExcl, 'subtotal');
            $parent->addTotal($totalIncl, 'subtotal_excl');
            $parent->removeTotal('subtotal');
        } elseif ($this->_config->displaySalesSubtotalInclTax($store)) {
            $subtotalIncl   = (float) $this->_source->getSubtotalInclTax();
            $baseSubtotalIncl= (float) $this->_source->getBaseSubtotalInclTax();

            if (!$subtotalIncl) {
                $subtotalIncl = $this->_source->getSubtotal()
                    + $this->_source->getTaxAmount()
                    - $this->_source->getShippingTaxAmount()
                    - $this->_source->getVaimoKlarnaFeeTax();
            }
            if (!$baseSubtotalIncl) {
                $baseSubtotalIncl = $this->_source->getBaseSubtotal()
                    + $this->_source->getBaseTaxAmount()
                    - $this->_source->getBaseShippingTaxAmount()
                    - $this->_source->getVaimoKlarnaBaseFeeTax();
            }

            $total = $parent->getTotal('subtotal');
            if ($total) {
                $total->setValue(max(0, $subtotalIncl));
                $total->setBaseValue(max(0, $baseSubtotalIncl));
            }
        }
        return $this;
    }
}