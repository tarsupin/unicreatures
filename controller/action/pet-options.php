<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure pet exists
if(!isset($url[2]) or !$pet = MyCreatures::petData((int) $url[2], "id, uni_id, area_id, type_id, nickname, gender, activity, active_until, experience, total_points, date_acquired"))
{
	header("Location: /"); exit;
}

// Prepare Values
$pet['id'] = (int) $pet['id'];
$pet['uni_id'] = (int) $pet['uni_id'];
$pet['active_until'] = (int) $pet['active_until'];
$pet['total_points'] = (int) $pet['total_points'];

// Check if the pet is performing an activity
$isBusy = MyCreatures::isBusy($pet['activity'], $pet['active_until']);

// Get the Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");

// Update the pet name
if(Form::submitted("uc-pet-options"))
{
	FormValidate::safeword("Pet Name", $_POST['pet_nickname'], 1, 22);
	
	if(FormValidate::pass())
	{
		if(MyCreatures::updateName($pet['id'], $_POST['pet_nickname']))
		{
			Alert::saveSuccess("Name Updated", 'You have successfully renamed your pet "' . $_POST['pet_nickname'] . '".');
			
			header("Location: /pet/" . $pet['id']); exit;
		}
	}
}

// Prepare Values
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
	' . MyBlocks::pet($pet, $petType, Me::$vals['handle']) . '
	' . MyBlocks::inventory(Me::$id) . '
</div>

<div id="uc-right-wide">
	<div class="uc-action-block">
		<div class="uc-bold" style="margin-bottom:10px;">Additional Pet Options</div>
		<div class="uc-action-inline"><a href="/action/change-gender/' . $pet['id'] . '"><img src="/assets/items/genx_' . ($pet['gender'] == "m" ? 'female' : 'male') . '.png" /></a><div class="uc-note-bold">Change Gender</div><div class="uc-note">5 Alchemy</div></div>
		<div class="uc-action-inline"><a href="/action/reverse-evolve/' . $pet['id'] . '"><img src="/assets/items/watch_warp.png" /></a><div class="uc-note-bold">Reverse-Evolve</div><div class="uc-note">10 Alchemy</div></div>
		<div class="uc-action-inline"><a href="/action/write-story/' . $pet['id'] . '"><img src="/assets/items/scroll_words.png" /></a><div class="uc-note-bold">Record History</div><div class="uc-note">20 Coins</div></div>
		<div class="uc-action-inline"><a href="/action/release-pet/' . $pet['id'] . '"><img src="/assets/icons/abandoned.png" /></a><div class="uc-note-bold">Release Pet</div><div class="uc-note">&nbsp;</div></div>
	</div>
	<div id="pet-desc">
		<form class="uniform" action="/action/pet-options/' . $pet['id'] . '" method="post">' . Form::prepare("uc-pet-options") . '
			<h2 style="margin-bottom:4px;">Rename ' . $pet['nickname'] . '</h2>
			<div><input type="text" name="pet_nickname" value="' . $pet['nickname'] . '" placeholder="Pet Name . . ." maxlength="22" style="width:100%;" /></div>
			<div style="margin-top:10px;"><input type="submit" name="submit" value="Update Name" />
		</form>
	</div>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
