<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure pet exists
if(!isset($url[1]) or !$pet = MyCreatures::petData((int) $url[1], "id, uni_id, area_id, type_id, nickname, gender, activity, active_until, experience, total_points, date_acquired"))
{
	header("Location: /"); exit;
}

// Prepare Values
$pet['id'] = (int) $pet['id'];
$pet['uni_id'] = (int) $pet['uni_id'];
$pet['active_until'] = (int) $pet['active_until'];
$pet['total_points'] = (int) $pet['total_points'];

// Get the User Data
if($pet['uni_id'] == Me::$id)
{
	$userData = Me::$vals;
}
else
{
	// Get the active user (who isn't you)
	if(!$userData = User::get($pet['uni_id'], "uni_id, handle, display_name"))
	{
		header("Location: /"); exit;
	}
	
	// Prepare Values
	$userData['uni_id'] = (int) $userData['uni_id'];
	$urlAdd = "/" . $userData['handle'];
	
	You::$id = $userData['uni_id'];
	You::$handle = $userData['handle'];
	You::$name = $userData['display_name'];
}

// Check if the pet is performing an activity
$isBusy = MyCreatures::isBusy($pet['activity'], $pet['active_until']);

if($isBusy)
{
	switch($pet['activity'])
	{
		case "training":
			Alert::info("Busy", $pet['nickname'] . ' is busy training. They will be available ' . Time::fuzzy($pet['active_until']) . '. <a href="' . $urlAdd . '/training-center">Visit Training Center</a>');
			break;
		
		default:
			Alert::info("Busy", $pet['nickname'] . ' is currently busy.' . ($pet['active_until'] ? ' They will be available ' . Time::fuzzy($pet['active_until']) . '.' : ''));
			break;
	}
}

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");

// Get Components
$components = MySupplies::getSupplies(Me::$id, "components");

// If the pet is available (i.e. not busy with an activity), run several core functions (your pets only)

// Perform Actions on the Pet
if(Me::$loggedIn)
{
	// Clicked on a link on this page (specific to this pet)
	if($link = Link::clicked() and $link == "uc-pet-page-" . $pet['id'])
	{
		// Feed the pet
		if(isset($_GET['feed']) && $_GET['feed'] > 0 && $_GET['feed'] <= 1000)
		{
			// Make sure the pet can be fed now
			if(!$isBusy)
			{
				$_GET['feed'] = (int) $_GET['feed'];
				// Do not feed beyond 10000 care points
				$_GET['feed'] = min($_GET['feed'], 10000 - $pet['total_points']);
				
				if($components >= $_GET['feed'])
				{
					Database::startTransaction();
					
					// Reduce your components
					$components = MySupplies::changeSupplies(Me::$id, "components", 0 - $_GET['feed']);
					
					// Update the pet
					MyCreatures::feedPet($pet['id'], $_GET['feed']);
					
					$pet['total_points'] += $_GET['feed'];
					
					Database::endTransaction();
				}
			}
			
			// If the pet is busy, announce it
			else
			{
				Alert::error("Pet Busy", "The pet is away, and cannot be fed right now.");
			}
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("uc-pet-page-" . $pet['id']);
$level = MyTraining::getLevel((int) $pet['experience']);

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

echo '
<div id="panel-right"></div>
<div id="content">' . Alert::display();

echo '
<div id="uc-left-wide">
	' . MyBlocks::pet($pet, $petType, $userData['handle']) . '
	<div class="uc-bold-block">' . $petType['blurb'] . '</div>';
	
	if(!$isBusy)
	{
		echo '
	<div class="uc-action-block">';
		$needsToEvolve = max(min($petType['required_points'] - $pet['total_points'], $components, 1000), 0);
		echo '
		<div style="margin-bottom:10px;">Components Available: ' . $components . '</div>
		<div class="uc-action-inline"><a href="/pet/' . $pet['id'] . '?feed=1&' . $linkProtect . '"><img src="/assets/supplies/sunnyseed.png" /></a><div class="uc-note-bold">Feed</div><div class="uc-note">&nbsp;</div></div>';
		
		if($needsToEvolve)
		{
			echo '
		<div class="uc-action-inline"><a href="/pet/' . $pet['id'] . '?feed=' . $needsToEvolve . '&' . $linkProtect . '"><img src="/assets/supplies/component_bag.png" /></a><div class="uc-note-bold">Feed x' . $needsToEvolve . '</div><div class="uc-note">&nbsp;</div></div>';
		}
		else
		{
			echo '
			<div class="uc-action-inline"><a id="feedcustom" href="#"><img src="/assets/supplies/component_bag.png" /></a><div class="uc-note-bold">Feed x<input type="number" value="0" min="0" max="1000" onchange="document.getElementById(\'feedcustom\').href=\'/pet/' . $pet['id'] . '?feed=\' + this.value + \'&' . $linkProtect . '\';" style="max-width:4em;" /></div><div class="uc-note">&nbsp;</div></div>';
		}
		echo '
	</div>';
	}
	
	if($pet['experience'] > 0)
	{
		echo '
	<div class="uc-static-block">
		<div style="text-align:center; font-weight:bold;">' . ($level ? 'Level ' . $level . ' ' : '') . ($pet['gender'] == "m" ? "Male" : "Female") . ' ' . $petType['family'] . '</div>
		<div style="text-align:center;">' . number_format($pet['experience']) . ' EXP</div>
		<div style="margin-top:12px;">';
		
		$attributes = MyTraining::getAttributes($petType['family'], $level);
		
		foreach($attributes as $key => $value)
		{
			echo '
		<div class="attr-row"><div class="attr-name">' . ucfirst($key) . '</div><div class="attr-num">' . $value . '</div><div class="attr-graph">' . str_pad("", ($value / 4), "-")  . '</div></div>';
		}
		
		echo '
		</div>
	</div>';
	}
echo '
</div>
<div id="uc-right-wide">';

// If the pet is available (i.e. not busy with an activity) show several details of the pet
if(!$isBusy)
{
	if(Me::$id == $pet['uni_id'])
	{
		// Determine cost of training
		list($trainCost, $expGain) = MyTraining::getTrainingData($level);
		
		echo '
		<div class="uc-action-block">';
		
		if(!$petType['required_points'] == 0)
		{
			$evolve = $pet['total_points'] >= $petType['required_points'] ? true : false;
			
			echo '
			<div class="uc-action-inline">' . ($evolve ? '<a href="/action/evolve/' . $pet['id'] . '?' . $linkProtect . '"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" style="max-height:70px;" /></a>' : '<img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" style="max-height:70px; opacity:0.5;" />') . '<div class="uc-note-bold">Evolve</div><div class="uc-note">' . ($evolve ? 'Can Evolve!' : '&nbsp;') . '</div></div>';
		}
		
		echo '
			<div class="uc-action-inline"><a href="/action/send-trainer?pet=' . $pet['id'] . '"><img src="/assets/items/training_manual.png" /></a><div class="uc-note-bold">Train</div><div class="uc-note">' . $trainCost . ' Coins</div></div>
			<div class="uc-action-inline"><a href="/action/move-pet/' . $pet['id'] . '"><img src="/assets/icons/backpack.png" /></a><div class="uc-note-bold">Move Pet</div><div class="uc-note">&nbsp;</div></div>
			<div class="uc-action-inline"><a href="/action/join-herd/' . $pet['id'] . '"><img src="/assets/icons/herd.png" /></a><div class="uc-note-bold">Send to Herd</div><div class="uc-note">&nbsp;</div></div>
			<div class="uc-action-inline"><a href="/action/join-team/' . $pet['id'] . '"><img src="/assets/icons/campfire.png" /></a><div class="uc-note-bold">Send to Team</div><div class="uc-note">&nbsp;</div></div>
			<div class="uc-action-inline"><a href="/action/pet-options/' . $pet['id'] . '"><img src="/assets/items/notes_diagram.png" /></a><div class="uc-note-bold">More Options</div><div class="uc-note">&nbsp;</div></div>
		</div>';
	}
}

// Pet Description
echo '
	<div id="pet-desc">' . nl2br(MyCreatures::descMarkup($petType['description'], $pet['nickname'], $pet['gender'])) . '</div>';

// If you're logged in to your own pet page
if(Me::$id == $userData['uni_id'])
{
	echo '
	<div style="margin-bottom:10px;">&nbsp;</div>
	<h3>Attract Users For Free Goodies</h3>		
	<form class="uniform">
		<div>
			<div class="uc-note" style="font-weight:bold;">Direct Link:</div>
			<input type="text" name="dir_link" value="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '/' . $pet['id'] . '" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:10px;">
			<div class="uc-note" style="font-weight:bold;">BBCode Link:</div>
			<input type="text" name="bb_link" value="[url=' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '/' . $pet['id'] . '][img]' . URL::unicreatures_com() . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '[/img][/url]" style="width:100%;" readonly onclick="this.select();" />
		</div>
		<div style="margin-top:10px;">
			<div class="uc-note" style="font-weight:bold;">HTML Link:</div>
			<input type="text" name="bb_link" value=\'<a href="' . URL::unicreatures_com() . '/' . Me::$vals['handle'] . '/' . $pet['id'] . '"><img src="' . URL::unicreatures_com() . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></a>\' style="width:100%;" readonly onclick="this.select();" />
		</div>
	</form>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
