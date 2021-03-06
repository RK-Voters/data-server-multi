<?php

	/*************************************************************************************************************
	*	GEOCODING SCRIPT
	
		- Designed to be run from a browser, this script updates the voter database using the Google Geocoding API

		- @param : read two $_GET variables: rkid and campaignId 

		- If rkid is set, 
			- Fetch the user and update their lot \ lon.
			- Return JSON with the Google request and their updated entry in the voter table.


		- Else if campaignId is set, 
			- iterate through all the active voters in the campaign
			- geocode them accordingly
			- flush regular udates to the screen


	*************************************************************************************************************/


	set_time_limit(0);
	header('Content-Type: text/plain');

	// load data handler
	include("../rk-config.php");
	include("../models/model-import.php");
	$import_model = new RKVoters_ImportModel();
	ob_start();


	// if an rkid is specified, geo-code that voter
	if(isset($_GET['rkid']) && is_numeric($_GET['rkid'])) {
		$rkid = (int) $_GET['rkid'];
		$geocode_data = $import_model -> geoCodeVoter($rkid);
		exit(json_encode($geocode_data, JSON_PRETTY_PRINT));
	}


	// if a campaignid is specified, geo-code all the remaining active voters in that campaign
	else if(isset($_GET['campaignId']) && is_numeric($_GET['campaignId'])) {
		global $rkdb;
		$campaignid =  (int) $_GET['campaignId'];

		$sql = "SELECT Count(*) FROM voters where campaignid=" . $campaignid . " AND active=1";
		$totalSize = $rkdb -> get_var($sql);


		$sql = "SELECT rkid FROM voters where campaignId=" . $campaignid . " AND active=1 AND lat=0";
		$targetList = $rkdb -> get_results($sql);

		
		foreach($targetList as $k => $voter){

			$rkid = $voter -> rkid;
			$geocode_data = $import_model -> geoCodeVoter($rkid);

			if (isset($geocode_data["addr_error"])) {
    			echo $geocode_data["addr_error"];
    			continue;
			}

			$sql = "SELECT Count(*) FROM voters where campaignid=" . $campaignid . " AND active=1 AND lat=0";
			$remainingSize = $rkdb -> get_var($sql);

			$completed = parseInt($totalSize) - parseInt($remainingSize);

			echo ($k + 1) . ". Mapped Voter #" . $rkid . " - " . $completed . " done. " . 
							$remainingSize . " remaining. " . $totalSize . " total.\n\n";

			ob_flush(); flush();

		}
		
	}

	// else it's a dud
	else {
		exit("Please provide an rkid or a campaignid.");		
	}



