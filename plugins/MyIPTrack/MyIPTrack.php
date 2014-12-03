<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About the MyIPTrack Plugin ------
----------------------------------------

This plugin allows you to tracker user IPs that have visited (allows you to identify what treasure to give, if applicable).


-------------------------------
------ Methods Available ------
-------------------------------

MyIPTrack::userTracker($uniID);
MyIPTrack::cleanup($uniID);

*/

abstract class MyIPTrack {
	
	
/****** Plugin Variables ******/
	public static $dayCount = 0;		// <int> The number of times the user has been visited today.
	
	
/****** Log a user, or identify them as being already logged ******/
	public static function userTracker
	(
		$uniID				// <int> The UniID to check if an IP has been triggered.
	,	$timelimit = 86400	// <int> The duration (in seconds) to disregard old visits.
	)						// RETURNS <bool> TRUE if the user was newly logged, FALSE if already tracked.
	
	// MyIPTrack::userTracker($uniID);
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if($date = (int) Database::selectValue("SELECT date_visit FROM ip_visit_user WHERE uni_id=? AND ip=? LIMIT 1", array($uniID, $ip)))
		{
			if($date > time() - $timelimit) { return false; }
			
			$success = Database::query("UPDATE ip_visit_user SET date_visit=? WHERE uni_id=? AND ip=? LIMIT 1", array(time(), $uniID, $ip));
		}
		else
		{
			$success = Database::query("INSERT IGNORE INTO ip_visit_user (uni_id, ip, date_visit) VALUES (?, ?, ?)", array($uniID, $ip, time()));
		}
		
		if($success)
		{
			// Cleanup IP logs once in a while
			if(mt_rand(0, 22) == 5)
			{
				self::cleanup($uniID, $timelimit);
			}
		}
		
		return $success;
	}
	
	
/****** Pull a user's day count data ******/
	public static function getDayCount
	(
		$uniID				// <int> The UniID to update the day count for.
	,	$update = false		// <int> Update the day counter.
	)						// RETURNS <str:mixed> The data for the user's day count.
	
	// $dayCountData = MyIPTrack::getDayCount($uniID, [$update]);
	{
		// Update the user's counter for this current cycle
		$cycle = (int) date("yz");
		
		// Pull the user's counter data
		if(!$dayCountData = Database::selectOne("SELECT cycle, count FROM ip_visit_counter WHERE uni_id=? LIMIT 1", array($uniID)))
		{
			Database::query("INSERT IGNORE INTO ip_visit_counter (uni_id, cycle, count) VALUES (?, ?, ?)", array($uniID, $cycle, 0));
			
			$dayCountData = array(
				"cycle"		=> $cycle
			,	"count"		=> 0
			);
		}
		
		// If the current cycle doesn't match (e.g. it's a new cycle), then reset the count
		if($dayCountData['cycle'] != $cycle)
		{
			$dayCountData = array(
				"cycle"		=> $cycle
			,	"count"		=> 0
			);
		}
		
		// If you want to update the counter
		if($update)
		{
			$dayCountData['count'] += 1;
			
			Database::query("UPDATE ip_visit_counter SET cycle=?, count=? WHERE uni_id=? LIMIT 1", array($cycle, $dayCountData['count'], $uniID));
		}
		
		return $dayCountData;
	}
	
	
/****** Checks how many ip logs are with a user ******/
	public static function userVisits
	(
		$uniID				// <int> The UniID to check IP logs for.
	)						// RETURNS <bool> TRUE if the user was newly logged, FALSE if already tracked.
	
	// MyIPTrack::userVisits($uniID);
	{
		$visits = (int) Database::selectValue("SELECT COUNT(*) as totalNum FROM ip_visit_user WHERE uni_id=?", array($uniID));
		
		return ($visits ? false : true);
	}
	
	
/****** Clean up old IP logs for a user's visits ******/
	public static function cleanup
	(
		$uniID				// <int> The UniID of the user to clean IP logs.
	,	$timelimit = 86400	// <int> The duration (in seconds) to retain IP logs.
	)						// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// MyIPTrack::cleanup($uniID);
	{
		return Database::query("DELETE FROM ip_visit_user WHERE uni_id=? AND date_visit < ?", array($uniID, time() - $timelimit));
	}
}
