<?php

###sent.php###

require '/var/www/cdlc_script/cdlc_function.php';
#Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

$fname=$_REQUEST["fname"];
$lname=$_REQUEST["lname"];

$email=$_REQUEST["email"];
$inst=$_REQUEST["inst"];
$address=$_REQUEST["address"];
$caddress=$_REQUEST["caddress"];
$wphone=$_REQUEST["wphone"];

if (isset($_REQUEST['reqLOCcode'])) {
    $reqLOCcode = $_REQUEST['reqLOCcode'];
}
if (isset($_REQUEST['bibauthor'])) {
    $author = $_REQUEST['bibauthor'];
}
if (isset($_REQUEST['bibtitle'])) {
    $title = $_REQUEST['bibtitle'];
}
if (isset($_REQUEST['bibtype'])) {
    $itype = $_REQUEST['bibtype'];
}
if (isset($_REQUEST['pubdate'])) {
    $pubdate = $_REQUEST['pubdate'];
}
if (isset($_REQUEST['isbn'])) {
    $isbn = $_REQUEST['isbn'];
}
if (isset($_REQUEST['issn'])) {
    $issn = $_REQUEST['issn'];
}
if (isset($_REQUEST['needbydate'])) {
    $needbydate = $_REQUEST['needbydate'];
}
if (isset($_REQUEST['reqnote'])) {
    $reqnote = $_REQUEST['reqnote'];
}
if (isset($_REQUEST['patronnote'])) {
    $patronnote = $_REQUEST['patronnote'];
}

#Pull all the articles files and then combine into one variable
if (isset($_REQUEST['arttile'])) {
    $arttile = $_REQUEST['arttile'];
}
if (isset($_REQUEST['artauthor'])) {
    $artauthor = $_REQUEST['artauthor'];
}
if (isset($_REQUEST['artissue'])) {
    $artissue = $_REQUEST['artissue'];
}
if (isset($_REQUEST['artvolume'])) {
    $artvolume = $_REQUEST['artvolume'];
}
if (isset($_REQUEST['artpage'])) {
    $artpage = $_REQUEST['artpage'];
}
if (isset($_REQUEST['artmonth'])) {
    $artmonth = $_REQUEST['artmonth'];
}
if (isset($_REQUEST['artyear'])) {
    $artyear = $_REQUEST['artyear'];
}
if (isset($_REQUEST['artcopyright'])) {
    $artcopyright = $_REQUEST['artcopyright'];
}
$article="Article Title: ". $arttile ." <br>Article Author: ". $artauthor ." <br>Volume: ". $artvolume ."<br>Issue: ". $artissue ."<br> Pages: ". $artpage ." <br>Year: ".$artyear." <br>Month:  ".$artmonth."<br>Copyright: ".$artcopyright." ";
if (strlen($needbydate)>0) {
    $needbydatet="This item is needed by $needbydate";
}
if (strlen($reqnote)>0) {
    $reqnote="Note: $reqnote";
}
if (strlen($patronnote)>0) {
    $patronnote="Patron: $patronnote";
}
if (strlen($isbn)>2) {
    $isbn="ISBN: $isbn";
}
if (strlen($issn)>2) {
    $issn="ISSN: $issn";
}

#Pull the information of the person making the request
$reqsystem=$field_home_library_system;
foreach ($_POST['libdestination'] as $destination) {

    list($libcode, $library, $destsystem, $itemavail, $itemcall, $itemlocation, $destemail, $destloc) = explode(":", $destination);
    #UnHTML encodes call numbers that might have strange characters
    $itemcall = htmlspecialchars_decode($itemcall, ENT_QUOTES);
    $libcode = htmlspecialchars_decode($libcode, ENT_QUOTES);
    $library = htmlspecialchars_decode($library, ENT_QUOTES);

    #Put the dest email in an array in case the library has more than one person who gets the message
    $destemailarray = explode(';', $destemail);
    #Check to see if data was posted to the forum
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        #Insert request into Database
        $today = date('Y-m-d H:i:s');

        #Add escape for the title, author, call number, Library name, and Requester Name
        $ititle = mysqli_real_escape_string($db, $title);
        $article = mysqli_real_escape_string($db, $article);
        $iauthor = mysqli_real_escape_string($db, $author);
        $pubdate = mysqli_real_escape_string($db, $pubdate);
        $isbn = mysqli_real_escape_string($db, $isbn);
        $issn = mysqli_real_escape_string($db, $issn);
        $itemcall = mysqli_real_escape_string($db, $itemcall);
        $itemlocation = mysqli_real_escape_string($db, $itemlocation);
        $itype = mysqli_real_escape_string($db, $itype);
        $itemavail = mysqli_real_escape_string($db, $itemavail);
        $inst = mysqli_real_escape_string($db, $inst);
        $fname = mysqli_real_escape_string($db, $fname);
        $lname = mysqli_real_escape_string($db, $lname);
        $email = mysqli_real_escape_string($db, $email);
        $library = mysqli_real_escape_string($db, $library);
        $needbydate = mysqli_real_escape_string($db, $needbydate);
        $reqnote = mysqli_real_escape_string($db, $reqnote);
        $patronnote = mysqli_real_escape_string($db, $patronnote);
        $destloc = mysqli_real_escape_string($db, $destloc);
        $reqLOCcode = mysqli_real_escape_string($db, $reqLOCcode);
        $wphone = mysqli_real_escape_string($db, $wphone);
        $saddress = mysqli_real_escape_string($db, $address);
        $caddress = mysqli_real_escape_string($db, $caddress);

        $destloc = trim($destloc);
        $reqLOCcode = trim($reqLOCcode);
        #The SQL statement to insert for Stats and to recall if needed in the future
        #$itemcall_s = mysql_escape_string($itemcall);



        $sql = "INSERT INTO `$cdlcSTAT` (`illNUB`,`Title`,`Author`,`pubdate`,`reqisbn`,`reqissn`,`itype`,`Call Number`,`Location`,`Available`,`article`,`needbydate`,`reqnote`,`patronnote`,`Destination`,`DestSystem`,`Requester lib`,`Requester LOC`,`ReqSystem`,`Requester person`,`requesterEMAIL`,`Timestamp`,`Fill`,`responderNOTE`,`requesterPhone`,`saddress`,`caddress`)
 VALUES ('0','$ititle','$iauthor','$pubdate','$isbn','$issn','$itype','$itemcall','$itemlocation','$itemavail','$article','$needbydate','$reqnote','$patronnote','$destloc','$destsystem','$inst','$reqLOCcode','$reqsystem','$fname $lname','$email','$today','3','','$wphone','$saddress','$caddress')";
         //for testing
        //for testing
      //  echo $sql."<br>";

        if (mysqli_query($db, $sql)) {
            //for testing
            //echo "SQL was good<br><br>";
            #Get the SQL id and create a ILL Number
            $sqlidnumb= mysqli_insert_id($db);
            $yearid=date('Y');
            $illnum="$yearid-$sqlidnumb";
            $sqlupdate = "UPDATE `$cdlcSTAT` SET `illNUB` =  '$illnum' WHERE `index` = $sqlidnumb";
            echo "Request <b>$illnum</b> has been emailed to <b>$library.</b><br>";
            mysqli_query($db, $sqlupdate);

            #SETUP email
            #We'll set these to white space if they are empty to prevent an error message
            if (empty($needbydatet)) {
                $needbydatet='';
            }
            if (empty($reqnote)) {
                $reqnote='';
            }
            if (empty($arttile)) {
                $article='';
            }
            #Copy of message sent to the requester
            $messagereq = "An ILL request ($illnum) has been created for the following: <br><br>
     Library: $library <br>
     Title: $title <br>
     Author: $author<br>
     Item Type: $itype<br>
     Publication Date: $pubdate<br>
     $isbn<br>
     $issn<br>
     Call Number: " . $itemcall . " <br>
     Availability Status: $itemavail<br>
     Location: $itemlocation<br>
     $article<br><br>
     <a href='https://eform.cdlc.org/cancel?num=$illnum&a=3' >Do you need to cancel this request? </a>
     <br><br>
     The title is requested by the following library:<br>
     $inst<br>
     $address<br>
     $caddress<br><br>
     Need by: $needbydate<br>
     Note from requestor: $reqnote<br><br><br>
     The request was created by:<br>
     $fname $lname<br>
     $email<br>
     $wphone<br>";

            #Message for the destination library
            $messagedest = "An ILL request ($illnum) has been created for the following: <br><br>
     Library: $library <br>
     Title: $title <br>
     Author: $author<br>
     Item Type: $itype<br>
     Publication Date: $pubdate<br>
     $isbn<br>
     $issn<br>
     Call Number: " . $itemcall . " <br>
     Availability Status: $itemavail<br>
     Location: $itemlocation<br>
     $article<br><br><br>
     The title is requested by the following library:<br>
     $inst<br>
     $address<br>
     $caddress<br><br>
     Need by: $needbydate<br>
     Note from requestor: $reqnote<br><br><br>
     The request was created by:<br>
     $fname $lname<br>
     $email<br>
     $wphone<br><br>
     Will you fill this request?  <a href='https://eform.cdlc.org/respond?num=$illnum&a=1' >Yes</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='https://duenorth.nnyln.org/respond?num=$illnum&a=0' >No</a><br>";

            #Set email subject for request
            $subject = "NEW ILL Request from $inst ILL# $illnum";

            #SEND EMAIL to destination Library with DKIM Signature
            $email_to = implode(',', $destemailarray);
            $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "eFrom" <donotreply@cdlc.org>' . "\r\n" . "Reply-to: " . $email . "\r\n" . 'Content-type: text/html; charset=utf8';

            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            #mail has been disabled for development
            # for testing mail($email_to, $subject, $messagedest, $headers);

            #SEND a copy of EMAIL to the requester with DKIM sig
            $headers = 'MIME-Version: 1.0' . "\r\n" . 'From: "eForm" <donotreply@cdlc.org>' . "\r\n" . "Reply-to: " . $email_to . "\r\n" . 'Content-type: text/html; charset=utf8';

            $messagereq = preg_replace('/(?<!\r)\n/', "\r\n", $messagereq);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        #mail has been disabled for development
        # for testing mail($email, $subject, $messagereq, $headers);
        } else {
            #Something happened, and I could not create a request
            echo "Error: " . $sql . "<br>" . mysqli_error($db);
            echo "Unable to create request due a technical issue, if this happens again, please contact CDCL Tech Support";
            echo "<br><br>";
        }#end if for SQL query check
    }//end if the check for POST


    #This will generate the web page response
    echo "<br>Details of your request(s):<br>
Library: <b>$library</b><br>
Title: <b>$title</b><br>
Author: <b>$author</b><br>
Publication Date: <b>$pubdate</b><br><br>
A copy of this request has also been emailed to the requester $fname $lname at $email.<br>" ;
}//end foreach loop
#Ask the requester if they would like to make another request
echo "<br><a href='https://eform.cdlc.org'>Would you like to make another request?<a>";
mysqli_close($db);
