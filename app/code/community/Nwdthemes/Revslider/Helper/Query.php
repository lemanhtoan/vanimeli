<?php

/**
 * Nwdthemes Revolution Slider Extension
 *
 * @package     Revslider
 * @author		Nwdthemes <mail@nwdthemes.com>
 * @link		http://nwdthemes.com/
 * @copyright   Copyright (c) 2015. Nwdthemes
 * @license     http://themeforest.net/licenses/terms/regular
 */

class Nwdthemes_Revslider_Helper_Query extends Mage_Core_Helper_Abstract {

	const ARRAY_A = 'ARRAY_A';

	public $prefix = '';
	public $base_prefix = '';
	public $last_error = '';
	public $last_query = '';
	public $insert_id = '';

	/**
	 *	Get query results
	 *
	 *	@param	string	Query
	 *	@param	string	Result format
	 *	@return	array
	 */
	public function get_results($query, $mode = self::ARRAY_A) {

		$queryArray = explode('FROM', $query);
		if (count($queryArray) < 2) {
			Mage::log('DB ERROR: ' . $query, null, 'nwd.log');
			die();
		}
		$queryArray = explode('WHERE', $queryArray[1]);
		$table = trim($queryArray[0]);
		$collection = Mage::getModel($table)->getCollection();
		
		$where = isset($queryArray[1]) ? trim($queryArray[1]) : '';
		if ($where)
		{
			if (strpos($where, '!=') === false)
			{
				list($field, $value) = explode('=', $where);
				$collection->addFieldToFilter(trim($field, '`"\' '), trim($value, '"\' '));
			}
			else
			{
				list($field, $value) = explode('!=', $where);
				$collection->addFieldToFilter(trim($field, '`"\' '), array('neq' => trim($value, '"\' ')));
			}
		}

		$response = array();
		foreach ($collection as $_item) {
			$response[] = $_item->getData();
		}
		return $response;
	}

	/**
	 *	Get query row
	 *
	 *	@param	string	Query
	 *	@param	string	Result format
	 *	@return	array
	 */
	public function get_row($query, $mode = self::ARRAY_A) {
		preg_match('#\b(nwdrevslider/\w+)\b#', $query,	$modelNames);
		$resource = Mage::getSingleton('core/resource');
		foreach ($modelNames as $modelName) {
			$query = str_replace($modelName, $resource->getTableName($modelName), $query);
		}
		$readConnection = $resource->getConnection('core_read');
		$result = $readConnection->fetchRow($query);
		return $mode == self::ARRAY_A ? $result : (object) $result;
	}

	/**
	 *	Insert row
	 *
	 *	@param	string	Table name
	 *	@param	array	Data
	 *	@return	int
	 */

	public function insert($table, $data = array()) {
		$model = Mage::getModel($table)->setData($data);
		try {
			$model->save();
		} catch (Exception $e) {
			$this->throwError($e->getMessage());
		}
		$this->lastRowID = $model->getId();
		return $this->lastRowID;
	}

	/**
	 *	Update row
	 *
	 *	@param	string	Table name
	 *	@param	array	Data
	 *	@param	array	Where
	 */

	public function update($table, $data = array(), $where) {
		if (is_array($where) && $where)
		{
			$collection = Mage::getModel($table)->getCollection();
			foreach ($where as $_field => $_value) {
				$collection->addFieldToFilter($_field, $_value);
			}
			$item = $collection->getFirstItem();
			try {
				$item
					->addData($data)
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
	 *	Delete row
	 *
	 *	@param	string	Table name
	 *	@param	array	Data
	 *	@param	array	Where
	 */

	public function delete($table, $where) {
		UniteFunctionsRev::validateNotEmpty($table,"table name");
		UniteFunctionsRev::validateNotEmpty($where,"where");
		$collection = Mage::getModel($table)->getCollection();
		foreach ($where as $field => $value) {
			$collection->addFieldToFilter($field, $value);
		}
		foreach ($collection as $_item) {
			$_item->delete();
		}
	}

	/**
	 *	Prepare query
	 *
	 *	@param	string	Query
	 *	@param	mixed	Args
	 *	@return	array
	 */
	public function prepare($query, $args) {
		$args = func_get_args();
		array_shift( $args );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $args[0] ) && is_array($args[0]) )
			$args = $args[0];
		$query = str_replace( "'%s'", '%s', $query ); // in case someone mistakenly already singlequoted it
		$query = str_replace( '"%s"', '%s', $query ); // doublequote unquoting
		$query = preg_replace( '|(?<!%)%f|' , '%F', $query ); // Force floats to be locale unaware
		$query = preg_replace( '|(?<!%)%s|', "%s", $query ); // quote the strings, avoiding escaped strings like %%s
		array_walk( $args, array( $this, 'escape_by_ref' ) );
		return @vsprintf( $query, $args );
	}

	public function escape_by_ref(&$arg) {
		if( (string)(int)$arg != $arg) $arg = Mage::getSingleton('core/resource')->getConnection('default_write')->quote($arg);
	}
	
	/**
	 *	Run sql query
	 *
	 *	param	string	Query
	 */

	public function query($query) {
		preg_match('#\b(nwdrevslider/\w+)\b#', $query,	$modelNames);
		$resource = Mage::getSingleton('core/resource');
		foreach ($modelNames as $modelName) {
			$query = str_replace($modelName, $resource->getTableName($modelName), $query);
		}
		$writeConnection = $resource->getConnection('core_write');
		$result = $writeConnection->query($query);
		$this->insert_id = $writeConnection->lastInsertId();
		return $result;
	}

	/**
	 *	Run sql query and get result variable
	 *
	 *	param	string	Query
	 *	return	var
	 */

	public function get_var($query) {
		preg_match('#\b(nwdrevslider/\w+)\b#', $query,	$modelNames);
		$resource = Mage::getSingleton('core/resource');
		foreach ($modelNames as $modelName) {
			$query = str_replace($modelName, $resource->getTableName($modelName), $query);
		}
		$readConnection = $resource->getConnection('core_read');
		return $readConnection->fetchOne($query);
	}

	/**
	 *	Generate DB alter query by difference
	 *
	 *	@param  string  Query
	 *	@param  boolean Is Execute
	 *	@return string
	 */

	public function dbDelta($sql, $isExecute = true) {
        return false;
	}

}