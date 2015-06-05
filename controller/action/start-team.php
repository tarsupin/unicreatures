<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	header("Location: /"); exit;
}

// Make sure pet exists
if(!isset($url[2]) or !$pet = MyCreatures::petData((int) $url[2], "id, uni_id, area_id, type_id, nickname, gender, activity, active_until, experience, total_points, date_acquired"))
{
	header("Location: /"); exit;
}

// Make sure you own the pet
if($pet['uni_id'] != Me::$id)
{
	header("Location: /"); exit;
}

// Prepare Values
$pet['id'] = (int) $pet['id'];
$pet['uni_id'] = (int) $pet['uni_id'];
$pet['active_until'] = (int) $pet['active_until'];
$pet['total_points'] = (int) $pet['total_points'];

// Get the User Data
$userData = Me::$vals;

if($isBusy = MyCreatures::isBusy($pet['activity'], $pet['active_until']))
{
	Alert::saveError("Cannot Join Team", "Cannot join a team while busy.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Protect Variables
if(!isset($_POST['herdName'])) { $_POST['herdName'] = $pet['nickname'] . "'s Team"; }

$_POST['herdName'] = Sanitize::safeword($_POST['herdName'], " '()#-+");

// Start a Herd
if(Form::submitted("uni-start-herd"))
{
	if(FormValidate::pass())
	{
		if($herdID = MyTeams::createHerd(Me::$id, $pet['id'], $_POST['herdName']))
		{
			Alert::saveSuccess("Created Herd", "You have created the team \"" . $_POST['herdName'] . "\".");
			
			header("Location: /teams/" . $herdID); exit;
		}
	}
}

// Get Pet Type Data
$petType = MyCreatures::petTypeData((int) $pet['type_id'], "family, name, evolution_level, required_points, rarity, blurb, description, evolves_from, prefix");
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

<div id="uc-right-wide">
	<form class="uniform" method="post">' . Form::prepare("uni-start-herd") . '
		<p>Team Name: <input type="text" name="herdName" value="' . $_POST['herdName'] . '" maxlength="22" /></p>
		<p><input type="submit" name="submit" value="Create Team" /></p>
	</form>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
