
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
class Ves_ProductList_Model_System_Config_ListAnimations{
      public function toOptionArray(){
            return array(
                  array('value' => "", 'label'=>Mage::helper('adminhtml')->__('No Animation')),
                  array('value' => "bounce", 'label'=>Mage::helper('adminhtml')->__('bounce')),
                  array('value' => "flash", 'label'=>Mage::helper('adminhtml')->__('flash')),
                  array('value' => "pulse", 'label'=>Mage::helper('adminhtml')->__('pulse')),
                  array('value' => "rubberBand", 'label'=>Mage::helper('adminhtml')->__('rubberBand')),
                  array('value' => "shake", 'label'=>Mage::helper('adminhtml')->__('shake')),
                  array('value' => "swing", 'label'=>Mage::helper('adminhtml')->__('swing')),
                  array('value' => "tada", 'label'=>Mage::helper('adminhtml')->__('tada')),
                  array('value' => "wobble", 'label'=>Mage::helper('adminhtml')->__('wobble')),
                  array('value' => "bounceIn", 'label'=>Mage::helper('adminhtml')->__('bounceIn')),
                  array('value' => "bounceInDown", 'label'=>Mage::helper('adminhtml')->__('bounceInDown')),
                  array('value' => "bounceInLeft", 'label'=>Mage::helper('adminhtml')->__('bounceInLeft')),
                  array('value' => "bounceInRight", 'label'=>Mage::helper('adminhtml')->__('bounceInRight')),
                  array('value' => "bounceInUp", 'label'=>Mage::helper('adminhtml')->__('bounceInUp')),
                  array('value' => "fadeIn", 'label'=>Mage::helper('adminhtml')->__('fadeIn')),
                  array('value' => "fadeInDown", 'label'=>Mage::helper('adminhtml')->__('fadeInDown')),
                  array('value' => "fadeInDownBig", 'label'=>Mage::helper('adminhtml')->__('fadeInDownBig')),
                  array('value' => "fadeInLeft", 'label'=>Mage::helper('adminhtml')->__('fadeInLeft')),
                  array('value' => "fadeInLeftBig", 'label'=>Mage::helper('adminhtml')->__('fadeInLeftBig')),
                  array('value' => "fadeInRight", 'label'=>Mage::helper('adminhtml')->__('fadeInRight')),
                  array('value' => "fadeInRightBig", 'label'=>Mage::helper('adminhtml')->__('fadeInRightBig')),
                  array('value' => "fadeInUp", 'label'=>Mage::helper('adminhtml')->__('fadeInUp')),
                  array('value' => "fadeInUpBig", 'label'=>Mage::helper('adminhtml')->__('fadeInUpBig')),
                  array('value' => "flip", 'label'=>Mage::helper('adminhtml')->__('flip')),
                  array('value' => "flipInX", 'label'=>Mage::helper('adminhtml')->__('flipInX')),
                  array('value' => "flipInY", 'label'=>Mage::helper('adminhtml')->__('flipInY')),
                  array('value' => "lightSpeedIn", 'label'=>Mage::helper('adminhtml')->__('lightSpeedIn')),
                  array('value' => "rotateIn", 'label'=>Mage::helper('adminhtml')->__('rotateIn')),
                  array('value' => "rotateInDownLeft", 'label'=>Mage::helper('adminhtml')->__('rotateInDownLeft')),
                  array('value' => "rotateInDownRight", 'label'=>Mage::helper('adminhtml')->__('rotateInDownRight')),
                  array('value' => "rotateInUpLeft", 'label'=>Mage::helper('adminhtml')->__('rotateInUpLeft')),
                  array('value' => "slideInUp", 'label'=>Mage::helper('adminhtml')->__('slideInUp')),
                  array('value' => "slideInDown", 'label'=>Mage::helper('adminhtml')->__('slideInDown')),
                  array('value' => "slideInLeft", 'label'=>Mage::helper('adminhtml')->__('slideInLeft')),
                  array('value' => "slideInRight", 'label'=>Mage::helper('adminhtml')->__('slideInRight')),
                  array('value' => "slideOutUp", 'label'=>Mage::helper('adminhtml')->__('slideOutUp')),
                  array('value' => "slideOutDown", 'label'=>Mage::helper('adminhtml')->__('slideOutDown')),
                  array('value' => "slideOutLeft", 'label'=>Mage::helper('adminhtml')->__('slideOutLeft')),
                  array('value' => "slideOutRight", 'label'=>Mage::helper('adminhtml')->__('slideOutRight')),
                  array('value' => "hinge", 'label'=>Mage::helper('adminhtml')->__('hinge')),
                  array('value' => "rollIn", 'label'=>Mage::helper('adminhtml')->__('rollIn')),
                  array('value' => "zoomIn", 'label'=>Mage::helper('adminhtml')->__('zoomIn')),
                  array('value' => "zoomInDown", 'label'=>Mage::helper('adminhtml')->__('zoomInDown')),
                  array('value' => "zoomInLeft", 'label'=>Mage::helper('adminhtml')->__('zoomInLeft')),
                  array('value' => "zoomInRight", 'label'=>Mage::helper('adminhtml')->__('zoomInRight')),
                  array('value' => "zoomInUp", 'label'=>Mage::helper('adminhtml')->__('zoomInUp'))
                  );
      }
}