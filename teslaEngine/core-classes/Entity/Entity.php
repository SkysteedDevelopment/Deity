<?php /*

------------------------------------
------ About the Entity Class ------
------------------------------------

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

// Creating or calling an existing entity object
$johnSmith = new Person("John Smith");
$chicago = new Location("Chicago");

// Setting or creating traits
$johnSmith->firstName = "John";
$johnSmith->lastName = "Smith";
$johnSmith->work = "Chicago";		// References can point to the ID
$johnSmith->home = $chicago;		// References can also refer to the actual object

// Working with relationships
$johnSmith->addLocations("Chicago", "Detroit", "Miami", "Los Angeles");
$johnSmith->deleteLocations("Chicago", "New York");

// Getting relationships
$locations = $johnSmith->locations;
$myPet = $johnSmith->pet;


// Building Data
$jsonStr = '
{
	"Jerry Smith" : {
		"firstName" : "Jerry",
		"lastName" : "Smith",
		"myPet" : "Spot",
		"myLocation" : "New York",
		"Location" : [
			"Chicago",
			"San Francisco",
			"Madison",
			"Miami"
		]
	}
}';

Entity::build($jsonStr);


-------------------------------
------ Methods Available ------
-------------------------------


*/

class Entity {
	
	
/****** Class Variables ******/
	protected $structure = [];			// <str:array> The structure of the entity.
	protected $class = "";				// <str> The class name of the entity.
	protected $id = "";					// <str> The ID (string) of the entity.
	public $data = [];					// <array> The data of this entity.
	
	const AUTOSAVE = true;				// <bool> TRUE to automatically save data, FALSE if not.
	
	// Structure Tags
	const MANY = "HAS_MANY";			// <str> This tag means there are many instances of this object allowed.
	
	
/****** Construct the Entity Object ******/
	public function __construct
	(
		$entityID = ""	// <str> The ID of the Entity object being initialized.
	,	$full = false	// <bool> TRUE to get the full object, including relationships.
	)					// RETURNS <void>
	
	// $johnSmith = new Person("John Smith");		// "Person" must extend "Entity"
	{
		// Prepare Variables
		$this->class = get_called_class();
		$this->id = $entityID ? Sanitize::text($entityID) : "_" . Security_Hash::value(microtime(), 12);
		
		// Attempt to retrieve this object
		if(!$data = Database::selectValue("SELECT `data` FROM `entity_" . $this->class . "` WHERE `id`=? LIMIT 1", [$this->id]))
		{
			$data = '[]';
			if(!Database::query("INSERT INTO `entity_" . $this->class . "` (id, data) VALUES (?, ?)", [$this->id, json_encode([])]))
			{
				// Create the table if it doesn't exist
				Database::exec("
				CREATE TABLE IF NOT EXISTS `entity_" . $this->class . "`
				(
					`id`				varchar(32)					NOT NULL	DEFAULT '',
					`data`				text						NOT NULL	DEFAULT '',
					
					UNIQUE (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`id`) PARTITIONS 7;");
				
				Database::query("INSERT INTO `entity_" . $entityClass . "` (id, data) VALUES (?, ?)", [$this->id, $data]);
			}
		}
		
		$this->data = json_decode($data, true);
		$this->data['id'] = $this->id;
		
		// If the object is supposed to retrieve all objects
		if($full)
		{
			// Get all of the entity's relationships
			$results = Database::selectMultiple("SELECT attribute, related_id FROM `entity_relationships` WHERE entity_class=? AND entity_id=?", [$class, $entityID]);
			
			foreach($results as $result)
			{
				$this->data[$result['attribute']][] = $result['related_id'];
			}
		}
	}
	
	
/****** Get data from the entity ******/
	public function __get
	(
		$attribute		// <str> The entity attribute to get the value of.
	)					// RETURNS <mixed> the value of the designated attribute.
	
	// $entity->nameOfTrait				// Returns the trait
	// $entity->nameOfRelationship		// Returns the relationship
	{
		// If we can't locate the structure, just return the value as a trait without a data type
		if(!isset($this->structure[$attribute]) && isset($this->data[$attribute]))
		{
			return $this->data[$attribute];
		}
		
		// Prepare Structure Values
		list($title, $class, $dataType, $length, $tags) = $this->getAttributeStructure($attribute);
		
		// If the entry is a class
		if($class)
		{
			// If the class doesn't exist, return an empty object
			if(!class_exists($class))
			{
				return new stdClass();
			}
			
			// If this is a TO-MANY relationship, return a list of object
			if(isset($tags[self::MANY]))
			{
				return $this->getRelationships($class);
			}
			
			// For TO-ONE relationships, return one object
			return new $class($this->data[$attribute]);
		}
		
		// If we can't retrieve an attribute, return null
		else if(!isset($this->data[$attribute]))
		{
			return null;
		}
			
		// The entry is a data type - we can sanitize or handle it here
		// TODO: Add sanitizing & length restraints
		
		// Standard behavior is to just return the value of the attribute
		return $this->data[$attribute];
	}
	
	
/****** Set data into the entity ******/
	public function __set
	(
		$attribute		// <str> The entity attribute to add data to.
	,	$value			// <mixed> The data to add to the attribute.
	)					// RETURNS <mixed>
	
	// $entity->nameOfTrait = $value;		// Sets the trait's value
	{
		// If we can't locate the structure, just set the value exactly as it's provided
		if(!isset($this->structure[$attribute]))
		{
			$this->data[$attribute] = $value;
		}
		
		// The structure was located - identify it's behaviors
		else
		{
			// Prepare Structure Values
			list($title, $class, $dataType, $length, $tags) = $this->getAttributeStructure($attribute);
			
			// If the entry is a class
			if($class)
			{
				// If the class doesn't exist, end here
				if(!class_exists($class))
				{
					throw new Exception("The class `" . $class . "` does not exist."); return;
				}
				
				// If this is a TO-MANY relationship, prevent this from being set
				if(isset($tags[self::MANY]))
				{
					throw new Exception("Cannot use setter to create a TO-MANY relationship."); return;
				}
				
				// Make sure the entity's ID is valid
				if(isset($value->id))
				{
					// For TO-ONE relationships, set the object
					$this->data[$attribute] = $value->id;
				}
			}
			
			// The entry is a data type - we can sanitize or handle it here
			else
			{
				// TODO: Add sanitizing & length restraints
				
				// Set the value
				$this->data[$attribute] = $value;
			}
		}
		
		// If we're auto-saving data, save now
		if(self::AUTOSAVE) { $this->save(); }
	}
	
	
/****** Call a function for the entity ******/
	public function __call
	(
		$name			// <str> A special function being called to the entity.
	,	$arguments		// <int:mixed> The arguments passed to the function.
	)					// RETURNS <mixed> the value of the designated attribute.
	
	// $entity->setLocations("Chicago", "New York");
	{
		// If we're calling an "addRelationship" method
		if(strpos($name, "add") === 0 && ctype_upper($name[3]))
		{
			// Get the name of the attribute; e.g. "locations", "user", etc.
			$attrName = lcfirst(substr($name, 3));
			
			// Get the class name associated with the attribute
			$class = $this->getAttributeClass($attrName);
			
			// Add the relationship
			return $this->addRelationship($class, $arguments);
		}
		
		// If we're calling a "deleteRelationship" method
		if(strpos($name, "delete") === 0 && ctype_upper($name[6]))
		{
			// Get the name of the related class; e.g. "locations", "user", etc.
			$attrName = lcfirst(substr($name, 6));
			
			// Get the class name associated with the attribute
			$class = $this->getAttributeClass($attrName);
			
			// Delete the relationship
			return $this->deleteRelationship($class, $arguments);
		}
		
		return $this;
	}
	
	
/****** Delete the entity ******/
	public function remove (
	)					// RETURNS <void>
	
	// $entity->delete();
	{
		self::delete($this->id);
	}
	
	
/****** Save data on the entity ******/
	public function save (
	)					// RETURNS <void>
	
	// $entity->save();
	{
		self::update($this->id, $this->data);
	}
	
	
/****** Get the structure details of an attribute ******/
	public function getAttributeStructure
	(
		$attribute		// <str> The attribute to get structure details for.
	)					// RETURNS <int:mixed> An array of structure details.
	
	// list($title, $class, $dataType, $settings, $tags) = $entity->getAttributeStructure($attribute);
	// [$title, $class, $dataType, $settings, $tags]
	{
		// If we can't locate the structure, just set the value exactly as it's provided
		if(!isset($this->structure[$attribute]))
		{
			return [ucwords($attribute), '', 'string', 10000, []];
		}
		
		// Prepare Structure Values
		$structure = $this->structure[$attribute];
		
		// If the entry is a class
		if(ctype_upper($structure[1][0]))
		{
			// Prepare the list of tags
			$tagList = [];
			
			if(count($structure) > 2)
			{
				$tags = array_slice($structure, 2);
				
				foreach($tags as $tag)
				{
					$tagList[$tag] = $tag;
				}
			}
			
			return [$structure[0], $structure[1], "", 0, $tagList];
		}
		
		// If the entity is a trait
		$tags = array_slice($structure, 3);
		$tagList = [];
		
		foreach($tags as $tag)
		{
			$tagList[$tag] = $tag;
		}
		
		// Prepare settings
		$settings = [];
		
		if(isset($structure[2]))
		{
			$settings["Minimum Length"] = (int) $structure[2];
		}
		
		return [$structure[0], "", $structure[1], $settings, $tagList];
	}
	
	
/****** Get the class of an attribute ******/
	public function getAttributeClass
	(
		$attribute		// <str> The attribute to get structure details for.
	)					// RETURNS <str> The class associated with the attribute.
	
	// $class = $entity->getAttributeClass($attribute);
	{
		if(isset($this->structure[$attribute]) && ctype_upper($this->structure[$attribute][1][0]))
		{
			return $this->structure[$attribute][1];
		}
		
		return "";
	}
	
	
/****** Get an entity's structure relationships ******/
	public function getEntityStructureRelationships (
	)					// RETURNS <str:str> A list of relationship attributes.
	
	// $relationshipAttrs = $entity->getEntityStructureRelationships();
	{
		$relationshipAttrs = [];
		
		// Loop through structures to identify the related attributes
		foreach($this->structure as $attr)
		{
			list($title, $attrClass, $dataType, $settings, $tags) = $this->getAttributeStructure($attr[0]);
			
			if($attrClass)
			{
				$relationshipAttrs[$attr] = $this->structure[$attr];
			}
		}
		
		return $relationshipAttrs;
	}
	
	
/****** Check if an entity record exists ******/
	public static function exists
	(
		$entityID		// <str> The entity ID to update.
	)					// RETURNS <bool> TRUE if the record exists, FALSE if not.
	
	// Entity::exists($entityID);
	{
		$class = get_called_class();
		
		return (bool) Database::selectValue("SELECT id FROM `entity_" . $class . "` WHERE id=? LIMIT 1", [$entityID]);
	}
	
	
/****** Update the entity ******/
	protected static function update
	(
		$entityID		// <str> The entity ID to update.
	,	$data			// <array> The data to add to the entity.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Entity::update($entityID, $data);
	{
		$class = get_called_class();
		
		return Database::query("REPLACE INTO `entity_" . $class . "` (id, data) VALUES (?, ?)", [$entityID, json_encode($data)]);
	}
	
	
/****** Delete the entity ******/
	protected static function delete
	(
		$entityID		// <str> The entity ID to update.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Entity::delete($entityID);
	{
		$class = get_called_class();
		
		return Database::query("DELETE FROM `entity_" . $class . "` WHERE id=? LIMIT 1", [$entityID]);
	}
	
	
/****** Prepare an index hash for the entity relationships ******/
	private static function prepIndexHash
	(
		$entityName		// <str> The name of the entity in the index.
	,	$entityID		// <str> The ID of the entity record.
	,	$attribute		// <str> The attribute to relate from (e.g. ".locations").
	)					// RETURNS <mixed>
	
	// $indexHash = self::prepIndexHash($entityName, $entityID, $attribute);
	{
		return Security_Hash::value($entityName . $entityID . $attribute, 16);
	}
	
	
/****** Get the list of relationships ******/
	private function getRelationships
	(
		$attribute		// <str> The attribute to relate from (e.g. ".locations").
	)					// RETURNS <mixed>
	
	// $locations = $this->getRelationships($attribute);
	// $locations = $this->getRelationships("Location");
	{
		$indexHash = self::prepIndexHash($this->class, $this->id, $attribute);
		$list = [];
		
		$results = Database::selectMultiple("SELECT related_id FROM `entity_relationships` WHERE index_hash=?", [$indexHash]);
		
		foreach($results as $result)
		{
			$list[$result['related_id']] = new $attribute($result['related_id']);
		}
		
		return $list;
	}
	
	
/****** Add a relationship from one entity to another ******/
	private function addRelationship
	(
		$attribute		// <str> The attribute to relate from (e.g. ".locations").
	,	$arguments		// <array> The list of entities to add.
	)					// RETURNS <mixed>
	
	// $person->addRelationship($attribute, ["Chicago", "New York"]);
	{
		$indexHash = self::prepIndexHash($this->class, $this->id, $attribute);
		
		Database::startTransaction();
		
		// Loop through all of the related entities to add
		foreach($arguments as $entityID)
		{
			Database::query("REPLACE INTO `entity_relationships` (index_hash, related_id) VALUES (?, ?)", [$indexHash, $entityID]);
		}
		
		Database::endTransaction();
		
		return $this;
	}
	
	
/****** Delete an entity's relationship ******/
	private function deleteRelationship
	(
		$attribute		// <str> The attribute used to make the relationship (e.g. ".locations").
	,	$arguments		// <array> The list of entities to delete.
	)					// RETURNS <mixed>
	
	// $person->deleteRelationship("Location", ["Chicago", "New York"])
	{
		$indexHash = self::prepIndexHash($this->class, $this->id, $attribute);
		
		Database::startTransaction();
		
		// Loop through all of the related entities to add
		foreach($arguments as $entityID)
		{
			Database::query("DELETE FROM `entity_relationships` WHERE index_hash=? AND related_id=? LIMIT 1", [$indexHash, $entityID]);
		}
		
		Database::endTransaction();
		
		return $this;
	}
	
	
/****** Get all entities ******/
	public static function getAll (
	)					// RETURNS <array> The entire entity list.
	
	// $entityList = Entity::getAll();
	{
		// Prepare Variables
		$class = get_called_class();
		$entityList = [];
		
		$results = Database::selectMultiple("SELECT id, data FROM entity_" . $class, []);
		
		foreach($results as $result)
		{
			$entityList[$result['id']] = json_decode($result['data'], true);
		}
		
		return $entityList;
	}
	
	
/****** Return a list of entries inside a group of categories ******/
	public static function categorize
	(
		$entityList		// <array> The list of entities to sort.
	,	$categoryAttr	// <str> The attribute to categorize by.
	)					// RETURNS <array> The entity list sorted by the desired type.
	
	// $catList = Entity::categorize($entityList, $categoryAttr);
	{
		$catList = [];
		
		foreach($entityList as $entity)
		{
			$cat = isset($entity[$categoryAttr]) ? $entity[$categoryAttr] : "unsorted";
			
			$catList[$cat][] = $entity;
		}
		
		ksort($catList);
		
		return $catList;
	}
	
	
/****** Sort a list of entities ******/
	public static function sort
	(
		$entityList		// <array> The list of entities to sort.
	,	$sortAttr		// <str> The attribute to sort by.
	,	$asc = true		// <bool> TRUE to sort by ascending order, FALSE to sort by descending.
	)					// RETURNS <array> The entity list sorted by the desired type.
	
	// $sortedList = Entity::sort($entityList, $sortAttr, $asc = true);
	{
		uksort($entityList, function($a, $b) use ($entityList, $sortAttr) {
			
			// If the sort attributes don't exist
			if(!isset($entityList[$a][$sortAttr])) { return 1; }
			if(!isset($entityList[$b][$sortAttr])) { return -1; }
			
			return (($entityList[$a][$sortAttr] > $entityList[$b][$sortAttr]) ? 1 : -1);
		});
		
		return $entityList;
	}
	
	
/****** Build an entity (a full list of records) from JSON data ******/
	public static function buildDatabase
	(
		$buildData		// <mixed> The JSON string or array to build from.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// Entity::buildDatabase($buildData);
	{
		var_dump($buildData);
		// Prepare Values
		if(!is_array($buildData))
		{
			$buildData = json_decode($buildData, true);
		}
		
		$class = get_called_class();
		
		Database::startTransaction();
		
		// Loop through JSON entries
		foreach($buildData as $entityID => $entry)
		{
			$prepData = [];
			
			foreach($entry as $attribute => $value)
			{
				// Relationships (start with uppercase letter)
				if(ctype_upper($attribute[0]))
				{
					$indexHash = self::prepIndexHash($this->class, $this->id, $attribute);
					
					foreach($value as $relatedEntity)
					{
						Database::query("REPLACE INTO `entity_relationships` (index_hash, related_id) VALUES (?, ?)", [$indexHash, $relatedEntity]);
					}
					
					continue;
				}
				
				$prepData[$attribute] = $value;
			}
			
			static::update($entityID, $prepData);
		}
		
		return Database::endTransaction();
	}
	
	
/****** Output the JSON data for an entity record ******/
	public static function output
	(
		$entityID		// <str> The ID of the entity to output.
	)					// RETURNS <str> The JSON string of the entity record.
	
	// $jsonStr = Entity::output($entityID);
	{
		// Prepare Values
		$class = get_called_class();
		$entity = new $class($entityID);
		$data = $entity->data;
		$structure = $entity->structure;
		$sqlIn = [];
		
		// Loop through structures and get the related attributes
		$relationshipAttrs = $entity->getEntityStructureRelationships();
		
		foreach($relationshipAttrs as $attr)
		{
			$indexHash = self::prepIndexHash($class, $entityID, $attr);
			$results = Database::selectMultiple("SELECT related_id FROM `entity_relationships` WHERE index_hash=?", [$indexHash]);
			
			foreach($results as $result)
			{
				$data[$attr][] = $result['related_id'];
			}
		}
		
		return json_encode($data, JSON_PRETTY_PRINT);
	}
	
	
/****** Output the JSON data for the entire entity list ******/
	public static function outputAllAsArray (
	)					// RETURNS <array> The array of entity records.
	
	// $outputArray = Entity::outputAllAsArray();
	{
		// Prepare Values
		$class = get_called_class();
		$entityRecords = [];
		
		// Get the full list of entity records
		$records = Database::selectMultiple("SELECT id, data FROM entity_" . $class, []);
		
		foreach($records as $record)
		{
			$entityRecords[$record['id']] = json_decode($record['data'], true);
		}
		
		// Get Relationships
		$results = Database::selectMultiple("SELECT entity_id, attribute, related_id FROM `entity_relationships` WHERE entity_class=?", [$class]);
		
		foreach($results as $result)
		{
			$entityRecords[$result['entity_id']][$result['attribute']][] = $result['related_id'];
		}
		
		return $entityRecords;
	}
	
	
/****** Import all data for this entity ******/
	public static function importToDatabase
	(
		$importDir		// <str> The directory to import data from.
	)					// RETURNS <str> The JSON string of the entity.
	
	// Entity::importToDatabase($importDir = "");
	{
		// Prepare Values
		$class = get_called_class();
		$buildData = [];
		$fileList = Dir::getFiles($importDir);
		
		// Loop through each file and retrieve the contents
		foreach($fileList as $file)
		{
			$fileContents = file_get_contents($importDir . '/' . $file);
			$id = str_replace(".json", "", $file);
			
			$buildData[$id] = json_decode($fileContents, true);
		}
		
		// Run the build process
		static::build($buildData);
	}
	
	
/****** Export all data for this entity ******/
	public static function exportFromDatabase
	(
		$saveDir = ""	// <str> If set, this saves each entry to the designated folder.
	)					// RETURNS <str> The JSON string of the entity.
	
	// Entity::exportFromDatabase($saveDir = "");
	{
		// Prepare Values
		$class = get_called_class();
		$entityRecords = [];
		
		// Get the full list of entity records
		$records = Database::selectMultiple("SELECT id, data FROM entity_" . $class, []);
		
		foreach($records as $record)
		{
			$entityRecords[$record['id']] = json_decode($record['data'], true);
		}
		
		// Get Relationships
		$results = Database::selectMultiple("SELECT entity_id, attribute, related_id FROM `entity_relationships` WHERE entity_class=?", [$class]);
		
		foreach($results as $result)
		{
			$entityRecords[$result['entity_id']][$result['attribute']][] = $result['related_id'];
		}
		
		// If we have a save directory set, save this output to it
		if($saveDir)
		{
			foreach($entityRecords as $entityID => $entity)
			{
				File::create($saveDir . "/" . $class . "/" . $entityID . ".json", json_encode($entity, JSON_PRETTY_PRINT));
			}
		}
		
		return json_encode($entityRecords, JSON_PRETTY_PRINT);
	}
}
