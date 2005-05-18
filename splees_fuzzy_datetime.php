<?php
/*
Plugin Name: Fuzzy DateTime
Plugin URI: http://www.splee.co.uk/category/fuzzydate-plugin/
Description: Prints a fuzzy date and time, similar to the KDE clock.
Version: 0.6
Author: Splee
Author URI: http://www.splee.co.uk
*/

/*  Copyright 2004  Lee McFadden  (email : splee@splee.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* 
This plugin contains code from the Time of Day plugin
by Phu Ly & Dunstan Orchard & Michael Heilemann (http://www.ifelse.co.uk/code/timeofday.php)
*/

/*
ChangeLog:

		
0.6:	(02/05/05) - Updated code with fixes for months being detected 'too literally'
		Fixed issue with day/days mode not being detected correctly
		Code tidy and optimisation
		
0.5:	Fixed issues with admin panel not saving options correctly

0.4:	Added admin panel functionality

0.3:	Fixed numerous issues with date calculations
		Tidied code and added some meaningful comments

0.2:	Too much to list!

0.1:	First implimentation.
*/
//ini_set('display_errors', true); //for debugging

// This is the function that needs to replace 'the_time()' in your theme.
function splees_fuzzy_datetime() {
	//Get the options from the db
	$lm_fdt_options = get_option('lm_fdt_options');
	$lm_time_offset = get_settings('gmt_offset') * 3600;
	
	//get the two timestamps
	$nowStamp = strtotime(gmdate('Y-m-d H:i:s', time()));
	$postStamp = strtotime(get_post_time('Y-m-d H:i:s', true));
	$difference = $lm_nowStamp - $lm_postStamp;
	
	
	//Spot the difference...
	$dayDifference = (date('z', $nowStamp) - date('z', $postStamp));
	$monthDifference = (date('n', $nowStamp) - date('n', $postStamp));
	$yearDifference = (date('Y', $nowStamp) - date('Y', $postStamp));
	
	if ($yearDifference > 1) $fuzzyMode = "years";
	elseif ($yearDifference == 1) $fuzzyMode = "year";
	elseif ($monthDifference > 1) $fuzzyMode = "months";
	elseif (($monthDifference == 1) && ($dayDifference >= 14)) $fuzzyMode = "month";  //Added a two week bias before the post was "last month".
	elseif ($dayDifference >= 14) $fuzzyMode = "weeks";
	elseif ($dayDifference >= 7) $fuzzyMode = "week";
	elseif ($dayDifference > 1) $fuzzyMode = "days";
	elseif ($dayDifference == 1) $fuzzyMode = "yesterday";
	elseif ($dayDifference == 0) $fuzzyMode = "today";

	//these are the strings to be replaced
	$rewritecode = array(
		"%minutesSince%",
		"%hoursSince%",
		"%daysSince%",
		"%weeksSince%",
		"%monthsSince%",
		"%yearsSince%",
		"%dayName%",
		"%monthName%",
		"%weekOfMonth%",
		"%fuzzyTimeText%",
		"%fuzzy12Hour%",
		"%fuzzyWeekPeriod%",
		"%fuzzyMonthPeriod%",
		"%MinutesOrHoursSince%",
		"%actualDate%",
		"%actualTime%"
		);

	//get the replacements for the above
	$rewritereplace = lm_get_fuzzy_replacements($postStamp, $nowStamp, $lm_time_offset);

	$outString = str_replace($rewritecode, $rewritereplace, $lm_fdt_options[$fuzzyMode]);
	
	//now print the fuzzy datetime.
	print $outString;
}


/* lm_get_fuzzy_replacements:
	This function must return an array the same length as the
	$rewriteCode array in splees_fuzzy_datetime()
	
	Parameters
	----------
	$postStamp: This should be the timestamp of the post
	$nowStamp: This should be the timestamp for now()
*/
function lm_get_fuzzy_replacements($postStamp, $nowStamp, $lm_time_offset) {
	$difference = ($nowStamp - $postStamp);
	//Now the delta has been calculated, go with the blog's local time
	$postStamp = $postStamp + $lm_time_offset;
	$nowStamp = $nowStamp + $lm_time_offset;
	
	/*	Must return an array with the results in the following order:
	
		"%minutesSince%",
		"%hoursSince%",
		"%daysSince%",
		"%weeksSince%",
		"%monthsSince%",
		"%yearsSince%",
		"%dayName%",
		"%monthName%",
		"%weekOfMonth%",
		"%fuzzyTimeText%",
		"%fuzzy12Hour%",
		"%fuzzyWeekPeriod%",
		"%fuzzyMonthPeriod%",
		"%MinutesOrHoursSince%",
		"%actualDate%",
		"%actualTime%"
	*/
	$returnValue = array();
	// %minutesSince%
	$returnValue[0] = floor($difference/60);
	//%hoursSince%
	$returnValue[1] = floor(($difference/60)/60);
	//%daysSince%
	$returnValue[2] = (date('z', $nowStamp) - date('z', $postStamp)); //floor((($difference/60)/60)/24); 
	//%weeksSince%
	$returnValue[3] = floor(((($difference/60)/60)/24)/7);
	//%monthsSince%
	$returnValue[4] = (date('n', $nowStamp) - date('n', $postStamp));
	//%yearsSince%
	$returnValue[5] = (date('Y', $nowStamp) - date('Y', $postStamp));
		
		if ($returnValue[5] > 0) {
			//deal with the inverted %monthsSince% value if %yearsSince% > 0
			$returnValue[4] = $returnValue[4] * (-1);
			//Also, the daysSince tag will start to go skewiff when calculated in a different year.
			//fix this by being semantically less accurate but numerically more accurate.
			$returnValue[2] = floor(((($difference/60)/60)/24)/7);
		}
	//%dayName%
	$returnValue[6] = date('l', $postStamp);
	//%monthName%
	$returnValue[7] = date('F', $postStamp);
	//%weekOfMonth%
	$returnValue[8] = ceil(date('j', $postStamp)/7);
	switch ($returnValue[8]) {
		case 1:
			$returnValue[8] .= "st";
			break;
		case 2:
			$returnValue[8] .= "nd";
			break;
		case 3:
			$returnValue[8] .= "rd";
			break;
		default:
			$returnValue[8] .= "th";
			break;
	}
	//%fuzzyTimeText%
	$returnValue[9] = lm_get_fuzzyTimeText($postStamp);
	//%fuzzy12Hour%
	$returnValue[10] = lm_get_fuzzy12Hour($postStamp);
	// %fuzzyWeekPeriod%
	switch(date('w', $postStamp)) {
		case 0:
			//silly PHP people think sunday is the beginning of the week! *rolls eyes*
			$returnValue[11] = "at the weekend";
			break;
		case 1:
		case 2:
			$returnValue[11] = "at the beginning of the week";
			break;
		case 3:
		case 4:
			$returnValue[11] = "midweek";
			break;
		case 5:
		case 6:
			$returnValue[11] = "at the weekend";
			break;
		default:
			//we don't know what day it was posted, so we'll be super vague here.
			//it was this or an error message; at least this kinda fits in ;)
			$returnValue[11] = "at some point during a 7 day period";
			break;
	}
	// %fuzzyMonthPeriod%
	// Use the previously calculated monthname
	switch(ceil(date('j', $postStamp)/7)) {
		case 1:
			$returnValue[12] = "at the start of " . $returnValue[7];
			break;
		case 2:
		case 3:
			$returnValue[12] = "mid-" . $returnValue[7];
			break;
		case 4:
		case 5:
			$returnValue[12] = "at the end of " . $returnValue[7];
			break;
		default:
			//again with the nice camouflaged error messages.
			$returnValue[12] = "at some point in " . $returnValue[7];
			break;
	}
	
	//%fuzzyMinutesOrHours%
	//use the previously calculated %hoursSince% and %minutesSince%
	if ($returnValue[1] != 0) {
		//if it's been over an hour:
		if ($returnValue[1] > 1) {
			$returnValue[13] = $returnValue[1] . " hours";
		} else {
			$returnValue[13] = "1 hour";
		}
	} else {
		//it's been less than an hour
		if ($returnValue[0] > 1) {
			//it's more than 1 minute ago
			$returnValue[13] = $returnValue[0] . " minutes";
		} elseif ($returnValue[0] = 1) {
			$returnValue[13] = "1 minute";
		} else {
			//the chances are small, but it's been posted less than a minute ago
			$returnValue[13] = "less than a minute";
		}
	}
	//%actualDate%
	$returnValue[14] = date('D, d M Y', $postStamp);
	
	//%actualTime%
	$returnValue[15] = date('g:i a', $postStamp);
	
	return $returnValue;
}

/*
	lm_get_fuzzy12Hour:
	
	This function returns a string containing a
	fuzzy representation of the 12 hour clock.
	
	Parameters
	----------
	$postStamp: This should be the timestamp of the post
*/
function lm_get_fuzzy12Hour($postStamp) {
	//fuzzify the hour
	$fuzHours = array(
		"one",
		"two",
		"three",
		"four",
		"five",
		"six",
		"seven",
		"eight",
		"nine",
		"ten",
		"eleven",
		"twelve"
		);
	$returnVal = $fuzHours[(date('g', $postStamp)-1)];
	//fuzzify the minutes
	$postMins = date('i', $postStamp);
	if (($postMins >= 7) && ($postMins <= 26)) {
		//It's around quarter past
		$returnVal = "a quarter past " . $returnVal;
	} elseif (($postMins >= 27) && ($postMins <= 37)) {
		//it's around half past
		$returnVal = "half-past " . $returnVal;
	} elseif (($postMins >= 38) && ($postMins <= 47)) {
		$returnVal = "a quarter to " . $returnVal;
	}
	$returnVal .=  " " . date('a', $postStamp);
	return $returnVal;
}

/*
	lm_get_fuzzyTimeText:
	Returns a string with a fuzzy representation of the time
	period from the $postStamp param.
	
	*based on code from the timeofday plugin by 
	Phu Ly & Dunstan Orchard & Michael Heilemann 
	(http://www.ifelse.co.uk/code/timeofday.php)*
	
	Parameters
	----------
	$postStamp: This should be the timestamp of the post
*/
function lm_get_fuzzyTimeText($postStamp) {
	switch(date('G', $postStamp))
		{
		case 0:
		case 1:
		case 2:
			$returnVal = 'in the wee hours';
			break;
		case 3:
		case 4:
		case 5:
		case 6:
			$returnVal = 'terribly early in the morning';
			break;
		case 7:
		case 8:
		case 9:
			$returnVal = 'in the early morning';
			break;
		case 10:
			$returnVal = 'mid-morning';
			break;
		case 11:
			$returnVal = 'just before lunchtime';
			break;
		case 12:
		case 13:
			$returnVal = 'around lunchtime';
			break;
		case 14:
			$returnVal = 'in the early afternoon';
			break;
		case 15:
		case 16:
			$returnVal = 'mid-afternoon';
			break;
		case 17:
			$returnVal = 'in the late afternoon';
			break;
		case 18:
		case 19:
			$returnVal = 'in the early evening';
			break;
		case 20:
		case 21:
			$returnVal = 'at around evening time';
			break;
		case 22:
			$returnVal = 'in the late evening';
			break;
		case 23:
			$returnVal = 'late at night';
			break;
		default:
			$returnVal = '';
			break;
		}
	return $returnVal;
}

/*
	From here on out we are dealing with the admin GUI.
*/

function lm_fdt_options_page() {
	switch($_POST['action']) {
		case 'lm_fdt_update':
			//The options have just been updated...  so update em already!
			$lm_fdt_options["today"] = $_POST['today'];
			$lm_fdt_options["yesterday"] = $_POST['yesterday'];
			$lm_fdt_options["days"] = $_POST['days'];
			$lm_fdt_options["week"] = $_POST['week'];
			$lm_fdt_options["weeks"] = $_POST['weeks'];
			$lm_fdt_options["month"] = $_POST['month'];
			$lm_fdt_options["months"] = $_POST['months'];
			$lm_fdt_options["year"] = $_POST['year'];
			$lm_fdt_options["years"] = $_POST['years'];
			
			update_option('lm_fdt_options', $lm_fdt_options);
			break;
		default:
			//Add the defaults to the db if they're not there already.
			$lm_fdt_defaults["years"] = "%yearsSince% years ago, %fuzzyMonthPeriod%";
			$lm_fdt_defaults["year"] = "last year, %fuzzyMonthPeriod%";
			$lm_fdt_defaults["months"] = "%monthsSince% months ago";
			$lm_fdt_defaults["month"] = "1 month ago";
			$lm_fdt_defaults["weeks"] = "%dayName%, %weeksSince% weeks ago";
			$lm_fdt_defaults["week"] = "%dayName%, last week";
			$lm_fdt_defaults["days"] = "%daysSince% days ago, %fuzzyTimeText%";
			$lm_fdt_defaults["yesterday"] = "yesterday, %fuzzyTimeText%";
			$lm_fdt_defaults["today"] = "%MinutesOrHoursSince% ago";
			add_option('lm_fdt_options', $lm_fdt_defaults);
			break;
	}
	$lm_fdt_options = get_option('lm_fdt_options');

//the page itself
?>
<div class="wrap">
<form name="lm_fdt_options" method="post" action="<?php echo $_SERVER[PHP_SELF]; ?>?page=splees_fuzzy_datetime.php">
<input type="hidden" name="action" value="lm_fdt_update" />
	<h2>Output Strings</h2>
	<p>Each of these options configure the output depending on the amount of time passed.  Enter the string using the replacement
	variables which are explained at the bottom of the page.</p>
	<table width="100%" cellpadding="5">
		<tr>
			<th valign="top" scope="row" align="right">Today:</th>
			<td>
				<INPUT type="text" name="today" value="<?php echo $lm_fdt_options["today"]; ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">Yesterday:</th>
			<td>
				<INPUT type="text" name="yesterday" value="<?php echo $lm_fdt_options["yesterday"]; ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">More than 2 days ago:</th>
			<td>
				<input type="text" name="days" value="<?php echo $lm_fdt_options["days"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">Last week:</th>
			<td>
				<input type="text" name="week" value="<?php echo $lm_fdt_options["week"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">More than 1 week ago:</th>
			<td>
				<input type="text" name="weeks" value="<?php echo $lm_fdt_options["weeks"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">Last month:</th>
			<td>
				<input type="text" name="month" value="<?php echo $lm_fdt_options["month"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">More than 1 month ago:</th>
			<td>
				<input type="text" name="months" value="<?php echo $lm_fdt_options["months"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">Last year:</th>
			<td>
				<input type="text" name="year" value="<?php echo $lm_fdt_options["year"] ?>" size="70" />
			</td>
		</tr>
		<tr>
			<th valign="top" scope="row" align="right">More than a year ago:</th>
			<td>
				<input type="text" name="years" value="<?php echo $lm_fdt_options["years"] ?>" size="70" />
			</td>
		</tr>

	</table>
	<p class="submit"><input type="submit" value="Update Options"></p>
	
	<fieldset class="options">
	<legend>Available replacements</legend>
	<table width="100%" cellspacing="2" cellpadding="5">
	<tr>
	<th>String</th><th>Description</th><th>Example usage</th>
	</tr>
	<tr>
	<td>%minutesSince%</td><td>Displays the minutes since the post</td><td>Posted %minutesSince% minutes ago</td>
	</tr>
	<tr>
	<td>%hoursSince%</td><td>Displays the hours since the post</td><td>Posted %hoursSince% hours ago</td>
	</tr>
	<tr>
	<td>%daysSince%</td><td>Displays the days since the post</td><td>Posted %daysSince% days ago</td>
	</tr>
	<tr>
	<td>%weeksSince%</td><td>Displays the weeks since the post</td><td>Posted %weeksSince% weeks ago</td>
	</tr>
	<tr>
	<td>%monthsSince%</td><td>Displays the months since the post</td><td>Posted %monthsSince% months ago</td>
	</tr>
	<tr>
	<td>%yearsSince%</td><td>Displays the years since the post</td><td>Posted %yearsSince% years ago</td>
	</tr>
	<tr>
	<td>%dayName%</td><td>Displays the name of the day when the post was published</td><td>Posted on a %dayName%</td>
	</tr>
	<tr>
	<td>%monthName%</td><td>Displays the name of the month when the post was published</td><td>Posted during %monthName%</td>
	</tr>
	<tr>
	<td>%weekOfMonth%</td><td>Displays the week of the month in which the post was published</td><td>Posted during the %weekOfMonth% week of %monthName%</td>
	</tr>
	<tr>
	<td>%fuzzyTimeText%</td><td>Displays the time, fuzzily</td><td>Posted yesterday, %fuzzyTimeText%</td>
	</tr>
	<tr>
	<td>%fuzzy12Hour%</td><td>Displays the time, fuzzily, in a 12 hour format</td><td>Posted today %fuzzy12Hour%</td>
	</tr>
	<tr>
	<td>%fuzzyWeekPeriod%<td>Displays the general time of the week the post was published</td><td>Posted last week, %fuzzyWeekPeriod%</td>
	</tr>
	<tr>
	<td>%fuzzyMonthPeriod%</td><td>Displays the general time of the month the post was published</td><td>Posted last year, %fuzzyMonthPeriod%</td>
	</tr>
	<tr>
	<td>%MinutesOrHoursSince%</td><td>Diplays the minutes or hours since the post was published, usually used when the post was published that day.</td><td>Posted %MinutesOrHoursSince% ago</td>
	</tr>
	<tr>
	<td>%actualDate%</td><td>Displays the date the post was published</td><td>Posted %actualDate%</td>
	</tr>
	<tr>
	<td>%actualTime%</td><td>Displays the time the post was published</td><td>Posted %actualTime%</td>
	</tr>
	</table>
	</fieldset>
</form>

</div>
<?php
}

/* Add the above pages to the wp-admin GUI */
function lm_fdt_add_pages() {
	add_options_page('Fuzzy DateTime', 'Fuzzy DateTime', 8, basename(__FILE__), 'lm_fdt_options_page');
}
add_action('admin_menu', 'lm_fdt_add_pages');
?>
