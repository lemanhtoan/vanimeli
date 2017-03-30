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
 * @package    Ves_Gallery
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves Gallery Extension
 *
 * @category   Ves
 * @package    Ves_Gallery
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_Gallery_Block_Adminhtml_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	public function render(Varien_Object $row){
		if($row->getFile()!=''){
			$image = Mage::getBaseUrl('media').$row->getFile();
			return sprintf('<img src="%s" width="150px" alt="%s"/>',$image, $this->escapeHtml($row->getTitle()));
		}
	}
}