<?php if (!defined('BASEPATH')) exit('No direct script access allowed');  


/**
 *
 * Store Processcedure
 * - - - - - - - - - - - - - - - - - - - - - - - -
 *
 * DELIMITER $$
 *
 * USE `databasename`$$
 *
 * DROP PROCEDURE IF EXISTS `QueryExecutor`$$
 *
 * CREATE DEFINER=`root`@`localhost` PROCEDURE `QueryExecutor`(IN `QueryString` TEXT)
 * BEGIN
 *        SET @query := CONCAT( QueryString);
 *        PREPARE stmt FROM @query;
 *        EXECUTE stmt;
 *        DEALLOCATE PREPARE stmt;   
 * END$$
 * DELIMITER;
 *
 * 
 * 
 * Create Table Schema
 * - - - - - - - - - - - - - - - - - - - - - - - -
 * "create_table" => array(
 *     "table" => 'table',
 *     "schema" => "schema"
 * ),
 * 
 * 
 * Drop Table Schema
 * - - - - - - - - - - - - - - - - - - - - - - - -
 * "drop_table" => array(
 *     "table" => 'table',
 *     "schema" => "schema"
 * ),
 * 
 * 
 * Constraints Columns Schema
 * - - - - - - - - - - - - - - - - - - - - - - - -
 * ALERT:
 * "schema_primary" => "ALTER TABLE `table` ADD PRIMARY KEY(`column`);"
 * "schema_index"   => "ALTER TABLE `table` ADD INDEX(`column`)"
 * 
 * "primary_keys" => array(
 *     "table" => 'table',
 *     "columns" => 'column1, column2, column3',
 *     "schema" => "schema"
 * ),
 *
 * "index_keys" => array(
 *     "table" => 'table',
 *     "columns" => 'column1, column2, column3',
 *     "schema" => "schema"
 * ),
 *
 * 
 * Add Columns Schema
 * - - - - - - - - - - - - - - - - - - - - - - - -
 * 
 * Example 1:
 * "add_columns" => array(
 *     "table" => 'table',
 *     "columns" => 'column1, column2, column3',
 *     "schema" => "schema"
 * ),
 * 
 * 
 * Drop Columns Schema
 * - - - - - - - - - - - - - - - - - - - - - - - -
 * ALERT:
 * "schema" => "ALERT: ALTER TABLE `table` DROP `column`;"
 * 
 * Example 1:
 * "drop_columns" => array(
 *     "table" => 'table',
 *     "columns" => 'column1, column2, column3',
 *     "schema" => "schema"
 * ),
 *
 * Example 2:
 * "drop_columns" => array(
 *     "table" => 'table',
 *     "columns" => array('column1', 'column2', 'column3'),
 *     "schema" => "schema"
 * ),
 * 
 */

class Migrations
{
	private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function migrations()
    {
    	$excepts = [        
    		"alter"
    	];

    	$surface = array();

    	return $surface;
    }

	//deprecated //ReturnFirst
    public function compileQuries( $number )
    {
    	$migrations = $this->migrations();

    	if( !(count($migrations) >= $number) ) {
    		return [];
    	}

    	return $migrations;
    }

    public function runMigrate( $collation )
    {
    	$migrations     = $this->compileQuries($collation);
    	$compile 		= 0;

    	$this->ci->db->db_debug = FALSE;

    	foreach($migrations as $key => $value)
    	{
    		switch ( $key )
    		{
    			case 'create_table':

	    			$is_exists  = $this->_checkIfExistsTableSchema($value['table']);

	    			if( TRUE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;

    			case 'drop_table':

	    			$is_exists  = $this->_checkIfExistsTableSchema($value['table']);

	    			if( FALSE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;

    			case 'primary_keys':

	    			$is_exists  = $this->_checkIfExistsConstraintsSchema($value['table'], $value['columns'], 'primary');

	    			if( TRUE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;

    			case 'index_columns':

	    			$is_exists  = $this->_checkIfExistsConstraintsSchema($value['table'], $value['columns'], 'index');

	    			if( TRUE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;

    			case 'add_columns':

	    			$is_exists  = $this->_checkIfExistsColumnSchema($value['table'], $value['columns']);

	    			if( TRUE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;

    			case 'drop_columns':

	    			$is_exists  = $this->_checkIfExistsColumnSchema($value['table'], $value['columns']);

	    			if( FALSE !== (bool) $is_exists ) {
	    				$transaction = (bool) $this->ci->db->query("call QueryExecutor(".$this->ci->db->escape($value['schema']).")");

	    				$compile += 1;
	    			}

    			break;
    		}
    	}

    	$this->ci->db->update(TABLE_SETTINGS, array(
    		'values' => $compile
    	),
    	array(
            'key' => 'migrations'
        ));
    }

	private function _checkIfExistsTableSchema( $table)
	{
		$total = $this->ci->db->select('count(*) as count')->get_where('information_schema.COLUMNS', array(
			'TABLE_SCHEMA' => $this->ci->db->database,
			'TABLE_NAME' => $table
		), 1)->row_array();

		return $total['count'] ?: 0;
	}

	private function _checkIfExistsColumnSchema($table, $columns)
	{
		$columns = (is_array($columns) ? implode($columns, ',') : $columns);
		$columns = preg_replace('/\s+/', '', $columns);

		$this->ci->db->group_start();
		$this->ci->db->where_in('COLUMN_NAME', explode(',', $columns));
		$this->ci->db->group_end();

		$total = $this->ci->db->select('count(*) as count')->get_where('information_schema.COLUMNS', array(
			'TABLE_SCHEMA' => $this->ci->db->database,
			'TABLE_NAME' => $table
		), 1)->row_array();

		return $total['count'] ?: 0;
	}

	private function _checkIfExistsConstraintsSchema($table, $columns, $relation)
	{
		$columns = (is_array($columns) ? implode($columns, ',') : $columns);
		$columns = preg_replace('/\s+/', '', $columns);

		$this->ci->db->group_start();

		$this->ci->db->where_in('COLUMN_NAME', explode(',', $columns));

		switch ($relation)
		{
			case 'primary':
				$this->ci->db->where('COLUMN_KEY', 'PRI');
			break;

			case 'index':
				// $this->ci->db->where('EXTRA', 'auto_increment');
			break;

			case 'autoIncrement':
				$this->ci->db->where('EXTRA', 'auto_increment');
			break;
		}

		$this->ci->db->group_end();

		$total = $this->ci->db->select('values')->get_where('information_schema.COLUMNS', array(
			'TABLE_SCHEMA'  => $this->ci->db->database,
			'TABLE_NAME'    => $table
		), 'count(*) as count');

		return $total['count'] ?: 0;
	}
}