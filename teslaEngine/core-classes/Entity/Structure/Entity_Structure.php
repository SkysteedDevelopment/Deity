<?php /*

----------------------------------------------
------ About the Entity_Structure Class ------
----------------------------------------------

// Example of "structure"
// $class starts with uppercase, $dataType starts with lowercase
protected $structure = [
	
	// Ref			=> [$title, $class, $tags...]
	// Trait		=> [$title, $dataType, $length, $tags...]
	"firstName"		=> ["First Name", "string", 22],
	"lastName"		=> ["Last Name", "string", 22],
	"pet"			=> ["Pet", "Pet"]
	"home"			=> ["Home Address", "Location"],
	"work"			=> ["Work Address", "Location"],
	"locations"		=> ["Locations", "Location", self::MANY],
];



-------------------------------
------ Methods Available ------
-------------------------------


*/

class Entity_Structure {
	
	
/****** Class Variables ******/
	public $archetype = "trait";		// <str> "trait" or "class"
	public $datatype = "string";		// <str> The data type of this structure ("string", "variable", etc.)
	public $length = 10000;				// <int> The maximum length allowed for this structure.
	public $isMany = false;				// <bool> TRUE if there is a TO-MANY relationship allowed.
	
	public function isClass() { return $archetype == "trait" ? false : true; }
	public function isTrait() { return $archetype == "trait" ? true : false; }
}



