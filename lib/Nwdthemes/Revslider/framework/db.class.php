<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2015 ThemePunch
 */

if( !defined( 'Nwdthemes_Revslider_Helper_Framework::ABSPATH') ) exit();

class RevSliderDB{
	
	private $lastRowID;
	
	/**
	 * 
	 * constructor - set database object
	 */
	public function __construct(){
	}

	/**
	 * 
	 * throw error
	 */
	private function throwError($message,$code=-1){
		RevSliderFunctions::throwError($message,$code);
	}
	
	//------------------------------------------------------------
	// validate for errors
	private function checkForErrors($prefix = ""){
		global $wpdb;
		
		if($wpdb->last_error !== ''){
			$query = $wpdb->last_query;
			$message = $wpdb->last_error;
			
			if($prefix) $message = $prefix.' - <b>'.$message.'</b>';
            if($query) $message .=  '<br>---<br> Query: ' . Mage::helper('nwdrevslider/framework')->esc_attr($query);
			
			$this->throwError($message);
		}
	}
	
	
	/**
	 * 
	 * insert variables to some table
	 */
	public function insert($table,$arrItems) {

		$model = Mage::getModel($table)->setData($arrItems);
		try {
			$model->save();
		} catch (Exception $e) {
			$this->throwError($e->getMessage());
		}

		$this->lastRowID = $model->getId();

		return $this->lastRowID;
	}
	
	/**
	 * 
	 * get last insert id
	 */
	public function getLastInsertID(){
		global $wpdb;

		$this->lastRowID = $wpdb->insert_id;
		return($this->lastRowID);
	}


	/**
	 *
	 * delete rows
	 */
	public function delete($table,$where){

		UniteFunctionsRev::validateNotEmpty($table,"table name");
		UniteFunctionsRev::validateNotEmpty($where,"where");

		list($field, $value) = explode('=', $where);
		$collection = Mage::getModel($table)->getCollection();
		$collection->addFieldToFilter(trim($field, '"\' '), trim($value, '"\' '));
		foreach ($collection as $_item) {
			$_item->delete();
		}
	}


	/**
	 *
	 * run some sql query
	 */
	public function runSql($query){
		global $wpdb;

		$wpdb->query($query);
		$this->checkForErrors("Regular query error");
	}


	/**
	 * 
     * run some sql query
     */
    public function runSqlR($query){
        global $wpdb;
        
        $return = $wpdb->get_results($query, Nwdthemes_Revslider_Helper_Query::ARRAY_A);
        
        return $return;
    }
    
    
    /**
     * 
	 * insert variables to some table
	 */
	public function update($table,$arrItems,$where){

		if (is_array($where) && $where)
		{
			$collection = Mage::getModel($table)->getCollection();
			foreach ($where as $_field => $_value) {
				$collection->addFieldToFilter($_field, $_value);
			}
			$item = $collection->getFirstItem();
			try {
				$item
					->addData($arrItems)
					->setId( $item->getId() )
					->save();
			} catch (Exception $e) {
				$this->throwError($e->getMessage());
			}
		}
		else
		{
			$this->throwError('No id provided.');
		}

		return true;
	}
	
	
	/**
	 * 
	 * get data array from the database
	 * 
	 */
	public function fetch($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){

		$resource = Mage::getSingleton('core/resource');

		$query = 'SELECT * FROM `' . $resource->getTableName($tableName) . '`';
		if ($where)
		{
			$query .= ' WHERE ' . $where;
		}
		if ($orderField)
		{
			$query .= ' ORDER BY ' . $orderField;
		}

		$result = $resource->getConnection('core_read')->fetchAll($query);
		
		return $result;
	}
	
	/**
	 * 
	 * fetch only one item. if not found - throw error
	 */
	public function fetchSingle($tableName,$where="",$orderField="",$groupByField="",$sqlAddon=""){
		$response = $this->fetch($tableName, $where, $orderField, $groupByField, $sqlAddon);
		
		if(empty($response))
			$this->throwError("Record not found");
		$record = $response[0];
		return($record);
	}
	
	/**
	 * 
     * prepare statement to avoid sql injections
	 */
    public function prepare($query, $array){
        global $wpdb;

        $query = $wpdb->prepare($query, $array);

        return($query);
	}
	
}

/**
 * old classname extends new one (old classnames will be obsolete soon)
 * @since: 5.0
 **/
class UniteDBRev extends RevSliderDB {}
?>