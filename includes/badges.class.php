<?php
/**
 * WhispyForum class library - badges.class.php
 * 
 * Profile badge (achievement) managing class
 * 
 * This class manages the querying and giving of achievement badges
 * 
 * WhispyForum
 */
class class_badges
{
	public $badge_array; // Define the badge array variable
	
	function Init()
	{
		/**
		 * This function loads every badge data to a class variable ($_badge_array).
		 * Badges are hardcoded, not dynamic. This initialization is done from the loader
		 * scripts, because this is required.
		 */
		
		global $wf_lang; // Because badge tooltips are localized, we need to hook up the localization array
		
		$this->badge_array = array(
			'LOCKED'	=>	array(
				// Array for locked badges
				'PICTURE'	=>	"badge_locked.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_LOCKED}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_LOCKED_TIP}']
				),
			'AVATAR'	=>	array(
				// Upload your personal avatar
				'PICTURE'	=>	"badge_avatar_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_AVATAR}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_AVATAR_TIP}']
				),
			'FIRSTPOST'	=>	array(
				// Contribute your first post
				'PICTURE'	=>	"badge_firstpost_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_FIRSTPOST}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_FIRSTPOST_TIP}']
				),
			'FIFTY_POST'	=>	array(
				// Post 50 times
				'PICTURE'	=>	"badge_fifty_post_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_FIFTY_POST}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_FIFTY_POST_TIP}']
				),
			'TWENTYFIFTY_POST'	=>	array(
				// Post 250 times
				'PICTURE'	=>	"badge_twentyfifty_post_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_TWENTYFIFTY_POST}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_TWENTYFIFTY_POST_TIP}']
				),
			'FIVEHUNDRED_POST'	=>	array(
				// Post 500 times
				'PICTURE'	=>	"badge_fivehundred_post_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_FIVEHUNDRED_POST}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_FIVEHUNDRED_POST_TIP}']
				),
			'THOUSAND_POST'	=>	array(
				// Post 1000 times
				'PICTURE'	=>	"badge_thousand_post_earned.png", // Badge picture name (under /themes/{THEME_NAME} directory)
				'NAME'	=>	$wf_lang['{LANG_BADGES_THOUSAND_POST}'],
				'TOOLTIP'	=>	$wf_lang['{LANG_BADGES_THOUSAND_POST_TIP}']
				)
		);
	}
	
	function GrantBadge($badgeName)
	{
		/**
		 * This function grants the set badge ($badgeName) for the current user ($_SESSION['uid'])
		 * The badge will not be awarded if the user already have it.
		 *
		 * @inputs: $badgeName -- name of the badge to award (please refer $_badge_array for the name list)
		 */
		
		global $Cmysql; // We access the database, so we must hook the class here
		global $Ctemplate; // We use templates here, hook the class
		
		if ( array_key_exists($badgeName, $this->badge_array) == FALSE )
		{
			// If we tried to grant a badge which does not exist
			$Ctemplate->useTemplate("badge_err", array(
				'BADGE_NAME'	=>	$badgeName // Name of the badge
			), FALSE ); // We output an error message
			return; // Terminate function execution
		}
		
		$already = mysql_num_rows($Cmysql->Query("SELECT earndate FROM badges WHERE userid='" .$Cmysql->EscapeString($_SESSION['uid']). "' AND badgename='" .$Cmysql->EscapeString($badgeName). "'")); // Select whether the user already earned the requested badge
		// If $already != 0 (usally 1), the user already has the badge, we need to do nothing here
		
		if ( $already == 0 )
		{
			// If the user does not have the badge
			// give it to him/her
			
			$Cmysql->Query("INSERT INTO badges(userid, badgename, earndate) VALUES (
				'" .$Cmysql->EscapeString($_SESSION['uid']). "',
				'" .$Cmysql->EscapeString($badgeName). "',
				'" .time(). "')"); // Give the badge
		}
	}
	
	function CheckBadge($badgeName, $userid)
	{
		/**
		 * Check whether a user ($userid) has a badge ($badgeName)
		 *
		 * @inputs: $badgeName -- name of the badge to award (please refer $_badge_array for the name list)
		 * 			$userid -- ID of the user to check
		 * 
		 * @outputs: an array containing the info of the checked badge
		 * 			 'picture' -- badge picture filename
		 * 			 'name' -- name of the badge (localized)
		 * 			 'tooltip' -- description of the badge (localized)
		 * 			 'earndate' -- timestamp when the user earned the badge (0 if the user does not have it)
		 */
		
		global $Cmysql, $Ctemplate; // Load all required classes (database, templates)
		
		if ( array_key_exists($badgeName, $this->badge_array) == FALSE )
		{
			// If we tried to grant a badge which does not exist
			$Ctemplate->useTemplate("badge_err", array(
				'BADGE_NAME'	=>	$badgeName // Name of the badge
			), FALSE ); // We output an error message
			return; // Terminate function execution
		}
		
		$query = $Cmysql->Query("SELECT earndate FROM badges WHERE userid='" .$Cmysql->EscapeString($userid). "' AND badgename='" .$Cmysql->EscapeString($badgeName). "'"); // Query the badge info from database
		
		$has = mysql_num_rows($query); // Select whether the user has earned the requested badge
		// If $has != 0 (usally 1), the user has the badge
		
		if ( $has == 0 )
		{
			// If the user does not have the badge
			// the result is the 'locked' badge
			
			$result = array(
				'picture'	=>	$this->badge_array['LOCKED']['PICTURE'],
				'name'	=>	$this->badge_array['LOCKED']['NAME'],
				'tooltip'	=>	$this->badge_array['LOCKED']['TOOLTIP'],
				'earndate'	=>	0
			);
		} elseif ( $has != 0 )
		{
			// If the user has the badge, return badge data
			
			$earndate = mysql_fetch_row($query); // Fetch a row from the earning date
			
			$result = array(
				'picture'	=>	$this->badge_array[$badgeName]['PICTURE'],
				'name'	=>	$this->badge_array[$badgeName]['NAME'],
				'tooltip'	=>	$this->badge_array[$badgeName]['TOOLTIP'],
				'earndate'	=>	$earndate[0]
			);
		}
		
		return $result; // Return the array
	}
	
	function BadgeCount($userid)
	{
		/**
		 * This function returns the number of badges a specified user accumulated.
		 *
		 * @inputs: $userid -- ID of the user to check
		 * @outputs: number of badges
		 */
		
		global $Cmysql; // Load all required classes (database)
		
		return mysql_num_rows($Cmysql->Query("SELECT userid FROM badges WHERE userid='" .$Cmysql->EscapeString($userid). "'"));
	}
	
	function TotalBadgeCount()
	{
		/**
		 * This function returns the total number of badges in the system.
		 *
		 * @outputs: number of badges
		 */
		
		return (count($this->badge_array)-1); // We need to remove one, because the 'LOCKED' badge is isn't really a badge, just a placeholder for the badges a user hasn't unlocked yet
	}
}
?>