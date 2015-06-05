<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// The user must be logged in
if(!Me::$loggedIn)
{
	exit;
}

if(!isset($_POST['family']))
{
	exit;
}
$_POST['family'] = Sanitize::word($_POST['family']);

$ach = Database::selectOne("SELECT creature_family, finished, fully_evolved, evolutions, trained, royalty, herd, awards FROM achievements WHERE uni_id=? AND creature_family=? LIMIT 1", array(Me::$id, $_POST['family']));
if(!$ach)
{
	$ach = array("creature_family" => $_POST['family'], "finished" => 0, "fully_evolved" => 0, "evolutions" => 0, "trained" => 0, "royalty" => 0, "herd" => 0, "awards" => 0);
}

echo '
<style>
	.ach-row { display:table-row; background-color:white; }
	.ach-lcell { display:table-cell; padding:4px; border:solid 1px #eeeeee; }
	.ach-ccell { display:table-cell; padding:4px; text-align:center; border:solid 1px #eeeeee; }
	.ach-done { background-color:#bbffbb; }
	#achievements-frame { position:fixed; top:50%; left:50%; -webkit-transform:translate(-50%,-50%); transform:translate(-50%,-50%); }
	#achievements-display { margin:0px; }
</style>
<div class="ach-row" style="font-weight:bold;">
	<div class="ach-lcell">Family</div>
	<div class="ach-ccell">Evolutions</div>
	<div class="ach-ccell">Trained</div>
	<div class="ach-ccell">Royalty</div>
	<div class="ach-ccell">Herd</div>
	<div class="ach-ccell">Awards</div>
</div>
<div class="ach-row">
	<div class="ach-lcell' . ($ach['finished'] == 1 ? ' ach-done' : '') . '">' . $ach['creature_family'] . '</div>
	<div class="ach-ccell' . ($ach['fully_evolved'] == 1 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['evolutions'], "*") . '</div>
	<div class="ach-ccell' . ($ach['trained'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['trained'], "*") . '</div>
	<div class="ach-ccell' . ($ach['royalty'] == 3 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['royalty'], "*") . '</div>
	<div class="ach-ccell' . ($ach['herd'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['herd'], "*") . '</div>
	<div class="ach-ccell' . ($ach['awards'] == 2 ? ' ach-done' : '') . '">' . str_pad("", (int) $ach['awards'], "*") . '</div>
</div>';