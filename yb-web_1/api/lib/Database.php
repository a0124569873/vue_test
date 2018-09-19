<?php

require_once("PDO.php");

function tableName($table_name) {
    return DbTablePrefix.$table_name;
}

function IsNumber($v) {
	return is_int($v) || is_long($v) || is_float($v) || is_double($v);
}

function whereStr($options){
	if (empty($options) || !is_array($options)) {
		return '';
	}

	$wheres = array();
    foreach ($options as $key=>$value) {
		$k = addslashes($key);

		if (empty($value) && $value !== '0') {
			continue;
		}

		if (is_string($value)) {
			$v = addslashes($value);
			$wheres[] = " $k'$v'";
		} else if (is_bool($value) || IsNumber($value)) {
			$wheres[] = " $k $value";
		}
    }
    if(count($wheres) == 0){
    	return "";
    }
    return " WHERE ".implode(" AND ",$wheres);
}
function selectStr($table,$values){
	$table = tableName($table);
	return "SELECT $values FROM $table";
}
function deleteStr($table){
	$table = tableName($table);
	return "DELETE FROM $table ";
}
function updateStr($table,$values){

	if (empty($values) || !is_array($values)) {
		return '';
	}
	
	$table = tableName($table);
	$sets = array();
    foreach ($values as $key=>$value) {
		$k = addslashes($key);
		if (empty($value) && $value !== 0) {
			continue;
			// $sets[] = "'$values";
		}

		if (is_string($value) && !empty($value)) {
			$v = addslashes($value);
			$sets[] = " $k='$v'";
		} else if (is_bool($value) || IsNumber($value)) {
			$sets[] = " $k=$value";
		}
    }
    return "UPDATE $table SET ".implode(",",$sets);	
}
function insertStr($table,$values){
	if (empty($values) || !is_array($values)) {
		return '';
	}

	$table = tableName($table);
	$sets = array();
    foreach ($values as $value) {
		if (empty($value) && $value !== '0' && $value !== 0) {
			continue;
			// $sets[] = "'$value'";
		}

		if (is_string($value) && !empty($value)) {
			$v = addslashes($value);
			$sets[] = "'$v'";
		} else if (is_bool($value) || IsNumber($value)) {
			$sets[] = "$value";
		}else {
			$sets[] = "$value";
		}
    }
    return "INSERT INTO $table(".implode(",",array_keys($values)).") VALUES(" .implode(",",$sets).")";	
}
