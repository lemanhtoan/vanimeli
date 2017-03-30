<?php
/*------------------------------------------------------------------------
 # Venus Base Module 
 # ------------------------------------------------------------------------
 # author:    VenusTheme.Com
 # copyright: Copyright (C) 2012 http://www.venustheme.com. All Rights Reserved.
 # @license: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 # Websites: http://www.venustheme.com
 # Technical Support:  http://www.venustheme.com/
-------------------------------------------------------------------------*/
class Ves_Base_Block_Html extends Mage_Core_Block_Template 
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
