<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Lof
 * @package     Lof_Coinslider
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Banner base block
 *
 * @category    Lof
 * @package     Lof_Coinslider
 * @author    
 */
class Ves_BlockBuilder_Block_Adminhtml_Blocksample extends Mage_Adminhtml_Block_Widget_Form_Container {
    public function __construct() {

        parent::__construct();

        $this->_objectId = 'block_id';
        $this->_blockGroup = 'ves_blockbuilder';
        $this->_controller = 'adminhtml_blockbuilder';

        $this->_headerText = Mage::helper('ves_blockbuilder')->__('Data Sample Manager');

        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('save');

        $this->setTemplate('ves_blockbuilder/block/sample.phtml');
    }

    protected function _prepareLayout() {

        $this->setChild('backbutton',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                'label'     => Mage::helper('ves_blockbuilder')->__('Back'),
                'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
                'class'   => 'back'
                ))
        );
        
        return parent::_prepareLayout();
    }
    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/*/index/');
    }

    public function getBackButtonHtml() {
        return $this->getChildHtml('backbutton');
    }
    public function getDataSampleLink($profile = "", $sub_folder = "") {
        return $this->getUrl('*/adminhtml_blockbuilder/importCsv', array('profile'=>$profile, 'subfolder'=>$sub_folder));
    }


}