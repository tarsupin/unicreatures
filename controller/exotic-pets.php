<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Retrieve the shop exotics
$shopExotics = MyExotic::getShopList();

if(count($shopExotics) < MyExotic::$shopCreatures)
{
	MyExotic::updateExotics();
	
	$shopExotics = MyExotic::getShopList();
}

// Prepare Values
$creditCount = MyExotic::countMyAvailableCredits(Me::$id);

// If you purchased a pet
if(isset($_GET['purchase']) and $value = Link::clicked() and $value == "exotic-pet-shop")
{
	// Get details about the pet
	if($petData = MyExotic::getShopPet((int) $_GET['purchase']))
	{
		// Make sure that the pet can be purchased during this time-frame
		if($petData['date_end'] > time())
		{
			if($creditCount > 0)
			{
				// Purchase the pet
				$pass = false;
				
				Database::startTransaction();
				
				if($creatureID = MyCreatures::acquireCreature(Me::$id, (int) $petData['id']))
				{
					$pass = MyExotic::spendCreditOnCreature(Me::$id, $creatureID);
				}
				
				if(Database::endTransaction($pass))
				{
					$creditCount -= 1;
					
					Alert::success("Purchased Pet", 'You have purchased a ' . $petData['family'] . ' Egg' . '! <a href="/pet/' . $creatureID . '">Visit your pet</a>.');
				}
			}
			else
			{
				Alert::error("No Credits", "You don't have any exotic credits to purchase this creature with!");
			}
		}
	}
}

// Prepare Values
$linkProtect = Link::prepare("exotic-pet-shop");

if(Form::submitted("exotic-pets-uc"))
{
	// Get the exotic purchase
	$ecsToPurchase = (int) $_POST['exotic_purchase'];
	
	if(!in_array($ecsToPurchase, array(1, 3, 6, 12, 24)))
	{
		Alert::error("Invalid Purchase", "There was an issue with your purchase type.");
	}
	
	if(FormValidate::pass())
	{
		$UJCostList = array(1 => 5.00, 3 => 10.00, 6 => 15.00, 12 => 30.00, 24 => 60.00);
		$UJCost = (float) $UJCostList[$ecsToPurchase];
		
		// Attempt to purchase the credits
		Database::startTransaction();
		
		if($pass = MyExotic::purchaseCredit(Me::$id, $ecsToPurchase))
		{
			$pass = Credits::chargeInstant(Me::$id, $UJCost, "Got " . $ecsToPurchase .  " Exotic Credits");
		}
		
		if(Database::endTransaction($pass))
		{
			Alert::saveSuccess("Credits Purchased", "You have successfully purchased " . $ecsToPurchase . " Exotic Credits!"); 
		}
		else
		{
			Alert::saveError("Credits Failed", "The system was unable to process " . $ecsToPurchase . " Exotic Credits at this time.");
		}
		
		header("Location: /exotic-pets"); exit;
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
<h2>Exotic Pet Shop</h2>
<p>You have ' . $creditCount . ' exotic credits available. Every pet in the Exotic Pet Shop costs 1 exotic credit to purchase. Click on a pet to purchase it.</p>
<p>
	<form class="uniform" action="/exotic-pets" method="post">' . Form::prepare("exotic-pets-uc") . '
		<select name="exotic_purchase">
			<option value="1">Buy 1 exotic credit (5.00 UniJoule)</option>
			<option value="3">Buy 3 exotic credits (10.00 UniJoule)</option>
			<option value="6">Buy 6 exotic credits (15.00 UniJoule)</option>
			<option value="12">Buy 12 exotic credits (30.00 UniJoule)</option>
			<option value="24">Buy 24 exotic credits (60.00 UniJoule)</option>
		</select> <input type="submit" name="submit" value="Get Exotic Credits" />
	</form>
</p>';

// Display Exotics
foreach($shopExotics as $exoticPet)
{
	echo '
	<div class="shop-block">
		<div class="shop-block-inner">
			<div class="shop-block-left">
				<a href="/exotic-pets?purchase=' . $exoticPet['id'] . '&' . $linkProtect . '"><img src="' . MyCreatures::imgSrc($exoticPet['family'], $exoticPet['name'], $exoticPet['prefix']) . '" /></a>
			</div>
			<div class="shop-block-right">
				<div class="shop-block-title">' . ($exoticPet['name'] == "Egg" ? $exoticPet['family'] . ' Egg' : $exoticPet['name']) . '</div>
				<div class="shop-block-note">' . $exoticPet['blurb'] . '</div>';
			
			if($exoticPet['date_end'] > -1)
			{
				echo '
				<div class="shop-block-leave">Leaves ' . Time::fuzzy((int) $exoticPet['date_end']) . '</div>';
			}
			
			echo '
			</div>
		</div>
	</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");
