<?php
ob_end_clean();
###export.php###

//start session for export feature
session_start();
$allsqlresults= $_SESSION['query2'];
//for testing
//print_r($_SESSION['query2']);

require '/var/www/cdlc_script/cdlc_function.php';
#Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
//for testing
//echo "the query".$allsqlresults."<br>";
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);


$GETLIST = mysqli_query($db, $allsqlresults);
$row = mysqli_fetch_assoc($GETLIST);

$delimiter=",";
$filename= "nc_classlist_". date('Ymd').".csv";

$f= fopen('php://memory', 'w');
$fields = array('ID', 'NAME', 'System','ILL EMAIL', 'PHONE', 'Dept', 'Street Address', 'City, State, Zip', 'LOC Location', 'Email Alert', 'Participant', 'Syspend');

fputcsv($f, $fields, $delimiter);

while ($row=$GETLIST->fetch_assoc()) {
    $lineData = array($row['recnum'], $row['Name'], $row['system'], $row['ill_email'], $row['phone'], $row['address1'], $row['address2'], $row['address3'],$row['loc'], $ealert, $status,$suspend);

    fputcsv($f, $lineData, $delimiter);
}
fseek($f, 0);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="'. $filename .'";');
header( "Pragma: no-cache" );
header( "Expires: 0" );

fpassthru($f);

// writing headers to the csv file

//fputcsv($f, array('ID', 'NAME', 'System','ILL EMAIL', 'PHONE', 'Dept', 'Street Address', 'City, State, Zip', 'LOC Location', 'Email Alert', 'Participant', 'Syspend'));
//while ($row = mysqli_fetch_assoc($GETLIST)) {
//    $status = ($row['participant'] == 1) ? 'Active' : 'Inactive';
//    $suspend = ($row['suspend'] == 1) ? 'Active' : 'Inactive';
//    $ealert = ($row['LibEmailAlert'] == 1) ? 'Active' : 'Inactive';
//    $lineData = array($row['recnum'], $row['Name'], $row['system'], $row['ill_email'], $row['phone'], $row['address1'], $row['address2'], $row['address3'],$row['loc'], $ealert, $status,$suspend);
//    fputcsv($f, explode(',', $lineData));
//fputcsv($f, $lineData, $delimiter);
//}
// closing the "output" stream
//fclose($f);
//exit();
