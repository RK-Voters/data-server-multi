<?php
  set_time_limit(0);
  header('Content-Type: text/plain');
  error_reporting(E_ALL);
  ini_set("display_errors", 1);
  include("rk-config.php");


  include("models/model-import.php");
  $model = new RKVoters_ImportModel();
  $model -> campaignId = 2;
  $model -> _processStreets();


  global $rkdb;

  // $addresses = json_decode(file_get_contents("voters.js"));
  // foreach($addresses as $a){
  //   $rkdb -> insert("voters", $a);
  // }





  // for nb file
  // include("models/model-nationbuilder.php");
  // $nbmodel = new RKVoters_NationbuilderImportModel();

  // $nbmodel -> campaignId = 2;

  // $voter_file = "data/nbvf.csv";
  // $nbmodel -> importVoterFile($voter_file);
  // // //
  // $vh_file = "data/nbvh.csv";
  // $nbmodel -> importVoterHistoryFile($vh_file, "data/vh.sql");
