<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('date.timezone', 'Europe/Berlin');


$ethwallet = '------------------------------------------';
$zilwallet = '------------------------------------------';
$host = '192.168.1.x';
$user = 'xxxxxx';
$pass = 'xxxxxx';
$database = 'xxxxxxx';
$rewardstable = 'miningrewards';

$con=mysqli_connect($host,$user,$pass,$database);
if (mysqli_connect_errno()) echo 'tits up: ' . mysqli_connect_error();

if ($argc > 2 && $argv[2] == 'firstrun') {
  $timeneeded = date('Y-m-d H:i:s', time() - 93600);
  $perpage = 10;
  $totalread = 0;
  $reached = 0;
  $u = 1;

  while ($reached == 0) {
    $url = 'https://billing.ezil.me/rewards/' . $ethwallet . '.' . $zilwallet . '?page=' . $u . '&per_page=' . $perpage . '&coin=' . $argv[1];
    $data = json_decode(file_get_contents($url), TRUE);

    $i = 0;
    while ($i < $perpage) {
      $value[] = $data[$i]['amount'];
      $createdat[] = str_replace("Z", "" ,str_replace("T", " ", $data[$i]['created_at']));
      $id[] = $data[$i]['id'];
      if (str_replace("Z", "" ,str_replace("T", " ", $data[$i]['created_at'])) > $timeneeded) {
        $queryline[] = "INSERT INTO " . $rewardstable . " VALUES('" . str_replace('Z', '' ,str_replace('T', ' ', $data[$i]['created_at'])) . "', '" . $argv[1] . "', " . $data[$i]['amount'] . ");";
        $totalread++;
      } else $reached = 1;
      $i++;
    }
    echo "cycle " . $u . " done\n";
    $u++;
  }

  $i = $totalread-1;
  while ($i >= 0) {
    mysqli_query($con, $queryline[$i]);
    $i--;
  }

} else if ($argc > 1 && $argv[1] == 'eth' || $argc > 1 && $argv[1] == 'zil') {
  $sqlquery = "select datetime from miningrewards where coin='" . $argv[1] . "' order by datetime desc limit 1;";
  $result = mysqli_query($con, $sqlquery);
  $lastdatetime = mysqli_fetch_assoc($result);
  $timeneeded = $lastdatetime['datetime'];
  $perpage = 10;
  $totalread = 0;
  $reached = 0;
  $u = 1;
  while ($reached == 0) {
    $url = 'https://billing.ezil.me/rewards/' . $ethwallet . '.' . $zilwallet . '?page=' . $u . '&per_page=' . $perpage . '&coin=' . $argv[1];
    $data = json_decode(file_get_contents($url), TRUE);

    $i = 0;
    while ($i < $perpage) {
      $value[] = $data[$i]['amount'];
      $createdat[] = str_replace("Z", "" ,str_replace("T", " ", $data[$i]['created_at']));
      $id[] = $data[$i]['id'];
      if (str_replace("Z", "" ,str_replace("T", " ", $data[$i]['created_at'])) > $timeneeded) {
        $queryline[] = "INSERT INTO " . $rewardstable . " VALUES('" . str_replace('Z', '' ,str_replace('T', ' ', $data[$i]['created_at'])) . "', '" . $argv[1] . "', " . $data[$i]['amount'] . ");";
        $totalread++;
      } else $reached = 1;
      $i++;
    }
    $u++;
  }
  $i = $totalread-1;
  while ($i >= 0) {
    mysqli_query($con, $queryline[$i]);
    $i--;
  }
} else echo "use " . $argv[0] . " eth or zil to update or add firstrun to the end to add last 24h into database\n";


mysqli_close($con);
