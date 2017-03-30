<?php
class NWT_Languagepack_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/languagepack?id=15 
    	 *  or
    	 * http://site.com/languagepack/id/15 	
    	 */
    	/* 
		$languagepack_id = $this->getRequest()->getParam('id');
  		if($languagepack_id != null && $languagepack_id != '')	{
			$languagepack = Mage::getModel('languagepack/languagepack')->load($languagepack_id)->getData();
		} else {
			$languagepack = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($languagepack == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$languagepackTable = $resource->getTableName('languagepack');
			
			$select = $read->select()
			   ->from($languagepackTable,array('languagepack_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$languagepack = $read->fetchRow($select);
		}
		Mage::register('languagepack', $languagepack);
		*/
			
		$this->loadLayout();     
		$this->renderLayout();
    }
}