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
 * @package     Magestore_RewardPoints
 * @module     RewardPoints
 * @author      Magestore Developer
 *
 * @copyright   Copyright (c) 2016 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

/**
 * Rewardpointscsv Adminhtml Controller
 *
 * @category    Magestore
 * @package     Magestore_RewardPointsCsv
 * @author      Magestore Developer
 */
class Magestore_RewardPointsCsv_Adminhtml_RewardpointscsvController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_RewardPointsCsv_Adminhtml_RewardpointscsvController
     */
    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/rewardpointscsv')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('RewardPoints Information'), Mage::helper('adminhtml')->__('RewardPoints Information')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }

    public function importAction() {
        $this->loadLayout()
                ->_setActiveMenu('rewardpoints/rewardpointscsv');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('rewardpointscsv/adminhtml_rewardpointscsv_import'));
        $this->_title($this->__('Point Balance'))
                ->_title($this->__('Import Points'));
        $this->renderLayout();
    }

    /**
     * @return $this
     */
    public function processImportAction() {
        if (isset($_FILES['filecsv']['name']) && $_FILES['filecsv']['name'] != '') {
            try {
                /* Starting upload */
                $uploader = new Varien_File_Uploader('filecsv');

                // Any extention would work
                $uploader->setAllowedExtensions(array('csv'));
                $uploader->setAllowRenameFiles(false);

                // Set the file upload mode 
                // false -> get the file directly in the specified folder
                // true -> get the file in the product like folders 
                //  
                $uploader->setFilesDispersion(false);

                // We set media as the upload dir
//                $path = Mage::getBaseDir('var') . DS . 'tmp' . DS;
//                $result = $uploader->save($path, $_FILES['filecsv']['name']);
                $fileName = $_FILES['filecsv']['tmp_name'];

                if (isset($fileName) && $fileName != '') {
                    $csvObject = new Varien_File_Csv();
                    $dataFile = $csvObject->getData($fileName);
                    $customerData = array();
                    foreach ($dataFile as $row => $cols) {
                        if ($row == 0) {
                            $fields = $cols;
                        } else {
                            $customerData[] = array_combine($fields, $cols);
                        }
                    }
                }
                if (isset($customerData) && count($customerData)) {
                    $cnt = $this->_updateCustomer($customerData);
                    $cntNot = count($customerData) - $cnt;
                    $successMessage = $this->__('Imported total %d customer point balance(s)', $cnt);
                    if ($cntNot) {
                        $successMessage .= "</br>";
                        $successMessage .= $this->__("There are %d emails which don\'t belong to any accounts.", $cntNot);
                    }
                    if ($this->getRequest()->getParam('print')) {
                        $url = $this->getUrl('*/*/massPrint', array(
                            'rewardpointscsv' => implode(',', $customerData)
                        ));
                        $successMessage .= "<script type='text/javascript'>document.observe('dom:loaded',function(){window.location.href = '$url';});</script>";
                    }
                    Mage::getSingleton('adminhtml/session')->addSuccess($successMessage);
                    $this->_redirect('*/*/index');
                    return $this;
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Point balance imported'));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('No uploaded files'));
        }
        $this->_redirect('*/*/import');
    }

    /**
     * @param $customerData
     * @return int
     */
    protected function _updateCustomer($customerData) {
        $collection = array();
        $website = Mage::getSingleton('adminhtml/system_config_source_website')->toOptionArray();
        $website[] = array(
            'value' => 0,
            'label' => 'Admin'
        );
        foreach ($customerData as $key => $value) {
            $website_id = Mage::app()->getDefaultStoreView()->getWebsiteId();
            foreach ($website as $key => $id) {
                if ($id['label'] == $value['Website']) {
                    $website_id = $id['value'];
                    break;
                }
            }
            $email = $value['Email'];
            $pointBalance = $value['Point Change'];
            $expireAfter = $value['Points expire after'];
            $customerExist = $this->_checkCustomer($email, $website_id);
            if (!$customerExist || !$customerExist->getId()) {
                continue;
            }
            $customerExist->setPointBalance($pointBalance)
                    ->setExpireAfter($expireAfter);
            $collection[] = $customerExist;
        }
        Mage::getResourceModel('rewardpoints/transaction')->importPointFromCsv($collection);
        return count($collection);
    }

    /**
     * check customer exist by email
     * @param type $email
     * @param type $website_id
     * @return type
     */
    protected function _checkCustomer($email, $website_id = 1) {
        return Mage::getModel('customer/customer')->setWebsiteId($website_id)->loadByEmail($email);
    }

    /**
     * download simple csv
     */
    public function downloadSampleAction() {
        $filename = Mage::getBaseDir('media') . DS . 'rewardpointscsv' . DS . 'import_point_balance_sample.csv';
        $this->_prepareDownloadResponse('import_point_balance_sample.csv', file_get_contents($filename));
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        $fileName = 'rewardpointscsv.csv';
        $content = $this->getLayout()
                ->createBlock('rewardpointscsv/adminhtml_rewardpointscsv_grid')
                ->setIsCSV(true)
                ->getCSV();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
        $fileName = 'rewardpointscsv.xml';
        $content = $this->getLayout()
                ->createBlock('rewardpointscsv/adminhtml_rewardpointscsv_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * @return mixed
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('rewardpoints/rewardpointscsv');
    }

}
