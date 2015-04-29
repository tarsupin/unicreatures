<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the MyBlocks Plugin ------
--------------------------------------

This plugin allows for consistent use of common site elements, such as the avatar or inventory.


-------------------------------
------ Methods Available ------
-------------------------------

MyBlocks::avatar($userData);
MyBlocks::inventory($uniID);
MyBlocks::pet($pet, $petType, $handle);
MyBlocks::topnav($handle, $url[0]);

*/

abstract class MyBlocks {
	
	
/****** Show the chosen avatar ******/
	public static function avatar
	(
		$userData		// <str:mixed> The data of the user that you're getting the avatar for.
	)					// RETURNS <str> the output block.
	
	// MyBlocks::avatar($userData);
	{
		return '
		<div class="uc-static-block" style="margin-top:0px;">
			<a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '">
				<img src="' . ($userData['avatar_opt'] && $userData['avatar_opt'] < 10 ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ($userData['avatar_opt'] ? '/assets/npcs/darla_caretaker.png' : ProfilePic::image(Me::$id, "large"))) . '" />
			</a>
			<div class="uc-bold">' . $userData['display_name'] . '</div>' . (Me::$id == $userData['uni_id'] && $userData['avatar_opt'] == 10 ? '<span class="uc-note"><a href="/settings">Get a custom caretaker here!</a></span>' : '') . '
		</div>';
	}
	
/****** Show the inventory ******/
	public static function inventory
	(
		$uniID			// <int> The ID of the user whose inventory is displayed.
	)					// RETURNS <str> the output block.
	
	// MyBlocks::inventory($uniID);
	{
		$supplies = MySupplies::getSupplyList($uniID);
		
		return '
		<style>
			.supply-block { display:inline-block; width:45%; padding:1%; margin-bottom:12px; }
		</style>
		<div class="uc-action-block hide-600">
			<div class="supply-block"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note">' . number_format($supplies['components']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note">' . number_format($supplies['coins']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note">' . number_format($supplies['crafting']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note">' . number_format($supplies['alchemy']) . '</div></div>
		</div>';
	}
	
/****** Show a pet ******/
	public static function pet
	(
		$pet			// <str:mixed> The data of the pet that is displayed.
	,	$petType		// <str:mixed> The data of the pet that is displayed.
	,	$handle			// <str> The handle of the pet's owner.
	)					// RETURNS <str> the output block.
	
	// MyBlocks::pet($pet, $petType, $handle);
	{
		$urlAdd = "";
		if($pet['uni_id'] != Me::$id)
		{	
			$urlAdd = "/" . $handle;
		}		
		if($areaData = MyAreas::areaData((int) $pet['area_id']))
		{
			$areaLink = $urlAdd . "/area/" . $areaData['id'];
		}
		else
		{
			$areaLink = $urlAdd . "/wild";
			$areaData['type'] = "wild";
			$areaData['name'] = "The Wild";
		}
		
		$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $petType['prefix']);
		
		return '
		<div class="uc-static-block">
			<a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></a>
			<div class="uc-bold">' . ($prefix != "" && $pet['nickname'] == $petType['name'] ? $prefix . " " : "") . ($petType['name'] == "Egg" && $pet['nickname'] == "Egg" ? $petType['family'] . ' Egg' : $pet['nickname']) . (MyCreatures::petRoyalty($petType['prefix']) != "" ? '<br/><img src="/assets/medals/' . MyCreatures::petRoyalty($petType['prefix']) . '.png" /> ' . MyCreatures::petRoyalty($petType['prefix']) . ' <img src="/assets/medals/' . MyCreatures::petRoyalty($petType['prefix']) . '.png" />' : '') . '</div>
			<div class="uc-note">' . $pet['total_points'] . ($petType['required_points'] ? '/' . $petType['required_points'] : '') . ' Evolution Points</div>
			<div class="uc-static-inline"><a href="' . $areaLink . '"><img src="/assets/areas/' . $areaData['type'] . '.png"  style="max-height:64px;" /></a><div class="uc-note-bold">' . $areaData['name'] . '</div></div>
			' . ($pet['uni_id'] != Me::$id ? '<div class="uc-static-inline"><a href="/' . $handle . '"><img src="' . ProfilePic::image((int) $pet['uni_id'], "medium") . '" style="border-radius:6px;" /></a><div class="uc-note-bold">' . $handle . '\'s Visit Center</div></div>' : '') . '
		</div>';
	}
	
/****** Show the top navigation ******/
	public static function topnav
	(
		$handle			// <str> The handle of the profile's owner.
	,	$gray = ''		// <str> The current script.
	)					// RETURNS <str> the output block.
	
	// MyBlocks::topnav($handle, $url[0]);
	{
		$urlAdd = "";
		if(You::$id != Me::$id && You::$id != 0)
		{
			$urlAdd = "/" . $handle;
		}
		
		return '
		<div class="uc-action-block">
			<div class="uc-action-inline"' . ($gray == 'wild' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/wild"><img src="/assets/icons/wild.png" /></a><div class="uc-note-bold">The Wild</div></div>
			<div class="uc-action-inline"' . ($gray == 'home' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
			' . ($urlAdd ? '<div class="uc-action-inline"' . ($gray == $handle ? ' style="opacity:0.7;"' : '') . '><a href="/' . $handle . '"><img src="' . ProfilePic::image(You::$id, "large") . '" style="border-radius:6px; width:75px;" /></a><div class="uc-note-bold">Visit Center</div></div>' : '') . '			<div class="uc-action-inline"' . ($gray == 'achievements' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
			<div class="uc-action-inline"' . ($gray == 'training-center' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
			<div class="uc-action-inline"' . ($gray == 'herd-list' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
		</div>';
	}
	
}
