<?php

// Prepare the Configuration Details
$config = [
	
	'Version'			=> '1.0.0'
,	'Class Type'		=> 'standard'
	
,	'Title'				=> 'Entity Class'
,	'Description'		=> "Provides methods for interacting with database entity maps."
	
,	'Author'			=> 'Brint Paris'
,	'Website'			=> 'http://projectstarborn.com'
,	'License'			=> 'UniFaction License'
	
,	'SQL'				=> []
];

// Prepare the SQL
$config['SQL']['1.0.0'] = "
CREATE TABLE IF NOT EXISTS `entity_relationships`
(
	`entity_class`		varchar(32)					NOT NULL	DEFAULT '',
	`entity_name`		varchar(32)					NOT NULL	DEFAULT '',
	`attribute`			varchar(16)					NOT NULL	DEFAULT '',
	`related_name`		varchar(32)					NOT NULL	DEFAULT '',
	
	UNIQUE (`entity_class`, `entity_name`, `attribute`, `related_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`entity_class`, `entity_name`) PARTITIONS 31;

CREATE TABLE IF NOT EXISTS `entity_structure`
(
	`entity_class`		varchar(32)					NOT NULL	DEFAULT '',
	`structure`			text						NOT NULL	DEFAULT '',
	
	UNIQUE (`entity_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

// Return all Configuration Data
return $config;
