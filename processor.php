<?php

	/*

	Author @ Eric Reinsmidt

	Purpose:
		This script is used to post process tweets mined using tweetminer.php

	Usage:
		Call script from command line in the format:
		php processor.php <filename>
		<filename> is passed as the entire filename, e.g. somefile.txt

	Output:
		The script will create a file name tweets/<filename>_processed.txt

	Example:
		php processor.php easter+egg.txt

	*/

	// Suppress DateTime warnings
	date_default_timezone_set(@date_default_timezone_get());

	// grab <filename> from args
	$filein =  "tweets/".$_SERVER['argv'][1];

	// create filename for processed file
	$fileout = "tweets/".$_SERVER['argv'][1].'_processed.txt';
	if(!file_exists(dirname($fileout)))
	    mkdir(dirname($fileout), 0777, true);

	// open file handler
	$handle = fopen($filein, "r");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/processor_errors.log")))
		    mkdir(dirname("log/processor_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$filein." did not open!\n", 3, "log/processor_errors.log");
		die("ERROR: Check log/processor_errors.log\n");
	}

	// put contents of <filename> into a string to regex it
	$contents = stream_get_contents($handle);

	// close file handler
	fclose($handle);

	// remove unwanted line breaks
	$contents = preg_replace("/(\n(?!\d{10})|\r(?!\d{10})|\r\n(?!\d{10}))/", " ", $contents);

	// open file handler
	$handle = fopen($filein, "w+");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/processor_errors.log")))
		    mkdir(dirname("log/processor_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$filein." did not open!\n", 3, "log/processor_errors.log");
		die("ERROR: Check log/processor_errors.log\n");
	}

	// overwrite file with regexed contents
	fwrite($handle, $contents);

	//echo $contents;
	fclose($handle);

	// open file handler
	$handle = fopen($filein, "r");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/processor_errors.log")))
		    mkdir(dirname("log/processor_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$filein." did not open!\n", 3, "log/processor_errors.log");
		die("ERROR: Check log/processor_errors.log\n");
	}

	// grab each line until EOF and send to function
	while (!feof($handle)) {
		$line = fgets($handle);
		process_line($line, $fileout);
	}

	// close file handler
	fclose($handle);

	// open file handler
	$handle = fopen($fileout, "r");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/processor_errors.log")))
		    mkdir(dirname("log/processor_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$fileout." did not open!\n", 3, "log/processor_errors.log");
		die("ERROR: Check log/processor_errors.log\n");
	}

	// put contents of <filename> into a string to regex it
	$contents = stream_get_contents($handle);

	// close file handler
	fclose($handle);

	// remove unwanted line breaks
	$contents = preg_replace("/\n(?!\d{10})/", " ", $contents);

	// open file handler
	$handle = fopen($fileout, "w+");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/processor_errors.log")))
		    mkdir(dirname("log/processor_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$fileout." did not open!\n", 3, "log/processor_errors.log");
		die("ERROR: Check log/processor_errors.log\n");
	}

	// overwrite file with regexed contents
	fwrite($handle, $contents);

	//echo $contents;
	fclose($handle);

	// process each line
	function process_line($line, $fileout) {

		// remove all tweets longer than 140 char (invalid data)
		$line = preg_replace("/\d{10} - .{141,}/", '', $line);

		// remove all empty tweets
		$line = preg_replace("/\d{10} - \n/", '', $line);

		// open file handler
		$handle = fopen($fileout, "a");

		// throw error message
		if ($handle===false) {
			if(!file_exists(dirname("log/processor_errors.log")))
			    mkdir(dirname("log/processor_errors.log"), 0777, true);
			error_log(date('Y.m.d h:i:s A')." ".$fileout." did not open!\n", 3, "log/processor_errors.log");
			die("ERROR: Check log/processor_errors.log\n");
		}

		// write line to file
		fwrite($handle, $line);

		// close file handler
		fclose($handle);
	}

?>