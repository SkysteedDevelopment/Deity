<?php

class SiteVariable_config {
	
	
/****** Class Variables ******/
	public $classType = "standard";
	public $className = "SiteVariable";
	public $title = "Site Variable Tracker";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Tracks site-created variables as key-value pairs and categorizes them for better retrieval.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `site_variables`
		(
			`key_group`				varchar(22)					NOT NULL	DEFAULT '',
			`key_name`				varchar(32)					NOT NULL	DEFAULT '',
			`value`					text						NOT NULL	DEFAULT '',
			
			UNIQUE (`key_group`, `key_name`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if this plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $class->isInstalled();
	{
		// Make sure the newly installed tables exist
		return Database_Meta::columnsExist("site_variables", array("key_group", "key_name", "value"));
	}
	
}