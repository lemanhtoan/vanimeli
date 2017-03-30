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
 * @package    Ves_BlockBuilder
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

/**
 * Ves BlockBuilder Extension
 *
 * @category   Ves
 * @package    Ves_BlockBuilder
 * @author     Venustheme Dev Team <venustheme@gmail.com>
 */
class Ves_BlockBuilder_Adminhtml_PagebuilderinstallController extends Mage_Adminhtml_Controller_Action{
	public function importAction(){
		$ves_import = Mage::helper('ves_blockbuilder/import');
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		$readConnection = $resource->getConnection('core_read');
		$action_type = $this->getRequest()->getParam('action_type');
		$data = $this->getRequest()->getParams();
		if(isset($action_type) && $action_type == 'import' && $data = $this->getRequest()->getParams()){
			$filePath = '';
			$fileContent = '';
			if(isset($_FILES['data_import_file']['name']) && $_FILES['data_import_file']['name'] != '')
			{	
				$fileContent = file_get_contents($_FILES['data_import_file']['tmp_name']);
			}else{
				$filePath = isset($data['file_path'])?$data['file_path']:"";
				if($filePath!=''){
					$filePath = str_replace("/", DS, $filePath);
					$filePath = Mage::getBaseDir() . DS . $filePath;
					$fileContent = file_get_contents($filePath);
				}
			}

			$importData = Mage::helper('core')->jsonDecode($fileContent);
			$store_id = $data['stores'];
			$overwrite = false;
			if($data['overwrite_blocks']){
				$overwrite = true;
			}
			if($importData!=''){
				try{
					foreach ($importData as $k => $sourceType) {
						if(!is_array($sourceType)) continue;
						foreach ($sourceType as $key => $source) {

							if($key == 'system_config'){
								foreach ($source as $section => $sections) {
									foreach ($sections as $column => $columns) {
										foreach ($columns as $field => $val) {
											$path = $section.'/'.$column.'/'.$field;

											if( $k == 'cmspages'){
												if($field == 'cms_home_page'){
													$page = Mage::getModel('cms/page')->getCollection()->addFieldToFilter('identifier',$val);
													if($page && $val!=Mage::getStoreConfig($path,$store_id)){
														Mage::getConfig()->saveConfig($path, $val, "stores", (int)$store_id );
													}
												}
											}
											if( $k != 'cmspages' && $val!=Mage::getStoreConfig($path,$store_id)){
												if($store_id==0){
													Mage::getConfig()->saveConfig($path, $val, "default", (int)$store_id );	
												}else{
													Mage::getConfig()->saveConfig($path, $val, "stores", (int)$store_id );
												}
											}
										}
									}
								}
							}

							if($key == 'tables'){
								foreach ($source as $tableName =>  $table) {
									$table_name = $resource->getTableName($tableName);
									if($table_name){
										$writeConnection->query("SET FOREIGN_KEY_CHECKS=0;");
										foreach ($table as $row) {
											$where = '';
											$query_data = $ves_import->buildQueryImport($row, $table_name, $overwrite, $store_id);
											$writeConnection->query($query_data[0].$where, $query_data[1]);
										}
										$writeConnection->query("SET FOREIGN_KEY_CHECKS=1;");
									}
								}
							}
						}
					}

					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_blockbuilder')->__('Import successfully'));

				}catch(Exception $e){

					Mage::logException($e);
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('cms')->__('An Error occured importing file.'));
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
					
				}
			}
		}
		$this->loadLayout();
		$this->_addContent($this->getLayout()->createBlock('ves_blockbuilder/adminhtml_install_import_edit'));
		$this->renderLayout();
	}

	public function exportAction(){
		$this->loadLayout();
		$data = $this->getRequest()->getParams();
		if($data && isset($data['action_type']) && $data['action_type'] == 'export'){
			$backupFileContent = array();
			$ves = Mage::helper('ves_blockbuilder');
			$ves_export = Mage::helper('ves_blockbuilder/export');

			// Export Modules
			if($exportModules = $ves_export->exportModules($data))
				$backupFileContent['modules'] = $exportModules;

			// Export Page Builder Profiles
			if($exportPageBuilderProfiles = $ves_export->exportBlockBuilderProfiles($data, "page"))
				$backupFileContent['pagebuilders'] = $exportPageBuilderProfiles;

			// Export Block Builder Profiles
			if($exportBlockBuilderProfiles = $ves_export->exportBlockBuilderProfiles($data))
				$backupFileContent['blockbuilders'] = $exportBlockBuilderProfiles;

			// Export Product Builder Profiles
			if($exportProductBuilderProfiles = $ves_export->exportBlockBuilderProfiles($data, "product"))
				$backupFileContent['productbuilders'] = $exportProductBuilderProfiles;

			// Export CSS Selector Profiles
			if($exportCssSelectorProfiles = $ves_export->exportCssSelectorProfiles($data))
				$backupFileContent['cssselectors'] = $exportCssSelectorProfiles;

			// Export Widgets
			if($exportWidgets = $ves_export->exportWidgets($data))
				$backupFileContent['widgets'] = $exportWidgets;

			// Export CMS Pages
			if($exportCmsPages = $ves_export->exportCmsPages($data))
				$backupFileContent['cmspages'] = $exportCmsPages;

			// Export Static Blocks
			if($exportStaticBlocks = $ves_export->exportStaticBlocks($data))
				$backupFileContent['staticblocks'] = $exportStaticBlocks;

			if(!empty($backupFileContent)){
				$folderTheme = isset($data['folder'])?$data['folder']:"";
				$importDir = Mage::getBaseDir() . DS . $folderTheme;

				$file_name = str_replace(" ", "_", $data['file_name']).'.json';
				$backupFileContent['created_at'] = date("m/d/Y h:i:s a", Mage::getModel('core/date')->timestamp(time()));
				$backupFileContent = Mage::helper('core')->jsonEncode($backupFileContent);

				if($data['isdowload']){
					$this->_sendUploadResponse($file_name, $backupFileContent);
				}else{
					$filePath = $importDir. DS . $file_name;
					try{
						$ves_export->writeSampleDataFile($importDir, $file_name, $backupFileContent);
						Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('ves_blockbuilder')->__('Successfully exported to file %s',$filePath));
					}catch (Exception $e){
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('ves_blockbuilder')->__('Can not save import sample file "%s".', $filePath));
						Mage::logException($e);
					}
				}
				$this->_redirect('*/*/export');
			}
		}
		$this->_addContent($this->getLayout()->createBlock('ves_blockbuilder/adminhtml_install_export_edit'));
		$this->renderLayout();
	}

	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
	{
		$response = $this->getResponse();
		$response->setHeader('HTTP/1.1 200 OK','');
		$response->setHeader('Pragma', 'public', true);
		$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
		$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
		$response->setHeader('Last-Modified', date('r'));
		$response->setHeader('Accept-Ranges', 'bytes');
		$response->setHeader('Content-Length', strlen($content));
		$response->setHeader('Content-type', $contentType);
		$response->setBody($content);
		$response->sendResponse();
		die;
	}

	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        $action = strtolower($this->getRequest()->getActionName());

        switch ($action) {
            case 'import':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/installsample/import');
                break;
            case 'export':
                return Mage::getSingleton('admin/session')->isAllowed('vesextensions/installsample/export');
                break;
            default:
            	return false;
            	break;
        }
    }
}