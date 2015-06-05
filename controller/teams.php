<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active herd
if(!isset($url[1]))
{
	header("Location: /"); exit;
}

// Get the active user
if(!isset($userData))
{
	// If you're not viewing someone and not logged in yourself
	if(!Me::$loggedIn)
	{
		Me::redirectLogin("/teams");
	}
	
	$userData = Me::$vals;
	$userData['uni_id'] = (int) $userData['uni_id'];
}

// Prepare Values
$url[1] = (int) $url[1];

// Check the herd
if(!$herdData = MyTeams::teamData($url[1]))
{
	header("Location: /team-list"); exit;
}


if($link = Link::clicked())
{
	// Return a creature to the wild
	if($link == "return-to-wild")
	{
		if(isset($_GET['nickname']) && isset($_GET['tID']))
		{
			$_GET['nickname'] = Sanitize::safeword($_GET['nickname'], "!@#$%^*", false);
			
			if(MyTeams::backToWild(Me::$id, (int) $herdData['id'], (int) $_GET['tID'], $_GET['nickname']))
			{
				Alert::success("Pet Moved", "You have returned \"" . $_GET['nickname'] . "\" to your Wild.");
				$herdData = MyTeams::teamData($url[1]);
			}
		}
	}
	// delete the herd
	elseif($link == "disband-team")
	{
		if(MyTeams::deleteHerd($url[1]))
		{
			Alert::saveSuccess("Team Deleted", "You have disbanded \"" . $herdData['name'] . "\".");
			header("Location: /teams-list"); exit;
		}
	}
}

// Rename a Team
if(Form::submitted("uni-rename-herd"))
{
	$_POST['herdName'] = Sanitize::safeword($_POST['herdName'], " '()#-+");
	
	if(FormValidate::pass() && Me::$id == $herdData['uni_id'])
	{
		if(Database::query("UPDATE teams SET name=? WHERE id=? LIMIT 1", array($_POST['herdName'], $url[1])))
		{
			$herdData['name'] = $_POST['herdName'];
			Alert::success("Renamed Herd", "You have renamed the team to \"" . $_POST['herdName'] . "\".");
		}
	}
}

$_POST['herdName'] = isset($_POST['herdName']) ? $_POST['herdName'] : $herdData['name'];

// Get the list of the user's pets
$herdPets = MyTeams::getPets((int) $herdData['id']);

// Prepare Values
$linkProtect = Link::prepare("return-to-wild");

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
<div id="uc-left">
	' . MyBlocks::avatar($userData) . '
	<div class="uc-bold-block">Team Experience: ' . $herdData['experience'] . '</div>
	<div class="uc-bold-block">Team Points: ' . $herdData['points'] . '</div>
	<div class="uc-static-block"><img src="' . $herdData['image'] . '" /></div>
	<form class="uniform" method="post" style="text-align:center;">' . Form::prepare("uni-rename-herd") . '
		<p><input type="text" name="herdName" value="' . $_POST['herdName'] . '" maxlength="22"  style="text-align:center; max-width:100%;" /></p>
		<p><input type="submit" name="submit" value="Rename Team" /></p>
		<p><input type="button" onclick="if(confirm(\'Are you sure you want to disband this team?\')) { window.location.href=\'/teams/' . $url[1] . '?' . Link::prepare("disband-team") . '\'; }" value="Disband Team" /></p>
	</form>
</div>
<div id="uc-right">
	' . MyBlocks::topnav($userData['handle'], $url[0]) . '

	<h2>' . $herdData['name'] . '</h2>';

	
// Cycle through each creature in the herd and display it
foreach($herdPets as $pet)
{
	echo '
	<div class="pet-cube">
		<div class="pet-cube-inner">
			<div><img src="' . MyCreatures::imgSrc($pet['family'], $pet['name'], $pet['prefix']) . '" /></div>
			<div>' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . ($userData['uni_id'] == Me::$id ? ' <a onclick="return confirm(\'Are you sure you want to return ' . ($pet['prefix'] != "" && $pet['nickname'] == $pet['name'] ? $pet['prefix'] . " " : "") . $pet['nickname'] . ' to your Wild?\');" href="/teams/' . $herdData['id'] . '?nickname=' . $pet['nickname'] . '&tID=' . $pet['type_id'] . '&' . $linkProtect . '"><span class="icon-redo" title="Return to Wild"></span></a>' : '') . '</div>
		</div>
	</div>';
}

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
?>