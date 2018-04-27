<?php

function db() {
    global $db;
	
	if (!empty($db)) {
		return $db;
	}
    
	try {
		if(DbType=="mysql"){
			$db = new PDO("mysql:dbname=".DbName.";host=".DbAddress .";port=" .DbPort.";charset=".DbCharset, DbUsername,DbPassword); 
		}else if(DbType=="oracle" || DbType=="oracle12"){
			$db = new PDO("oci:dbname=//".DbAddress.":".DbPort."/".DbName.";charset=".DbCharset, DbUsername,DbPassword); 
		}
	} catch (PDOException $ex) {
		Error('20001','sql connect fail: ' . $ex->getMessage());
	}

	return $db;
}

function dbQuery($sql){
	if (empty($sql)) {
		return;
	}
	
	if (!db()->query($sql)) {
	    Error('20001','sql error: '.$sql);
	}
	
	$data = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
	return $data;
}