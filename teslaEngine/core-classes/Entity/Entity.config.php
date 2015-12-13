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
	`index_hash`		varchar(16)					NOT NULL	DEFAULT '',
	`related_id`		varchar(32)					NOT NULL	DEFAULT '',
	
	UNIQUE (`index_hash`, `related_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (`index_hash`, `entity_id`) PARTITIONS 31;
";

// Return all Configuration Data
return $config;
