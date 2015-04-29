<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

if(Me::$id != 43)
	exit;

$issue = Database::selectMultiple("SELECT * FROM creatures_types", array());
foreach ($issue as $i)
{
	if($i['evolves_from'] == 0)
		continue;
	$fix = (int) Database::selectValue("SELECT id FROM creatures_types WHERE family=? AND evolution_level=? AND prefix=? LIMIT 1", array($i['family'], (int) $i['evolution_level']-1, $i['prefix']));
	if($i['evolves_from'] != $fix)
	{
		Database::query("UPDATE creatures_types SET evolves_from=? WHERE id=? LIMIT 1", array($fix, $i['id']));
		echo Database::showQuery("UPDATE creatures_types SET evolves_from=? WHERE id=? LIMIT 1", array($fix, $i['id'])) . "<br/>";
	}
}

$pets = Database::selectMultiple("SELECT e.type_id, c.rarity AS cr FROM creatures_types c INNER JOIN basket_creatures e ON c.id=e.type_id WHERE c.rarity!=e.rarity AND c.rarity!=18", array());
foreach($pets as $pet)
{
	print_r($pet);
	echo "<br/>";
	Database::query("UPDATE basket_creatures SET rarity=? WHERE type_id=? LIMIT 1", array($pet['cr'], $pet['type_id']));
}

echo "<hr/>";

$unused = array();
$eggs = Database::selectMultiple("SELECT id, family, prefix, rarity FROM creatures_types WHERE evolution_level=?", array(1));
foreach($eggs as $egg)
	if($egg['rarity'] != 20 || substr($egg['prefix'], 0, 5) == "Noble" || substr($egg['prefix'], 0, 7) == "Exalted")
		$unused[$egg['id']] = array($egg['family'], $egg['prefix']);
	
$basket = Database::selectMultiple("SELECT type_id FROM creatures_types t INNER JOIN basket_creatures b ON t.id=b.type_id", array());
foreach($basket as $b)
	if(isset($unused[$b['type_id']]))
		unset($unused[$b['type_id']]);

$basket = Database::selectMultiple("SELECT type_id FROM creatures_types t INNER JOIN explore_creatures b ON t.id=b.type_id", array());
foreach($basket as $b)
	if(isset($unused[$b['type_id']]))
		unset($unused[$b['type_id']]);
	
$basket = Database::selectMultiple("SELECT type_id FROM creatures_types t INNER JOIN shop_creatures b ON t.id=b.type_id", array());
foreach($basket as $b)
	if(isset($unused[$b['type_id']]))
		unset($unused[$b['type_id']]);
	
foreach($unused as $key => $u)
{
	$base = MyCreatures::getTypeID($u[0], "Egg", MyCreatures::petRoyalty($u[1]));
	echo "[#" . $key . " / #" . $base . "] " . $u[0] . " " . $u[1] . "<br/>";
}

/*$old = Database::selectMultiple("SELECT * FROM old_creature_types", array());
foreach($old as $o)
{
	$colors1 = Database::selectMultiple("SELECT color FROM old_creature_color WHERE family=?", array($o['creature_family']));
	$colors2 = Database::selectMultiple("SELECT color FROM old_creature_color_shop WHERE type_id=?", array($o['id']));
	$colors = array("");
	foreach($colors1 as $c)
		$colors[] = ($c == "" ? $c : ucfirst(substr($c['color'], 0, -1)));
	foreach($colors2 as $c)
		$colors[] = ($c == "" ? $c : ucfirst(substr($c['color'], 0, -1)));
	$colors = array_unique($colors);
	$royalty = array("");
	if(!$o['deny_noble'])
		$royalty = array("", "Noble", "Exalted");
	foreach($royalty as $r)
	{
		if($basicTypeID = MyCreatures::getTypeID($o['creature_family'], $o['creature_name'], ""))
			$basic = MyCreatures::petTypeData($basicTypeID);
		
		foreach($colors as $c)
		{
			$c = ($c ? ($r ? $r . " " . $c : $c) : $r);
			$typeID = MyCreatures::getTypeID($o['creature_family'], $o['creature_name'], $c);
			if(!$typeID)
			{
				if($basicTypeID)
				{
					$special = 0;
					if($r == "Noble") $special = 2;
					if($r == "Exalted") $special = 3;
					$fromTypeID = 0;
					
					if($basic['evolves_from'] == 0)
					{
						$fromTypeID = 0;
						$basicfrom = MyCreatures::petTypeData((int) $basic['evolves_from']);
					}
					else
						$fromTypeID = MyCreatures::getTypeID($o['creature_family'], 'Egg', $c);
					
					Database::query("INSERT INTO creatures_types (family, name, evolution_level, required_points, rarity, blurb, description, gender, evolves_from, prefix) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($basic['family'], $basic['name'], $basic['evolution_level'], $basic['required_points'], $basic['rarity'] + $special, $basic['blurb'], $basic['description'], $basic['gender'], $fromTypeID, $c));
					echo $o['creature_family'] . " " . $o['creature_name'] . " (" . $c . ") [#" . Database::$lastID . "]<br/>";
				}
			}
		}
	}
}*/