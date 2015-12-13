<?php /*

----------------------------------------
------ About the Model_Tags Class ------
----------------------------------------

A "Tag Model" is used to add tags to a parent class. This model will automatically partition the tags so that they can be indexed properly, as well as handle the tagging behavior for you.

Tags have two attributes: Name and Value. Values are optional. If a value is not present, the tag is just a flag. For example, if a tag had the name "Planet" with no value, it would classify the object as being a planet.

A few examples of tags include:
	"City" => "New York"		// sets the entry as being located in "New York" in the "City" category
	"NPC"						// marks the entry as an NPC
	"Location"					// marks the entry as a location
	"Age Group" => "Adult"		// marks the entry as being an "Adult" within the "Age Group" category
	

There are a few differences between "Model" and "Model_Tags" classes, which are listed here:
	
	1. Tag models do NOT need a schema. They already know their exact mappings.
	
	2. You must set the $lookupClass to designate the parent class that will be assigned tags.
	
	3. You must set the $lookupKey to designate the key that will be assigned.
		- The "lookup key" is the column that will be used to identify the records associated with tags.
	
	4. The $tablePrefix value will be used to create the three tables necessary for the tagging structure.
	

-------------------------------------------------------
------ Example content for extending Model_Tags -------
-------------------------------------------------------

abstract class Example_Child extends Model_Tags {
	
	// Class Variables
	protected static $tablePrefix = "example_";		// <str> The prefix to append to the tables generated.
	
	protected static $lookupClass = "Example";		// <str> The class that we're mapping from.
	protected static $lookupKey = "example_id";		// <str> Table's lookup key (column); usually primary key.
	
}

*/

abstract class Model_Tags extends Model {
	
	
/****** Class Variables ******/
	protected static $tablePrefix = "example_";		// <str> The prefix to append to the tables generated.
	
	protected static $lookupClass = "";		// <str> The class that we're mapping from.
	protected static $lookupKey = "";		// <str> Table's lookup key (column); usually primary key.
	
	
/****** Create the database tables for this model ******/
	public static function createDatabaseTables (
	)					// RETURNS <void>
	
	// static::createDatabaseTables()
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `" . static::$tablePrefix . "_tags_data`
		(
			`tag_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`name`					varchar(22)					NOT NULL	DEFAULT '',
			`value`					varchar(45)					NOT NULL	DEFAULT '',
			
			PRIMARY KEY (`tag_id`),
			UNIQUE (`name`, `value`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `" . static::$tablePrefix . "_tags_by_object`
		(
			`object_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`tag_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`object_id`, `tag_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`object_id`) PARTITIONS 7;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `" . static::$tablePrefix . "_tags_by_tag`
		(
			`tag_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`object_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`tag_id`, `object_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`tag_id`) PARTITIONS 7;
		");
	}
}
