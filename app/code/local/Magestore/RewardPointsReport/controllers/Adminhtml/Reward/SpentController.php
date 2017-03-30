<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Rewardpoints Spent Report Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Adminhtml_Reward_SpentController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('rewardpoints/reports/spent')
            ->_addBreadcrumb(
                $this->__('Reward Points'),
                $this->__('Reward Points')
            );
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Report'))
            ->_title($this->__('Spent Points'));
        
        $this->getLayout()->getBlock('rewardpointsreport.spent')
            ->setTitle($this->__('Point Spending Report'));
        
        $this->renderLayout();
    }
    
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * export grid items to CSV file
     */
    public function exportCsvAction()
    {
        $fileName   = 'spentsreport.csv';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_spent_grid')
                           ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid items to XML file
     */
    public function exportXmlAction()
    {
        $fileName   = 'spentsreport.xml';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_spent_grid')
                           ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * export grid items to XML Excel file
     */
    public function exportExcelAction()
    {
        $fileName   = 'spentsreport.xml';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_spent_grid')
                           ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/reports');
    }
}
