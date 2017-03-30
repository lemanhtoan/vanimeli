<?php

class Magestore_RewardpointsReferFriends_Model_Pdf_Couponcode extends Varien_Object {

    public function getPdf($couponIds) {
        // $this->_beforeGetPdf();

        if ($couponIds) {

            $pdf = new Zend_Pdf();
            $this->_setPdf($pdf);
            $style = new Zend_Pdf_Style();
            $this->_setFontBold($style, 10);
            $couponIds = array_chunk($couponIds, 8);
            foreach ($couponIds as $coupons) {
                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
                $this->y = 790;
                $i = 0;

                foreach ($coupons as $couponId) {
                    $couponOffer = $this->_getCouponById($couponId);
                    if ($couponOffer->getId()) {
                        $i++;
                        if ($i % 2 == 0) {


                            /* Add Border */
                            $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
                            $page->setLineColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfStyleColor()));
                            $page->setLineWidth(0.5);
                            $page->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
//                            $page->drawRectangle(310, $this->y, 560, $this->y - 170);
                            /* Insert Background image */
                            $store = Mage::app()->getStore($couponOffer->getStoreId());
                            $background_image = Mage::helper('rewardpointsreferfriends')->getBackgroundImg($store->getId());

                            if ($background_image) {
                                $background_image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/background/' . $background_image;
                                if (is_file($background_image)) {
                                    $background_image = Zend_Pdf_Image::imageWithPath($background_image);
                                    $page->drawImage($background_image, 310, $this->y - 170, 560, $this->y);
                                }
                            } else {
                                $background_image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/background/default/background.jpg';
                                if (is_file($background_image)) {
                                    $background_image = Zend_Pdf_Image::imageWithPath($background_image);
                                    $page->drawImage($background_image, 310, $this->y - 170, 560, $this->y);
                                }
                            }

                            /* Insert Image Logo */
                            $store = Mage::app()->getStore($couponOffer->getStoreId());
                            $image = Mage::getStoreConfig('rewardpoints/referfriendplugin/logo', $store->getId());

                            if ($image) {
                                $image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/logo/' . $image;
                                if (is_file($image)) {
                                    $image = Zend_Pdf_Image::imageWithPath($image);
                                    $page->drawImage($image, 320, $this->y - 79, 395, $this->y - 59);
                                }
                            } else {
                                $image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/logo/default/logo_print.png';
                                if (is_file($image)) {
                                    $image = Zend_Pdf_Image::imageWithPath($image);
                                    $page->drawImage($image, 320, $this->y - 79, 395, $this->y - 59);
                                }
                            }
                            //                        Insert discount
                            $page->setFillColor(new Zend_Pdf_Color_Html('#FF0000'));
                            $font = $this->_setFontBold($page, 40);
                            $special_offer = $this->getOfferDiscount();
                            $default_offer = $this->getDefaultDiscount();
                            if ($special_offer) {
                                $page->drawText($special_offer, 470, $this->y - 70, 'UTF-8');
                            } else {
                                if ($default_offer) {
                                    $page->drawText($default_offer, 470, $this->y - 70, 'UTF-8');
                                }
                            }

//                        Insert coupon id
//                            $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
//                            $this->_setFontBold($page, 10);
//                            $page->drawText(Mage::helper('rewardpointsreferfriends')->__('No: ' . $couponOffer->getId()), 340, $this->y - 85, 'UTF-8');
                            /* Insert caption */
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfStyleColor()));
                            $this->_setFontBold($page, 14);
                            $page->drawText(Mage::helper('rewardpointsreferfriends')->getCaptionCoupon(), 470, $this->y - 30, 'UTF-8');

                            /* Coupon code */
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getBackgroundCoupon()));
                            $page->setLineColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getBackgroundCoupon()));
                            $page->setLineDashingPattern();
                            $page->setLineWidth(0);
                            $page->drawRectangle(320, $this->y - 90, 550, $this->y - 115);
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfCouponColor()));
                            $font = $this->_setFontBold($page, 14);
                            $page->drawText($couponOffer->getCoupon(), 770 / 2 - $this->getTextWidth($couponOffer->getCoupon(), $font, 14) / 2, $this->y - 108, 'UTF-8');


//                    /* NOTE */
//                        $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
//                        $this->_setFontBold($page, 8);
//                        $page->drawText(Mage::helper('rewardpointsreferfriends')->__('Notes: '), 85, $this->y - 175, 'UTF-8');

                            $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
                            $font = $this->_setFontItalic($page, 8);
                            $notes = $this->getPrintNotes($couponOffer, $font, 230);
                            $drawY = $this->y - 130;
                            foreach ($notes as $note) {
                                if ($note != '') {
                                    $this->_printHyperLink($page, $note, 320, $drawY);
//                                    $page->drawText($note, 70, $drawY, 'UTF-8');
                                    $drawY -= 9;
                                }
                            }
                            $temp = $this->y - 180;
                            $this->y = $temp;
                        } else {

                            /* Add Border */
                            $page->setFillColor(new Zend_Pdf_Color_Rgb(255, 255, 255));
                            $page->setLineColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfStyleColor()));
                            $page->setLineWidth(0.5);
                            $page->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
//                            $page->drawRectangle(50, $this->y, 300, $this->y - 170);

                            /* Insert Background image */
                            $store = Mage::app()->getStore($couponOffer->getStoreId());
                            $background_image = Mage::helper('rewardpointsreferfriends')->getBackgroundImg($store->getId());

                            if ($background_image) {
                                $background_image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/background/' . $background_image;
                                if (is_file($background_image)) {
                                    $background_image = Zend_Pdf_Image::imageWithPath($background_image);
                                    $page->drawImage($background_image, 50, $this->y - 170, 300, $this->y);
                                }
                            } else {
                                $background_image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/background/default/background.jpg';
                                if (is_file($background_image)) {
                                    $background_image = Zend_Pdf_Image::imageWithPath($background_image);
                                    $page->drawImage($background_image, 50, $this->y - 170, 300, $this->y);
                                }
                            }
                            /* Insert Image Logo */
                            $store = Mage::app()->getStore($couponOffer->getStoreId());
                            $image = Mage::getStoreConfig('rewardpoints/referfriendplugin/logo', $store->getId());

                            if ($image) {
                                $image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/logo/' . $image;
                                if (is_file($image)) {
                                    $image = Zend_Pdf_Image::imageWithPath($image);
                                    $page->drawImage($image, 60, $this->y - 79, 135, $this->y - 59);
                                }
                            } else {
                                $image = Mage::getBaseDir('media') . DS . 'rewardpointsreferfriends/pdf/logo/default/logo_print.png';
                                if (is_file($image)) {
                                    $image = Zend_Pdf_Image::imageWithPath($image);
                                    $page->drawImage($image, 60, $this->y - 79, 135, $this->y - 59);
                                }
                            }
                            //                        Insert discount
                            $page->setFillColor(new Zend_Pdf_Color_Html('#FF0000'));
                            $font = $this->_setFontBold($page, 40);
                            $special_offer = $this->getOfferDiscount();
                            $default_offer = $this->getDefaultDiscount();
                            if ($special_offer) {
                                $page->drawText($special_offer, 210, $this->y - 70, 'UTF-8');
                            } else {
                                if ($default_offer) {
                                    $page->drawText($default_offer, 210, $this->y - 70, 'UTF-8');
                                }
                            }

//                        Insert coupon id
//                            $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
//                            $this->_setFontBold($page, 10);
//                            $page->drawText(Mage::helper('rewardpointsreferfriends')->__('No: ' . $couponOffer->getId()), 80, $this->y - 85, 'UTF-8');
                            /* Insert caption */
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfStyleColor()));
                            $this->_setFontBold($page, 14);
                            $page->drawText(Mage::helper('rewardpointsreferfriends')->getCaptionCoupon(), 210, $this->y - 30, 'UTF-8');

                            /* Coupon code */
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getBackgroundCoupon()));
                            $page->setLineColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getBackgroundCoupon()));
                            $page->setLineDashingPattern();
                            $page->setLineWidth(0);
                            $page->drawRectangle(60, $this->y - 90, 290, $this->y - 115);
                            $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfCouponColor()));
                            $font = $this->_setFontBold($page, 14);
                            $page->drawText($couponOffer->getCoupon(), 250 / 2 - $this->getTextWidth($couponOffer->getCoupon(), $font, 14) / 2, $this->y - 108, 'UTF-8');


//                    /* NOTE */
//                        $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
//                        $this->_setFontBold($page, 8);
//                        $page->drawText(Mage::helper('rewardpointsreferfriends')->__('Notes: '), 85, $this->y - 175, 'UTF-8');

                            $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
                            $font = $this->_setFontItalic($page, 8);
                            $notes = $this->getPrintNotes($couponOffer, $font, 230);
                            $drawY = $this->y - 130;
                            foreach ($notes as $note) {
                                if ($note != '') {
                                    $this->_printHyperLink($page, $note, 60, $drawY);
//                                    $page->drawText($note, 70, $drawY, 'UTF-8');
                                    $drawY -= 9;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $pdf;
    }

    public function getPrintNotes($couponOffer, $font, $max_width = 100) {
        $keyCache = 'print_notes_' . $couponOffer->getStoreId();
        if (!$this->hasData($keyCache)) {
            $notes = Mage::getStoreConfig('rewardpoints/referfriendplugin/note', $this->getStoreId());
            $notes = str_replace(array(
                '{store_url}',
                '{store_name}',
                '{store_address}'
                    ), array(
                Mage::app()->getStore($this->getStoreId())->getBaseUrl(),
                Mage::app()->getStore($this->getStoreId())->getFrontendName(),
                Mage::getStoreConfig('general/store_information/address', $this->getStoreId())
                    ), $notes);
            $notes = $this->_getWrappedText($notes, $font, $max_width);
            $notes = explode("\n", $notes);

            $this->setData($keyCache, $notes);
        }

        return $this->getData($keyCache);
    }

    public function wrapWordLine($string, $font, $fontSize = 8) {
        $notes = array();
        $string = str_replace(array(chr(10), chr(13)), array(' ', ' '), $string);
        $words = array_filter(explode(' ', $string));
        $string = '';
        foreach ($words as $word) {
            if ($this->widthForStringUsingFontSize($string . ' ' . $word, $font, $fontSize) > 416) {
                if ($string) {
                    $notes[] = $string;
                    $string = $word;
                } else {
                    $notes[] = $word;
                    $string = '';
                }
            } else {
                $string .= ' ' . $word;
            }
        }
        if ($string) {
            $notes[] = $string;
        }
        return $notes;
    }

    public function widthForStringUsingFontSize($string, $font, $fontSize) {
        $drawingString = '"libiconv"' == ICONV_IMPL ?
                iconv('UTF-8', 'UTF-16BE//IGNORE', $string) :
                @iconv('UTF-8', 'UTF-16BE', $string);

        $characters = array();
        for ($i = 0; $i < strlen($drawingString); $i++) {
            $characters[] = (ord($drawingString[$i++]) << 8) | ord($drawingString[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $stringWidth = (array_sum($widths) / $font->getUnitsPerEm()) * $fontSize;
        return $stringWidth;
    }

    protected function _beforeGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
    }

    protected function _afterGetPdf() {
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(true);
    }

    protected function _setPdf(Zend_Pdf $pdf) {
        $this->_pdf = $pdf;
        return $this;
    }

    protected function _setFontRegular($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $object->setFont($font, $size);
        return $object;
    }

    protected function _setFontBold($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $object;
    }

    protected function _getCouponById($id) {
        $collection = Mage::getResourceModel('rewardpointsreferfriends/rewardpointsrefercustomer_collection');
        $collection->getSelect()->joinLeft(array('customer_entity' => Mage::getModel('core/resource')->getTableName('customer/entity'))
                , 'main_table.customer_id = customer_entity.entity_id', array('customer_entity.*'));
        $coupon = $collection->addFieldToFilter('id', $id)->getFirstItem();
        return $coupon;
    }

    protected function _getWrappedText($string, Zend_Pdf_Style $style, $max_width) {
        $wrappedText = '';
        $lines = explode("\n", $string);
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $word_count = count($words);
            $i = 0;
            $wrappedLine = '';
            while ($i < $word_count) {
                /* if adding a new word isn't wider than $max_width,
                  we add the word */
                if ($this->widthForStringUsingFontSize($wrappedLine . ' ' . $words[$i]
                                , $style->getFont()
                                , $style->getFontSize()) < $max_width) {
                    if (!empty($wrappedLine)) {
                        $wrappedLine .= ' ';
                    }
                    $wrappedLine .= $words[$i];
                } else {
                    $wrappedText .= $wrappedLine . "\n";
                    $wrappedLine = $words[$i];
                }
                $i++;
            }
            $wrappedText .= $wrappedLine . "\n";
        }
        return $wrappedText;
    }

    /**
     * Return length of generated string in points
     *
     * @param string $string
     * @param Zend_Pdf_Resource_Font $font
     * @param int $font_size
     * @return double
     */
    public function getTextWidth($text, Zend_Pdf_Resource_Font $font, $font_size) {
        $drawing_text = iconv('', 'UTF-8', $text);
        $characters = array();
        for ($i = 0; $i < strlen($drawing_text); $i++) {
            $characters[] = (ord($drawing_text[$i++]) << 8) | ord($drawing_text[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($characters);
        $widths = $font->widthsForGlyphs($glyphs);
        $text_width = (array_sum($widths) / $font->getUnitsPerEm()) * $font_size;
        return $text_width;
    }

    /**
     * prepare hyperlink to print
     * @param type $page
     * @param type $text
     * @param type $width
     * @param type $drawY
     */
    protected function _printHyperLink($page, $text, $width, $drawY) {
        if (strpos($text, 'http') !== false) {
            $font = $this->setFontItalic($page, 8);
            $new_text = explode(' ', $text);
            $text_width = $width;

            foreach ($new_text as $str) {
                if (strpos($str, 'http') !== false) {

                    $page->setFillColor(new Zend_Pdf_Color_Html(Mage::helper('rewardpointsreferfriends')->getPdfStyleColor()));
                    $font = $this->_setFontItalic($page, 8);
                    $notes = $this->_getWrappedText($str, $font, 230);
                    $notes = explode("\n", $notes);
                    foreach ($notes as $note) {
                        if ($note != '') {
                            $page->drawText($note, $text_width, $drawY, 'UTF-8');
//                                    $page->drawText($note, 70, $drawY, 'UTF-8');
                             $drawY -= 9;
                        }
                    }
                } else {
                    $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
                    $font = $this->setFontItalic($page, 8);
                    $page->drawText($str, $text_width, $drawY, 'UTF-8');
                }
                $font = $this->setFontItalic($page, 8);
                $text_width+=$this->getTextWidth($str, $font, 8) + 5;
            }
        } else {
            $page->setFillColor(new Zend_Pdf_Color_Html('#000000'));
            $this->setFontItalic($page, 8);
            $page->drawText($text, $width, $drawY, 'UTF-8');
        }
    }

    /**
     * 
     * @param type $object
     * @param type $size
     * @return type
     */
    public function setFontItalic($object, $size = 7) {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * get special discount
     * @param type $store_id
     * @return boolean
     */
    public function getOfferDiscount($store_id = null) {
        $max_offer = Mage::helper('rewardpointsreferfriends')->getOfferWithMaxDiscount($store_id);
        if (!$max_offer || !$max_offer->getId()) {
            return false;
        }
        $discount = Mage::helper('rewardpointsreferfriends/calculation_earning')->round($max_offer->getDiscountValue());
        if ($max_offer->getDiscountType() == Magestore_RewardPointsReferFriends_Helper_Specialrefer::OFFER_TYPE_FIXED) {
            return $discount . Mage::app()->getLocale()->currency(Mage::app()->getStore($store_id)->getCurrentCurrencyCode())->getSymbol();
        }
        else
            return $discount . '%';
    }

    /**
     * get default offer
     * @param type $store_id
     * @return boolean
     */
    public function getDefaultDiscount($store_id = null) {
        $_helper = Mage::helper('rewardpointsreferfriends');
        $discountValue = Mage::helper('rewardpointsreferfriends/calculation_earning')->round($_helper->getReferConfig('discount_value', $store_id));
        if ($_helper->getReferConfig('use_default_config', $store_id) && $discountValue) {

            if ($_helper->getReferConfig('discount_type') == 'fix') {
                return $discountValue . Mage::app()->getLocale()->currency(Mage::app()->getStore($store_id)->getCurrentCurrencyCode())->getSymbol();
            }
            else
                return $discountValue . '%';
        }
        return false;
    }

}