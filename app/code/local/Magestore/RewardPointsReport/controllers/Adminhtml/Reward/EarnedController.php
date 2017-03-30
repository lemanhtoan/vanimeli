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
 * Rewardpoints Earned Report Controller
 * 
 * @category    Magestore
 * @package     Magestore_RewardPointsReport
 * @author      Magestore Developer
 */
class Magestore_RewardPointsReport_Adminhtml_Reward_EarnedController extends Mage_Adminhtml_Controller_Action
{    
    /**
     * index Action
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('rewardpoints/reports/earned')
            ->_addBreadcrumb(
                $this->__('Reward Points'),
                $this->__('Reward Points')
            );
        $this->_title($this->__('Reward Points'))
            ->_title($this->__('Report'))
            ->_title($this->__('Earned Points'));
        
        $this->getLayout()->getBlock('rewardpointsreport.earned')
            ->setTitle($this->__('Point Earning Report'));
        
        $this->renderLayout();
    }
    
    /**
     * grid Action
     */
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
        $fileName   = 'earnedsreport.csv';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_earned_grid')
                           ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid items to XML file
     */
    public function exportXmlAction()
    {
        $fileName   = 'earnedsreport.xml';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_earned_grid')
                           ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * export grid items to XML Excel file
     */
    public function exportExcelAction()
    {
        $fileName   = 'earnedsreport.xml';
        $content    = $this->getLayout()
                           ->createBlock('rewardpointsreport/adminhtml_earned_grid')
                           ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    /**
     * get allowed report
     * 
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/reports');
    }
}
