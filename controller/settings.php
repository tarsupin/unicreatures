<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// If you're not viewing someone and not logged in yourself
if(!Me::$loggedIn)
{
	Me::redirectLogin("/settings");
}

// If you chose an avatar
if(isset($_GET['def']))
{
	// Check if that avatar is valid
	if($_GET['def'] == 0 || $_GET['def'] == 10)
	{
		$avatarExists = true;
	}
	else
	{
		$packet = array(
			"uni_id"	=> Me::$id
		,	"avi_id"	=> (int) $_GET['def']		// The ID of the avatar to test for
		);
		
		$avatarExists = Connect::to("avatar", "AvatarExists", $packet);
	}
	
	if($avatarExists)
	{
		// If the avatar is valid, update your default avatar
		Database::query("UPDATE users SET avatar_opt=? WHERE uni_id=? LIMIT 1", array((int) $_GET['def'], Me::$id));
		
		Me::$vals['avatar_opt'] = (int) $_GET['def'];
		
		Alert::success("Avatar Updated", "You have chosen your default avatar.");
	}
	else
	{
		Alert::error("Avatar Error", "There was an error trying to load this avatar.");
	}
}

// Prepare Values
if(!$settings = Database::selectOne("SELECT * FROM users_settings WHERE uni_id=? LIMIT 1", array(Me::$id)))
{
	Database::query("INSERT INTO users_settings VALUES (?, ?)", array(Me::$id, json_encode(array())));
	$settings['avatar_list'] = array();
}
$avatarList = json_decode($settings['avatar_list'], true);

// If you're loading your avatars
if(($value = Link::clicked() and $value == "load-avatars"))
{
	// Prepare a list of plugins and their current versions
	$packet = array(
		"uni_id"			=> Me::$id			// The UniID to check avatars for
	);
	
	if($avatarList = Connect::to("avatar", "MyAvatarsAPI", $packet))
	{
		// Update your avatar list
		Database::query("UPDATE users_settings SET avatar_list=? WHERE uni_id=? LIMIT 1", array(json_encode($avatarList), Me::$id));
		
		if(!Me::$vals['avatar_opt'])
		{
			Database::query("UPDATE users SET avatar_opt=? WHERE uni_id=? LIMIT 1", array(1, Me::$id));
		}
	}
}

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
	' . MyBlocks::avatar(Me::$vals) . '
</div>
<div id="uc-right">
	' . MyBlocks::topnav(Me::$vals['handle'], $url[0]);
	
	// Display your list of avatars available
echo '
	<div style="display:inline-block; padding:6px; text-align:center;"><img src="' . ProfilePic::image(Me::$id, "large") . '" /><br /><a class="button" href="/settings?def=0">Set as Default</a></div>';
if($avatarList)
{
	foreach($avatarList as $aviID => $aviName)
	{
		echo '
	<div style="display:inline-block; padding:6px; text-align:center;"><img src="' . Avatar::image(Me::$id, (int) $aviID) . '" style="max-height:262px;" /><br /><a class="button" href="/settings?def=' . $aviID . '">Set as Default</a></div>';
	}
}

echo '
	<div style="display:inline-block; padding:6px; text-align:center;"><img src="/assets/npcs/darla_caretaker.png" /><br /><a class="button" href="/settings?def=10">Set as Default</a></div>
	<div style="padding:8px;"><a class="button" href="/settings?loadAvis=1&' . Link::prepare("load-avatars") . '">Load My Avatars</a> <a class="button" href="' . URL::avatar_unifaction_com() . Me::$slg . '">Create an Avatar</a></div>';

echo '
</div>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
