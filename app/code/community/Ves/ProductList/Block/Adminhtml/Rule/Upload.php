<?php

/**
 * Venustheme
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Venustheme EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.venustheme.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.venustheme.com/ for more information
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves ProductList Extension
 *
 * @category   Ves
 * @package    Ves_ProductList
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_ProductList_Block_Adminhtml_Rule_Upload extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct() {
        parent::__construct();
        $this->_objectId = 'rule_id';
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'productlist';

        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');
        $this->_mode = 'rule_upload';
        $this->_headerText = 'Ves Import Rule From CSV';
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl() {
        return $this->getUrl('*/*/index/');
    }
}
