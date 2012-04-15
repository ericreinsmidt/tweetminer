<?php

	/*

	Author @ Eric Reinsmidt

	Purpose:
		This script is used to coalesce text files that have been processed using processor.php

	Usage:
		Call script from command line in the format:
		php appender.php <source file> <destination file>
		<source file> and <destination file> are passed as filename with extension.

	Output:
		The script will create a file name tweets/<destination file>.txt

	Example:
		php appender.php easter+egg.txt_processed.txt huge_chunk_of_files.txt

	*/

	// Suppress DateTime warnings
	date_default_timezone_set(@date_default_timezone_get());

	// grab <source file> from args
	$filein =  "tweets/".$_SERVER['argv'][1];

	// grab <destination file> from args
	$fileout = "tweets/".$_SERVER['argv'][2];
	if(!file_exists(dirname($fileout)))
	    mkdir(dirname($fileout), 0777, true);

	// open file dba_handlers()
	$handle = fopen($filein, "r");

	// throw error message
	if ($handle===false) {
		if(!file_exists(dirname("log/appender_errors.log")))
		    mkdir(dirname("log/appender_errors.log"), 0777, true);
		error_log(date('Y.m.d h:i:s A')." ".$filein." did not open!\n", 3, "log/appender_errors.log");
		die("ERROR: Check log/appender_errors.log\n");
	}

	// grab each line until EOF and send to function
	while (!feof($handle)) {
		$line = fgets($handle);
		process_line($line, $fileout);
	}

	// close file handler
	fclose($handle);

	// write each line to file
	function process_line($line, $fileout) {

		// open file handler
		$handle = fopen($fileout, "a");
		
		// throw error message
		if ($handle===false) {
			if(!file_exists(dirname("log/appender_errors.log")))
			    mkdir(dirname("log/appender_errors.log"), 0777, true);
			error_log(date('Y.m.d h:i:s A')." ".$fileout." did not open!\n", 3, "log/appender_errors.log");
			die("ERROR: Check log/appender_errors.log\n");
		}
		
		// write line to file
		fwrite($handle, $line);

		// close file handler
		fclose($handle);
	}

?>