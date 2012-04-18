<?php

	/*
	
	Author @ Eric Reinsmidt

	Purpose:
		This script is used to mine tweets using the Topsy Otter API.
		7,000 requests per day are allowed with an API key. With no key, 3,000 requests are permitted per IP.
		Tweets are writtn to file in the format:
			<start date> - tweet content

	Usage:
		Call script from command line in the format:
		php tweetminer.php <search terms> <start date> <end date> <time window>
		<search term> must be in URL friendly format, e.g. "My new search" should be passed as My+new+search
		<start date> and <end date> must be passed as month/day/year or month/day/year@hour:minute:second in GMT
		<time window> is passed in seconds. Only 1000 results are availble for each window, so smaller
			time window values ensure more complete results

	Output:
		The script will create a file named tweets/<search terms>.txt

	Example:
		php tweetminer.php easter+egg 2/27/2012 2/27/2012@14:35:11 7200
		This will grab all tweets with the words "easter" and "egg" from
			2/27/2012 @ 00:00:00 AM GMT to 2/27/2012 @ 2:35:11 PM GMT
			using a two hour time window.

	*/

	//////////////////////////////////////////////
	/*          Set server variables            */
	//////////////////////////////////////////////
	
	// Suppress DateTime warnings
	date_default_timezone_set(@date_default_timezone_get());

	// set allowed script execution time in seconds
	ini_set('max_execution_time', 3600);

	//////////////////////////////////////////////
	/*         Store arguments in vars          */
	//////////////////////////////////////////////
	
	// grab <search term> from args
	$search_term = $_SERVER['argv'][1];

	// grab <start date> from args
	$raw_start_date = explode("@", $_SERVER['argv'][2]);

	// grab <end date> from args
	$raw_end_date = explode("@", $_SERVER['argv'][3]);

	// grab <time window> from args
	$timewindow = $_SERVER['argv'][4];

	//////////////////////////////////////////////
	/*        Process dates to timestamp        */
	//////////////////////////////////////////////
	
	// split date and time from <start date>
	$date = explode("/", $raw_start_date[0]);

	// if hour:minute:second provided use, else set to midnight
	if (isset($raw_start_date[1])) {
		$time = explode(":", $raw_start_date[1]);
	} else {
		$time = array(0,0,0);
	}

	// create starting unix timestamp for API request
	$mintime = mktime($time[0],$time[1],$time[2],$date[0],$date[1],$date[2]);

	// split date and time from <end date>
	$date = explode("/", $raw_end_date[0]);

	// if hour:minute:second provided use, else set to midnight
	if (isset($raw_end_date[1])) {
		$time = explode(":", $raw_end_date[1]);
	} else {
		$time = array(0,0,0);
	}

	// create ending unix timestamp for API request
	$maxtime = mktime($time[0],$time[1],$time[2],$date[0],$date[1],$date[2]);
	
	//////////////////////////////////////////////
	/*           Set beginning vars             */
	//////////////////////////////////////////////
	
	// boolean flag for when all tweets are mined
	$done = false;

	// offset passed to query
    $offset = 0;

    // var to store last offset received
    $last_offset = 0;

    // total results returned for time window
    $total_tweets = 999999999;

    // provided by Topsy.com
    $apikey = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';

    // track number of page requests, only 10 allowed per time window
    $num_pages = 0;
    
    //////////////////////////////////////////////
    /*                Start mining              */
    //////////////////////////////////////////////
    
    // continue until done
    while(!$done)
    {
    	// end if all dates mined
    	if ($mintime >= $maxtime) {
			$done = true;
		}

		// if all tweets grabbed, or 10 pages requested, increment <start date> and reset offset
		if ($total_tweets <= $last_offset || $num_pages > 9) {
			$mintime = $mintime+$timewindow;
			$offset = 0;
			$num_pages = 0;
		}

		// request to Topsy's Otter API
		$request = 'http://otter.topsy.com/search.json?q='.$search_term.'&mintime='.$mintime.'&maxtime=';
		$request .= ($mintime+$timewindow).'&perpage=100&offset='.$offset.'&nohidden=0'.'&apikey='.$apikey;

		// open file handler for requested data
		$handle = fopen($request, "r");

		// throw error message
		if ($handle===false) {
			if(!file_exists(dirname("log/tweetminer_errors.log")))
			    mkdir(dirname("log/tweetminer_errors.log"), 0777, true);
			error_log(date('Y.m.d h:i:s A')." The http stream failed!\n", 3, "log/tweetminer_errors.log");
			die("ERROR: Check log/tweetminer_errors.log\nRestart at ".($mintime)."\n");
		}

		// decode the returned json data
		$twt_decoded = json_decode(stream_get_contents($handle), true);

		// close the file handler
		fclose($handle);

		// total number of results for query within timeframe
		$total_tweets = $twt_decoded['response']['total'];

		// set last offset to know starting point for next request
		$last_offset = $twt_decoded['response']['last_offset'];

		// total number of returned tweets in current request
		$num_tweets = count($twt_decoded['response']['list']);

		// set filename with search term name
		$twt_file = "tweets/".$search_term.".txt";
		if(!file_exists(dirname($twt_file)))
		    mkdir(dirname($twt_file), 0777, true);

		// open or create file handler for appending
		$handle = fopen($twt_file, 'a');

		// throw error message
		if ($handle===false) {
			if(!file_exists(dirname("log/tweetminer_errors.log")))
			    mkdir(dirname("log/tweetminer_errors.log"), 0777, true);
			error_log(date('Y.m.d h:i:s A')." ".$twt_file." did not open!\n", 3, "log/tweetminer_errors.log");
			die("ERROR: Check log/tweetminer_errors.log\nRestart at ".($mintime)."\n");
		}

		// write each tweet to file
		for ($i=0; $i < $num_tweets; $i++) {
			$tweet = html_entity_decode($twt_decoded['response']['list'][$i]['content'], ENT_QUOTES);
			fwrite($handle, $mintime.' - '.$tweet."\n");
		}
		
		// close the file handler
		fclose($handle);
		
		// set offset to last requests ending offset
		$offset = $last_offset;

		// increment number of pages requested
		$num_pages++;
	}
?>