<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Get the active hashtag of the page
$activeHashtag = isset($_POST['activeHashtag']) ? Sanitize::variable($_POST['activeHashtag']) : '';

// Display a Chat Widget
if($activeHashtag)
{
	$chatWidget = new ChatWidget($activeHashtag);
	echo $chatWidget->get();
}


// Dynamic Content Loader
echo '
<!-- Content gets dynamically shifted to this section -->
<div id="dynamic-content-loader"></div>';

// User Activity
$guestCount = UserActivity::getGuestsOnlineCount(900);
$userCount = UserActivity::getUsersOnlineCount(900);

echo '
<div style="background-color:white; padding:6px; border-radius:6px;"><div style="display:table; width:100%; text-align:center;"><div style="display:table-cell; width:50%;"><div class="uc-bold">Users Online</div><div>' . $userCount . '</div></div><div class="uc-bold">Guests Online</div><div>' . $guestCount . '</div></div></div>';

/*
// Prepare the Featured Widget Data
$categories = array("articles", "people");

// Create a new featured content widget
$featuredWidget = new FeaturedWidget($activeHashtag, $categories);

// If you want to display the FeaturedWidget by itself:
echo $featuredWidget->get();
*/