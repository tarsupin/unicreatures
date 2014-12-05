<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Must Log In
if(!Me::$loggedIn)
{
	Me::redirectLogin("/uc-static-blocks");
}

// Make sure pet exists
if(!isset($url[2]))
{
	header("Location: /uc-static-blocks"); exit;
}

// Get Pet Data
$pet = MyCreatures::petData($url[2]);

if(!$pet or $pet['uni_id'] != Me::$id)
{
	header("Location: /uc-static-blocks"); exit;
}

if($isBusy = MyCreatures::isBusy($pet['activity'], $pet['active_until']))
{
	Alert::saveError("Cannot Join Herd", "Cannot join a herd while busy.");
	
	header("Location: /pet/" . $pet['id']); exit;
}

// Protect Variables
if(!isset($_POST['herdName'])) { $_POST['herdName'] = $pet['nickname'] . "'s Herd"; }

$_POST['herdName'] = Sanitize::safeword($_POST['herdName'], " '()#-+");

// Start a Herd
if(Form::submitted("uni-start-herd"))
{
	if(FormValidate::pass())
	{
		if($herdID = MyHerds::createHerd(Me::$id, $pet['id'], $_POST['herdName']))
		{
			Alert::saveSuccess("Created Herd", "You have created the herd \"" . $_POST['herdName'] . "\"");
			
			header("Location: /herds/" . $herdID); exit;
		}
	}
}

// Get Pet Type Data
$petType = MyCreatures::petTypeData($pet['type_id'], "family, name, prefix");

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
	<div id="pet"><a href="/pet/' . $pet['id'] . '"><img src="' . MyCreatures::imgSrc($petType['family'], $petType['name'], $petType['prefix']) . '" /></a><div class="uc-bold">' . $pet['nickname'] . '</div><div class="uc-note">Level ' . MyTraining::getLevel($pet['experience']) . ' ' . $petType['name'] . '</div></div>
	<div id="pet-blurb">Name the herd you would like to create.</div>
</div>

<div id="uc-right-wide">
	<form class="uniform" action="/action/start-herd/' . $pet['id'] . '" method="post">' . Form::prepare("uni-start-herd") . '
		<p>Herd Name: <input type="text" name="herdName" value="' . $_POST['herdName'] . '" maxlength="22" /></p>
		<p><input type="submit" name="submit" value="Create Herd" /></p>
	</form>
</div>

</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
