<?hh if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

--------------------------------------
------ About the MyBlocks Plugin ------
--------------------------------------

This plugin allows for consistent use of common site elements, such as the avatar or inventory.


-------------------------------
------ Methods Available ------
-------------------------------

MyBlocks::avatar($userData);
MyBlocks::inventory($uniID);
MyBlocks::petInfo($pet, [$url]);
MyBlocks::pet($pet, $petType, $handle);
MyBlocks::petPlain($pet, [$url]);
MyBlocks::topnav($handle, $url[0]);

*/

abstract class MyBlocks {
	
	
/****** Show the chosen avatar ******/
	public static function avatar
	(
		array <str, mixed> $userData		// <str:mixed> The data of the user that you're getting the avatar for.
	): string					// RETURNS <str> the output block.
	
	// MyBlocks::avatar($userData);
	{
		return '
		<div class="uc-static-block" id="uc-avatar" style="margin-top:0px;">
			<a href="' . URL::unifaction_social() . '/' . $userData['handle'] . '">
				<img src="' . ($userData['avatar_opt'] && $userData['avatar_opt'] < 10 ? Avatar::image((int) $userData['uni_id'], (int) $userData['avatar_opt']) : ($userData['avatar_opt'] ? '/assets/npcs/darla_caretaker.png' : ProfilePic::image((int) $userData['uni_id'], "large"))) . '" />
			</a>
			<div class="uc-bold">' . $userData['display_name'] . '</div>' . (Me::$id == $userData['uni_id'] && $userData['avatar_opt'] == 10 ? '<span class="uc-note"><a href="/settings">Get a custom caretaker here!</a></span>' : '') . '
		</div>';
	}
	
/****** Show the inventory ******/
	public static function inventory
	(
		bool $hide = true	// <bool> Whether to hide the block on small screens.
	): string					// RETURNS <str> the output block.
	
	// MyBlocks::inventory();
	{
		$supplies = MySupplies::getSupplyList(Me::$id);
		
		return '
		<div class="uc-action-block"' . ($hide ? ' id="uc-inventory"' : '') .'>
			<div class="supply-block"><img src="/assets/supplies/component_bag.png" /><div class="uc-note-bold">Components</div><div class="uc-note" id="components-count">' . number_format($supplies['components']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/coins_large.png" /><div class="uc-note-bold">Coins</div><div class="uc-note" id="coins-count">' . number_format($supplies['coins']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/supplies.png" /><div class="uc-note-bold">Crafting</div><div class="uc-note" id="crafting-count">' . number_format($supplies['crafting']) . '</div></div>
			<div class="supply-block"><img src="/assets/supplies/tree_seeds.png" /><div class="uc-note-bold">Alchemy</div><div class="uc-note" id="alchemy-count">' . number_format($supplies['alchemy']) . '</div></div>
			' . ($supplies['mystery_boxes'] > 0 ? '<div class="supply-block" id="mystery-block"><a href="javascript:openMysteryBox();"><img src="/assets/supplies/mystery_box.png" /></a><div class="uc-note-bold">Mystery Boxes</div><div class="uc-note" id="mystery-count">' . number_format($supplies['mystery_boxes']) . '</div></div>' : '') . '
		</div>';
	}
	
/****** Show pet info ******/
	public static function petInfo
	(
		array <str, mixed> $pet			// <str:mixed> The data of the pet type
	,	string $url = ""		// <str> The url for the image.
	): string					// RETURNS <str> the output block.
	
	// MyBlocks::petInfo($pet, [$url]);
	{
		$day = date("z");
		$available = "";
		if(isset($pet['day_start']))
		{
			if($pet['day_start'] != -1)
			{
				if($day >= $pet['day_start'] && $day <= $pet['day_end'])
					$available = "leaves";
				else
					$available = "arrives";
			}
		}
		elseif(isset($pet['date_start']))
		{
			$available = "leaves";
		}
		
		$stars = "";
		for($j=0; $j<$pet['rarity']; $j++)
			$stars .= "&#9733;";
		for($j=$pet['rarity']; $j<9; $j++)
			$stars .= "&#9734;";
		
		$royalty = MyCreatures::petRoyalty($pet['prefix']);
		
		$html = '
		<div class="shop-block">
			<div class="shop-block-inner">
				<div class="shop-block-left">
					' . ($url ? '<a href="' . $url . '">' : '') . '<img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" />' . ($url ? '</a>' : '') . '
				</div>
				<div class="shop-block-right">
					<div class="shop-block-title">' . ($pet['prefix'] != "" ? $pet['prefix'] . " " : "") . ($pet['name'] == "Egg" ? $pet['family'] : $pet['name']) . ($royalty ? ' <img src="/assets/medals/' . $royalty . '.png" />' : '') . '</div>
					<div class="shop-block-note">' . $pet['blurb'] . ' <a href="javascript:viewAchievements(\'' . $pet['family'] . '\');"><span class="icon-circle-info"></span></a></div>';
		if($pet['rarity'] <= 9)
		{
			$html .= '
					<div class="shop-block-info" title="Rarity: ' . $pet['rarity'] . '">' . $stars . '</div>';
		}
		elseif($petData = MyShop::shopPet((int) $pet['id']))
		{
			$html .= '
				<div class="shop-block-info">' . $petData['cost'] . ' Coins</div>';
		}
		if($available == "arrives")
		{
			$html .= '
					<div class="shop-block-arrive">Arrives ' . ($pet['day_start'] - $day > 0 ? ' in ' . ($pet['day_start'] - $day) . ' Days' : ($pet['day_start'] - $day < 0 ? ' in ' . ($pet['day_start'] + 365 - $day) . ' Days' : 'Today')) . '</div>';
		}
		if($available == "leaves")
		{
			if(isset($pet['day_start']))
			{
				$html .= '
					<div class="shop-block-leave">Leaves ' . ($pet['day_end'] - $day > 0 ? ' in ' . ($pet['day_end'] - $day) . ' Days' : ($pet['day_end'] - $day < 0 ? ' in ' . ($pet['day_end'] + 365 - $day) . ' Days' : 'Today')) . '</div>';
			}
			else
			{
				$html .= '
					<div class="shop-block-leave">Leaves ' . Time::fuzzy((int) $pet['date_end']) . '</div>';
			}
		}
					
		$html .= '
				</div>
			</div>
		</div>';

		return $html;
	}
	
/****** Show a pet (plain) ******/
	public static function petPlain
	(
		array <str, mixed> $pet			// <str:mixed> The data of the pet that is displayed.
	,	string $url = ""		// <str> The url for the image.
	,	string $extra = ""		// <str> Text to append at the end.
	): string					// RETURNS <str> the output block.
	
	// MyBlocks::petPlain($pet, [$url]);
	{
		$royalty = MyCreatures::petRoyalty($pet['prefix']);
		
		return '
		<div class="pet-cube">
			<div class="pet-cube-inner">
				<div>' . ($url ? '<a href="' . $url . '">' : '') . '<img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" />' . ($url ? '</a>' : '') . '</div>
				<div>' . ($pet['prefix'] && $pet['name'] == $pet['nickname'] ? $pet['prefix'] . ' ' : '') . ($pet['name'] == "Egg" && $pet['nickname'] == "Egg" ? $pet['family'] . ' Egg' : $pet['nickname']) . ($pet['activity'] && ($pet['active_until'] >= time() || !$pet['active_until']) ? ' <span class="icon-spinner"></span>' : '') . ($royalty ? ' <img src="/assets/medals/' . $royalty . '.png" />' : '') . '</div>
				' . $extra . '
			</div>
		</div>';
	}
	
/****** Show a pet ******/
	public static function pet
	(
		array <str, mixed> $pet			// <str:mixed> The data of the pet that is displayed.
	,	array <str, mixed> $petType		// <str:mixed> The data of the pet that is displayed.
	,	string $handle			// <str> The handle of the pet's owner.
	): string					// RETURNS <str> the output block.
	
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
		foreach($pet as $key => $val)
			$pet[$key] = (is_numeric($val) ? (int) $val : $val);
		foreach($petType as $key => $val)
			$pet[$key] = (is_numeric($val) ? (int) $val : $val);
		unset($petType);
		
		$prefix = str_replace(array("Noble", "Exalted", "Noble ", "Exalted "), array("", "", "", ""), $pet['prefix']);

		return '
		<div class="uc-static-block">
			' . self::petPlain($pet, '/pet/' . $pet['id']) . '
			<div class="uc-note">Evolution Points: ' . $pet['total_points'] . ($pet['required_points'] ? '/' . $pet['required_points'] : '') . '</div>
			<div class="uc-static-inline"><a href="' . $areaLink . '"><img src="/assets/areas/' . $areaData['type'] . '.png"  style="max-height:64px;" /></a><div class="uc-note-bold">' . $areaData['name'] . '</div></div>
			' . ($pet['uni_id'] != Me::$id ? '<div class="uc-static-inline"><a href="/' . $handle . '"><img src="' . ProfilePic::image((int) $pet['uni_id'], "medium") . '" style="border-radius:6px;" /></a><div class="uc-note-bold">' . $handle . '\'s Visit Center</div></div>' : '') . '
		</div>';
	}
	
/****** Show the top navigation ******/
	public static function topnav
	(
		string $handle			// <str> The handle of the profile's owner.
	,	string $gray = ''		// <str> The current script.
	): string					// RETURNS <str> the output block.
	
	// MyBlocks::topnav($handle, $url[0]);
	{
		$urlAdd = "";
		$add = "";
		if(You::$id != Me::$id && You::$id != 0)
		{
			$urlAdd = "/" . $handle;
			$add = '<style>.uc-action-inline { width:95px; }</style>';
		}
		
		return $add . '
		<div class="uc-action-block">
			<div class="uc-action-inline"' . ($gray == 'wild' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/wild"><img src="/assets/icons/wild.png" /></a><div class="uc-note-bold">The Wild</div></div>
			<div class="uc-action-inline"' . ($gray == 'home' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/home"><img src="/assets/icons/button_hut.png" /></a><div class="uc-note-bold">Pet Areas</div></div>
			' . ($urlAdd ? '<div class="uc-action-inline"' . ($gray == $handle ? ' style="opacity:0.7;"' : '') . '><a href="/' . $handle . '"><img src="' . ProfilePic::image(You::$id, "large") . '" style="border-radius:6px; width:75px;" /></a><div class="uc-note-bold">Visit Center</div></div>' : '<div class="uc-action-inline show-600"><a href="javascript:viewInventory();"><img src="/assets/icons/button_items.png" /></a><div class="uc-note-bold">Inventory</div></div>') . '<div class="uc-action-inline"' . ($gray == 'achievements' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/achievements"><img src="/assets/icons/button_trophy.png" /></a><div class="uc-note-bold">Achievements</div></div>
			<div class="uc-action-inline"' . ($gray == 'training-center' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/training-center"><img src="/assets/icons/button_course.png" /></a><div class="uc-note-bold">Training</div></div>
			 <div class="uc-action-inline"' . ($gray == 'herd-list' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/herd-list"><img src="/assets/icons/button_herds.png" /></a><div class="uc-note-bold">Herds</div></div>
			<div class="uc-action-inline"' . ($gray == 'team-list' ? ' style="opacity:0.7;"' : '') . '><a href="' . $urlAdd . '/team-list"><img src="/assets/icons/button_teams.png" /></a><div class="uc-note-bold">Teams</div></div>
		</div>';
	}
	
}