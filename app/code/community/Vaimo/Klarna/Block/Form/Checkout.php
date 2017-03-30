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

class Vaimo_Klarna_Block_Form_Checkout extends Vaimo_Klarna_Block_Form_Abstract
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('vaimo/klarna/form/checkout.phtml');
    }

    /*
     * Should perhaps be isButtonNameImage... But this is ok as well
     *
     */
    public function isButtonNameUrl($name)
    {
        try {
            $res = false;
            if ($name) {
                if (stristr($name, 'http')) {
                    $res = true;
                }
            }
        } catch (Exception $e) {
        }

        return $res;
    }
    
    public function getBackButtonName()
    {
        try {
            $klarna = Mage::getModel('klarna/klarnacheckout');
            $klarna->setQuote($this->getQuote(), Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            $res = $klarna->getConfigData('label_back_button');
            if (!$res) {
                $res = Mage::helper('klarna')->__('Go to Klarna Checkout');
            }
        } catch (Exception $e) {
            $res = Mage::helper('klarna')->__('Go to Klarna Checkout');
        }

        return $res;
    }

    public function isButtonEnabled()
    {
        try {
            $klarna = Mage::getModel('klarna/klarnacheckout');
            $klarna->setQuote($this->getQuote(), Vaimo_Klarna_Helper_Data::KLARNA_METHOD_CHECKOUT);
            $res = $klarna->getConfigData('enable_other_button');
        } catch (Exception $e) {
            $res = true;
        }

        return $res;
    }
}

