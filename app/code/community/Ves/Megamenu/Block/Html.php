<?php
/******************************************************
 * @package Ves Megamenu module for Magento 1.4.x.x and Magento 1.7.x.x
 * @version 1.0.0.1
 * @author http://landofcoder.com
 * @copyright   Copyright (C) December 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license     GNU General Public License version 2
*******************************************************/
class Ves_Megamenu_Block_Html extends Mage_Core_Block_Template 
{
    /**
     * @var string $_config
     * 
     * @access protected
     */
    protected $_config = '';
    
    /**
     * Contructor
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);      
    }
}
