<?php /*

---------------------------------------
------ About the HashStore Class ------
---------------------------------------



*/

abstract class HashStore {
	
	
/****** Prepare Variables ******/
	
	
/****** Load My Data ******/
	public static function generateHash
	(
		$jsonObject		// <str> The serialized JSON object to generate a hash from.
	)					// RETURNS <str> The hash to save.
	
	// $hash = HashStore::generateHash($jsonObject);
	{
		return str_replace(['+', '=', '/'], '', base64_encode(hash("sha1", $jsonObject, true)));
	}
	
	
/****** Load My Data ******/
	public static function saveObject
	(
		$object		// <mixed> The object to save.
	)				// RETURNS <str> The hash to save.
	
	// HashStore::saveObject($object);
	{
		// Save the object as JSON
		$obj = json_encode($object);
		
		// Identify the hash of the object
		$hash = self::generateHash($obj);
		
		// Save the JSON object into the appropriate file
		
	}
}
