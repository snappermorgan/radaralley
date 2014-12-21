<?php
/**
 * Wordpress SQL Utility Library
 * 
 * A group of functions to make it easier to work with mysql database SQL statements.
 * 
 * This code is very much in alpha phase, and should not be distributed with plugins 
 * other than by Dan Harrison. 
 * 
 *
 * Version History
 * 
 * V0.01 				 - Initial version released.
 * V0.02 				 - Added ability for multiple columns in WHERE clause in arrayToSQLUpdate()
 * V0.03 				 - Added backticks to column names to escape column names which might be reserved words.
 * V0.04 - 30th Oct 2010 - Added function to check for data value in a table.
 * V0.05 -  2nd Aug 2013 - Fixed to work with WordPress 3.6.
 * V0.06 - 22nd Nov 2013 - Added support for serializing array values in  arrayToSQLInsert() and arrayToSQLUpdate().
 * V0.07 - 25th Aug 2014 - Replaced deprecated esc_sql with $wpdb_prepare. 
 */

/**
 * Returns the correct SQL to INSERT the specified values as columnname => data into the specified
 * table, escaping all of the data values.
 * 
 * @param $tablename The name of the table to insert into.
 * @param $dataarray The list of values as columnname => data.
 * @return String Valid SQL to allow the specified values to be safely INSERTed into the database.
 */
if (!function_exists('arrayToSQLInsert')) { function arrayToSQLInsert($tablename, $dataarray)
{
	global $wpdb; 
	
	// Handle dodgy data
	if (!$tablename || !$dataarray || count($dataarray) == 0) {
		return false;	
	}
	
	$SQL = "INSERT INTO $tablename (";
	
	// Insert Column Names
	$columnnames = array_keys($dataarray);
	foreach ($columnnames AS $column) {
		$SQL .= sprintf('`%s`, ', $column);
	}
	
	// Remove last comma to maintain valid SQL
	if (substr($SQL, -2) == ', ') {
		$SQL = substr($SQL, 0, strlen($SQL)-2);
	}
	
	$SQL .= ") VALUES (";
	
	// Now add values, escaping them all
	foreach ($dataarray AS $columnname => $datavalue) 
	{
		// Serialise arrays before saving.
		if (is_array($datavalue)) {
			$datavalue = serialize($datavalue);
		}			
		
		$SQL .= $wpdb->prepare("%s, ", $datavalue);
	}
	
	// Remove last comma to maintain valid SQL
	if (substr($SQL, -2) == ', ') {
		$SQL = substr($SQL, 0, strlen($SQL)-2);
	}	
	
	return $SQL . ")";
}}

/**
 * Returns the correctly formed SQL to UPDATE the specified values in the database 
 * using the <code>$wherecolumn</code> field to determine which field is used as part 
 * of the WHERE clause of the SQL statement. The fields and data are specified in an 
 * array mapping columnname => data.
 * 
 * @param $tablename The name of the table to UPDATE.
 * @param $dataarray The list of values as columnname => data.
 * @param $wherecolumn The column to use in the WHERE clause.  
 * @return String Valid SQL to allow the specified values to be safely UPDATEed in the database.
 */
if (!function_exists('arrayToSQLUpdate')) { function arrayToSQLUpdate($tablename, $dataarray, $wherecolumn)
{
	global $wpdb; 
	
	// Handle dodgy data
	if (!$tablename || !$dataarray || !$wherecolumn || count($dataarray) == 0) {
		return false;	
	}
	
	$SQL = "UPDATE $tablename SET ";
		
	// Now add values, escaping them all
	foreach ($dataarray AS $columnname => $datavalue)
	{
		// Do all fields except column we're using on the WHERE part
		if ($columnname != $wherecolumn)
		{
			// Serialise arrays before saving.
			if (is_array($datavalue)) {
				$datavalue = serialize($datavalue);
			}
			
			$SQL .= $wpdb->prepare("`$columnname` = %s, ", $datavalue);
		}
	}
	
	// Remove last comma to maintain valid SQL
	if (substr($SQL, -2) == ', ') {
		$SQL = substr($SQL, 0, strlen($SQL)-2);
	}	
	
	// Now add the WHERE clause 
	// Have we got more than 1 item to add to WHERE clause?
	if (is_array($wherecolumn))
	{
		// Create list of fields/values in the WHERE clause
		$WHERE = '';
		for ($i = 0; $i < count($wherecolumn); $i++)
		{
			$WHERE .= $wpdb->prepare("`$wherecolumn[$i]` = %s AND ", $dataarray[$wherecolumn[$i]]);
		}
		
		// Always going to have a final AND, so strip that off now
		$WHERE = substr($WHERE, 0, -4);
		$SQL .= " WHERE ". $WHERE;
	}
	
	// Nope, just a single item
	else {	
		$SQL .= $wpdb->prepare(" WHERE `$wherecolumn` = %s", $dataarray[$wherecolumn]);
	}
	
	return $SQL;
}}

/**
 * Does a record exist for the specified table? Handles multiple fields for $field and $value 
 * if $field and $value are arrays.
 * 
 * @param String $table The name of the table to check.
 * @param String $field The field of the table to use as part of the search.
 * @param String $value The value of the field to use as part of the search.
 * @return Mixed The row of data if this row is found.
 */
if (!function_exists('doesRecordExistAlready')) { function doesRecordExistAlready($table, $field, $value)
{
	return getRecordDetails($table, $field, $value);
}}



/**
 * Get all of the details for the specified record.
 * 
 * @param String $table The name of the table to check.
 * @param String $field The field of the table to use as part of the search.
 * @param String $value The value of the field to use as part of the search.
 * @param String $returnType The type of data to return (ARRAY_A or OBJECT)
 * @return Mixed The row of data if this row is found.
 */
if (!function_exists('getRecordDetails')) { function getRecordDetails($table, $field, $value, $returnType = OBJECT)
{
	global $wpdb;
	$wpdb->show_errors();
	
	// We've got 2 arrays, so make sure we have the right number of elements.
	if (is_array($field) && is_array($value))
	{
		if (count($field) != count($value)) { 
			die('Error! Mismatched field count for checking record exists.');			
		}
	
		// Create list of fields/values in the WHERE clause
		$WHERE = '';
		for ($i = 0; $i < count($field); $i++)
		{
			$WHERE .= $wpdb->prepare("`$field[$i]` = %s AND ", $value[$i]);
		}
		
		// Always going to have a final AND, so strip that off now
		$WHERE = substr($WHERE, 0, -4);
		$SQL = sprintf("SELECT * FROM %s WHERE %s", $table, $WHERE);
	}
	
	// Just got a single pair of fields/values
	else {
		$SQL = $wpdb->prepare("SELECT * FROM $table WHERE `$field` = %s", $value);	
	}
	
	$rowToReturn = $wpdb->get_row($SQL, $returnType);
	
	// Nothing to return.
	if (!$rowToReturn) {
		return false;
	}
	
	// Dealing with an object to unserialise.
	if (OBJECT == $returnType)
	{
		$rowToReturnNew = new stdClass();
		
		// Need to create a new object with unserialised data for this to work.
		foreach ($rowToReturn as $field => $value) {
			// Unserialise each property into the new object.
			$rowToReturnNew->$field = maybe_unserialize($value);
		}
		
		$rowToReturn = $rowToReturnNew;
	}
	
	// Dealing with an array to unserialise.
	else 
	{
		// Unserialise each field
		foreach ($rowToReturn as $field => $value) {
			$rowToReturn[$field] = maybe_unserialize($value);
		}
	}

	return $rowToReturn;
}}
	
?>