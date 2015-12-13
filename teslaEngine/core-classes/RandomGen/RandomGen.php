<?php /*

---------------------------------------
------ About the RandomGen Class ------
---------------------------------------

There are five types of "Generators" for the RandomGen class. Basically, each Generator is randomly generated result.
The result generated can be a string, another generator, a function (which ultimately returns a string), etc. Some
also have combined behaviors of multiple generators (known as "combo" generators).

Every generator is a 'dot.syntax' string, and saved in 'dot/syntax' equivalent file. These files store the generator
object in JSON. The generator objects store information that allows calling them to generate the final result.

There are a few critical elements in generator objects:

	$object->byReference[]	is a list of references to other generators (e.g. "Names.First.Male.Hungarian") with weights
	$object->byString[]		is a list of strings that could be generated (weight of 1 each)
	$object->byCombo[]		is a list of strings that might need additional processing (weight of 1 each)
	$object->byFunction[]	is a list of function calls to return the string (weight of 1 each)


------------------------------------
// How to store generator objects //
------------------------------------

References
	=> "Names.First.Male.Hungarian"

Strings
	=> "String"

Functions (start with "!")
	=> "!Rand:weight:1:2"

Combo (contains < and >)
	=> "Acquire <Names.Powers.Type> Power with <!Power:abilites:someValues>"

Map (starts with '{' and is valid JSON)
	=> {"Combat":{"Health":"<!Rand:weighted:9:3>"}}

-----------------------------------------
------ Examples in RandomGen Class ------
-----------------------------------------

// Create a new Generator
$gen = new RandomGen("Names.First.Male");
$gen->byReference = ["Names.First.Male.Hungarian", "Names.First.Male.Italian"];
$gen->byString = ["Jack", "Joe", "Fred"];
$gen->byCombo = ["Edgar {Names.Last.Hungarian}", "Mario {Names.Last.Italian}"];
$gen->parents = ["Names.First"];
$gen->related = ["Names.Last", "Names.First.Female"];
$gen->save();

// Update a generator
$gen = new RandomGen("Names.First.Male");
$gen->add("Names.First.Male.Greek");
$gen->add("Names.First.Male.American");
$gen->add("Alex", "Bob");
$gen->add("John {Names.Last}");
$gen->save();

// Display results of a generator
$gen = new RandomGen("Names.First.Male");
echo $gen->rand();


// Create a Generator Map
$orcGenerator = [
	
	// Combat Stats
	"Combat" => [
		"Health" => "<Rand:weighted:9:3>",
		"Soak" => "<Rand:number:1:1:2:2:2>",
		"Armor" => "<Rand:string:Flimsy Armor:Torn Leather Armor:Light Armor:Decent Armor:Makeshift Armor>",
		"Weapon" => "<Rand:string:Sword:Mace:Heavy Club:Makeshift Club:Makeshift Blade:Makeshift Axe:Large Club:Spear>",
		"Damage" => "<Rand:weighted:2:2>",
	],
	
	// Skills
	"Skills" => [
		
		"Warrior" => [
			"Body" => "<Rand:weighted:2:2>",
			"Combat" => "<Rand:weighted:2:2>",
			"Ranged" => "<Rand:weighted:0:2>",
			"Willpower" => "<Rand:weighted:2:2>",
		],
		
		"Rogue" => [
			"Athletics" => "<Rand:weighted:2:2>",
			"Larceny" => "<Rand:weighted:-1:2>",
			"Stealth" => "<Rand:weighted:-1:2>",
			"Survival" => "<Rand:weighted:0:2>",
		],
		
		"Scholar" => [
			"Craft" => "<Rand:weighted:-2:2>",
			"Intelligence" => "<Rand:weighted:-2:2>",
			"Perception" => "<Rand:weighted:-1:2>",
			"Studies" => "<Rand:weighted:-2:2>",
		],
		
		"Noble" => [
			"Charisma" => "<Rand:weighted:-1:2>",
			"Diplomacy" => "<Rand:weighted:-1:2>",
			"Performance" => "<Rand:weighted:-2:2>",
			"War" => "<Rand:weighted:2:2>",
		],
	],
];

$orcMap = RandomGen::runMap($orcGenerator);

var_dump($orcMap);

-------------------------------
------ Methods Available ------
-------------------------------


*/

class RandomGen {
	
	
/****** Class Variables ******/
	
	// Static Values
	public static $baseDir = "random-gen";	// <str> The base directory to save & load generators in.
	
	// Class Values
	public $generatorID = "";		// <str> The ID of this generator.
	public $referenceWeight = 0;	// <int> The weight of the references for this generator.
	
	// Generation values
	public $byReference = [];	// <str:int> A key of references (e.g. "Names.First.Male.Hungarian"), and their weights
	public $byString = [];		// <int:str> An array of strings (e.g. "Jack", "Joe")
	public $byCombo = [];		// <int:str> An array of combos (e.g. "Jack {Names.Last.Hungarian}")
	public $byFunction = [];	// <int:str> An array of function generators (e.g. "<Rand:weighted:2:2>")
	
	// Relationships to this generator
	public $parents = [];		// <int:str> A list of parents to this generator (e.g. "Names")
	public $related = [];		// <int:str> A list of related generators (e.g. "Names.Last", "Names.First.Female")
	
	
/****** Construct the generator object ******/
	public function __construct
	(
		$generatorID	// <str> The ID of the generator.
	)					// RETURNS <void>
	
	// $gen = new RandomGen($generatorID);
	{
		$this->generatorID = $generatorID;
		
		// Prepare the generation string
		$generationStr = str_replace(".", "/", $generatorID);
		
		// Get the ID
		if(File::exists(APP_PATH . "/" . self::$baseDir . "/" . $generationStr . ".json"))
		{
			$json = File::read(APP_PATH . "/" . self::$baseDir . "/" . $generationStr . ".json");
			$obj = json_decode($json, true);
			
			// Load the properties
			foreach(['byReference', 'byString', 'byCombo', 'byFunction', 'parents', 'related'] as $prop)
			{
				if(isset($obj[$prop]))
				{
					$this->$prop = $obj[$prop];
				}
			}
			
			// Loop through all references to identify the reference weight
			$cnt = 0;
			
			foreach($this->byReference as $refName => $refWeight)
			{
				$cnt += $refWeight;
			}
			
			$this->referenceWeight = $cnt;
		}
	}
	
	
/****** Save the object ******/
	public function save (
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// $gen = $this->save();
	{
		// Prepare the generation string
		$generationStr = str_replace(".", "/", $this->generatorID);
		
		// Prepare the object
		$obj = [
			'byReference' => $this->byReference,
			'byString' => $this->byString,
			'byCombo' => $this->byCombo,
			'byFunction' => $this->byFunction,
			'parents' => $this->parents,
			'related' => $this->related
		];
		
		$jsonStr = json_encode($obj, JSON_PRETTY_PRINT);
		
		// Save the object
		return File::write(APP_PATH . "/" . self::$baseDir . "/" . $generationStr . ".json", $jsonStr);
	}
	
	
/****** Check if a generator exists ******/
	public static function exists
	(
		$generatorID	// <str> The generator ID to test.
	)					// RETURNS <bool> TRUE if it exists, FALSE otherwise.
	
	// RandomGen::exists("Names.First");
	{
		// Anything after the ":" is a weighted value.
		$exp = explode(":", $generatorID);
		
		// The first portion of the reference name uses "." syntax
		$generationStr = str_replace(".", "/", $exp[0]);
		
		return File::exists(APP_PATH . "/" . self::$baseDir . "/" . $generationStr . ".json");
	}
	
	
/****** Add a generator to the list ******/
	public function add
	(
		/* Args */		// <mixed> A string, or array of string of values to add to the generator.
	)					// RETURNS <void>
	
	// $gen = $this->add("Names.First");
	// $gen = $this->add("Joe");
	// $gen = $this->add("Joe {Names.Last}");
	{
		$args = func_get_args();
		
		// Loop through all of the values to add
		foreach($args as $value)
		{
			// Find which type of generator it is.
			switch(self::checkGenType($value))
			{
				case "function":
					$this->byFunction[] = $value; break;
				
				case "map":
					$this->byMap[] = $value; break;
				
				case "combo":
					if(!in_array($value, $this->byCombo)) {
						$this->byCombo[] = $value;
					}
					break;
				
				case "reference":
					$exp = explode(":", $value);
					$this->byReference[$exp[0]] = (isset($exp[1]) ? (int) $exp[1] : 1);
					break;
				
				case "string":
					$this->byString[] = $value;
			}
		}
	}
	
	
/****** Return a random generator ******/
	public static function returnGenerator
	(
		$genString	// <str> The generator string to use.
	)				// RETURNS <str> The generator result.
	
	// $result = RandomGen::returnGenerator("Names.First");
	{
		switch(self::checkGenType($genString))
		{
			case "function":
				return self::returnFunction($genString);
			
			case "string":
				return $genString;
			
			case "combo":
				return self::returnCombo($genString);
			
			case "reference":
				$randGen = new RandomGen($genString);
				return $randGen->rand();
		}
		
		return "~Generator " . $genString . " Failed~";
	}
	
	
/****** Check the type of generator being used ******/
	public static function checkGenType
	(
		$genString	// <str> The generator string to test.
	)				// RETURNS <str> The type of generator.
	
	// $typeOfGenerator = RandomGen::checkGenType("Names.First");
	// $typeOfGenerator = RandomGen::checkGenType("!Rand:weighted:1:3");
	{
		// If the generator starts with "!", it's a function.
		if($genString[0] == "!")
		{
			return "function";
		}
		
		// If the generator starts with "{", it's a map.
		if($genString[0] == "{")
		{
			return "map";
		}
		
		// If the generator includes a < and >, it's a combo.
		if(strpos($genString, "<") !== false && strpos($genString, ">") !== false)
		{
			return "combo";
		}
		
		// If the generator includes a "." followed by uppercase character, it's a reference.
		// Or if the generator locates a ":" followed by a number, it's a reference
		$pos = strpos($genString, ".");
		
		if($pos !== false and isset($genString[$pos + 1]) and ctype_upper($genString[$pos + 1]))
		{
			return "reference";
		}
		
		$pos = strpos($genString, ":");
		
		if($pos !== false and isset($genString[$pos + 1]) and is_numeric($genString[$pos +1]))
		{
			return "reference";
		}
		
		// Otherwise, the generator is a string
		return "string";
	}
	
	
/****** Add a generator reference to the list ******/
	public function addReference
	(
		$value		// <str> The value to add to the generator.
	,	$weight = 1	// <int> The weight to give to the reference (how frequently it gets called)
	)				// RETURNS <void>
	
	// $gen = $this->addReference("Names.First", 15);
	{
		$this->byReference[$value] = $weight;
	}
	
	
/****** Return a random value ******/
	public function rand (
	)				// RETURNS <str> The generated value.
	
	// $gen = $this->rand();
	{
		// Get number of generators present
		$refMax = $this->referenceWeight;
		$strMax = count($this->byString) + $refMax;
		$cmbMax = count($this->byCombo) + $strMax;
		$funcMax = count($this->byFunction) + $cmbMax;
		
		// If there are no generators present, return an empty string
		if($funcMax == 0) { return '~' . $this->generatorID . ':No Generators Present~'; }
		
		// Determine which type of generator will be used: Reference, String, or Combo
		$val = mt_rand(0, $funcMax - 1);
		
		// A "Reference" generator will be used
		if($val < $refMax)
		{
			return $this->randReference();
		}
		
		// A "String" generator will be used
		if($val < $strMax)
		{
			return $this->randString();
		}
		
		// A "Combo" generator will be used
		if($val < $cmbMax)
		{
			return $this->randCombo();
		}
		
		// A "Function" generator will be used
		return $this->randFunction();
	}
	
	
/****** Return a result from a random reference generator ******/
	public function randReference (
	)				// RETURNS <str> The generated value.
	
	// $this->randReference();
	{
		$cnt = 0;
		
		// We need to select a reference by their weights
		// Loop through each reference and determine which one is the right choice
		foreach($this->byReference as $refName => $refWeight)
		{
			$cnt += $refWeight;
		}
		
		// Now we randomize which one we'd choose
		$rnd = mt_rand(0, $cnt - 1);
		$cnt = 0;
		
		foreach($this->byReference as $refName => $refWeight)
		{
			$cnt += $refWeight;
			
			if($rnd < $cnt)
			{
				// Return the result
				$randGen = new RandomGen($refName);
				return $randGen->rand();
			}
		}
		
		return '~' . $this->generatorID . ':Reference Check Failed~';
	}
	
	
/****** Return a result from a random string generator ******/
	public function randString (
	)				// RETURNS <str> The generated value.
	
	// $this->randString();
	{
		$cnt = count($this->byString);
		
		// If we don't have any string generators, return an empty string
		if($cnt == 0) { return '~' . $this->generatorID . ':No String Generators Present~'; }
		
		// Return a random string
		return $this->byString[mt_rand(0, $cnt - 1)];
	}
	
	
/****** Return a result from a random combo generator ******/
	public function randCombo (
	)				// RETURNS <str> The generated value.
	
	// $this->randCombo();
	{
		$cnt = count($this->byCombo);
		
		// If we don't have any combo generators, return an empty string
		if($cnt == 0) { return '~' . $this->generatorID . ':No Combo Generators Present~'; }
		
		// Prepare a random generator
		$rnd = mt_rand(0, $cnt - 1);
		$comboString = $this->byCombo[$rnd];
		
		return self::returnCombo($comboString);
	}
	
	
/****** Return a result from a random function generator ******/
	public function randFunction (
	)				// RETURNS <str> The generated value.
	
	// $this->randFunction();
	{
		$cnt = count($this->randFunction);
		
		// If we don't have any function generators, return an empty string
		if($cnt == 0) { return '~' . $this->generatorID . ':No Function Generators Present~'; }
		
		// Prepare a random generator
		$rnd = mt_rand(0, $cnt - 1);
		$functionStr = $this->byFunction[$rnd];
		
		return self::returnFunction($functionStr);
	}
	
	
/****** Return a result from a random combo generator ******/
	public static function returnCombo
	(
		$comboString	// <str> The string passed to generate the reference.
	)					// RETURNS <str> The generated value.
	
	// $generatedValue = RandomGen::returnCombo($comboString);
	{
		// Use regex to match everything between < and >
		preg_match_all("/\<(.*?)\>/si", $comboString, $match);
		
		// Loop through each result
		foreach($match[1] as $extractedGenID)
		{
			// Access each generator contained with these tags and replace the original string
			$result = RandomGen::returnGenerator($extractedGenID);
			
			$comboString = str_replace("<" . $extractedGenID . ">", $result, $comboString);
		}
		
		return $comboString;
	}
	
	
/****** Return a result from a random function generator ******/
	public static function returnFunction
	(
		$functionStr	// <str> The string passed to generate the function call.
	)					// RETURNS <str> The generated value.
	
	// $generatedValue = RandomGen::returnFunction($functionStr);
	{
		// Trim and explode the content to get the appropriate parts
		$functionStr = ltrim($functionStr, "!");
		$funcArgs = explode(":", $functionStr);
		
		$class = array_shift($funcArgs);
		$method = array_shift($funcArgs);
		
		return call_user_func_array(["RandomGen_" . $class, $method], $funcArgs);
	}
	
	
/****** Runs a generator for every key in an array ******/
	public static function runMap
	(
		$generatorMap	// <array> An array that maps out a full generator to construct.
	)					// RETURNS <str> The generated value.
	
	// RandomGen::runMap($generatorMap);
	{
		foreach($generatorMap as $key => $value)
		{
			$generatorMap[$key] = is_array($value) ? self::runMap($value) : self::returnGenerator($value);
		}
		
		return $generatorMap;
	}
}

