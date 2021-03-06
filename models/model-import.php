<?php

Class RKVoters_ImportModel {

  function __construct(){
    global $rkdb;
    $this -> db = $rkdb;
  }

  // data handling methods

  function _processStreets(){

    $campaignId = $this -> campaignId;

    $sql =  "SELECT DISTINCT stname, city FROM voters " .
            "WHERE campaignId=" . (int) $campaignId . " ORDER BY stname";

    echo $sql;

    $streets = $this -> db -> get_results($sql);

    // foreach($streets as $street){
    //   $s = array();
    //   $s['street_name'] = $street -> stname;
    //   $s['city'] = $street -> city;
    //   $s['campaignId'] = $campaignId;
    //   $this -> db -> getOrCreate('voters_streets', $s, $s);
    // }


    $sql = "SELECT * FROM voters_streets where  campaignId=" . (int) $campaignId;
    $streets = $this -> db -> get_results($sql);
    foreach($streets as $street){
      $update = array(
        "streetId" => $street -> streetid
      );
      $where = array(
        "city" => $street -> city,
        "stname" => $street -> street_name,
        "campaignId" => $this -> campaignId
      );
      $this -> db -> update("voters", $update, $where, false);
    }

  }

  function geoCodeVoter($rkid){
    global $config;


    // get the voter
    $sql = "SELECT * FROM voters WHERE rkid=" . (int) $rkid;
    $voter = $this -> db -> get_row($sql);
    if(count($voter) == 0){
      exit("Voter " . $rkid . " not found.");
    }


    // if lattitude is already set, continue
    if($voter -> lat != 0) {
      return array(
        "addr_error" => "Error: This voter has already been looked up.",
        "updatedVoter" => $voter
      );
    }


    // call the google maps api
    $address = $voter -> stnum . " " . $voter -> stname . ", " . $voter -> city . ", " . $voter -> state . " " . $voter -> zip;
    $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' .
            urlencode($address) .
            '&key=' . $config['googlemaps_apikey'];
    $addr_data = json_decode(file_get_contents($url));


    // if not found, return error
    if(count($addr_data -> results) == 0){
      return array(
        "addr_error" => "Error: Google didn't find any matching records for: " . $address . "\n$url\n\n",
        "updatedVoter" => $voter
      );
    }


    // parse data and update voter
    $location = (array) $addr_data -> results[0] -> geometry -> location;
    $address_components = $addr_data -> results[0] -> address_components;
    $neighborhoodName = "";
    foreach($address_components as $c){
      if($c -> types[0] == 'neighborhood'){
        $neighborhoodName = $c -> long_name;
        break;
      }
    }
    $update = array(
      "lat" => $location['lat'],
      "lon" => $location['lng'],
      "google_neighborhood" => $neighborhoodName
    );
    $where = array(
      "rkid" => $voter -> rkid
    );
    $updatedVoter = $this -> db -> update("voters", $update, $where);



    return array(
      "addr_data" => $addr_data,
      "updatedVoter" => $updatedVoter
    );

  }

  function getAllVotersInCampaign(){
    $campaignId = $this -> campaignId;
    $sql = "SELECT rkid, lat from VOTERS where campaignId=" . (int) $campaignId;
    $rkids = $this -> db -> get_results($sql);
    exit(json_encode($rkids));
  }
}
