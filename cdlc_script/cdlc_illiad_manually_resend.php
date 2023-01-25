<?php
//resend a request to ILLiad

//note article does not work
$illnumb='14149';


// Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

$sqlselect="select * from `$cdlcSTAT` where `index`='$illnumb' limit 1";
echo $sqlselect."\n";;
$retval = mysqli_query($db, $sqlselect);
//$GETLISTCOUNT = mysqli_num_rows($retval);
$GETLISTCOUNT = '1';
while ($row = mysqli_fetch_assoc($retval)) {
    $timestamp    = $row["Timestamp"];
    $destloc = $row["Destination"];
    $illnum    = $row["illNUB"];
    $ititle    = $row["Title"];
    $iauthor    = $row["Author"];
    $itype    = $row["Itype"];
    $itemcall      = $row["Call Number"];
    $pubdate    = $row["pubdate"];
    $isbn        = $row["reqisbn"];
    $issn        = $row["reqissn"];
    $itemavail    = $row["Available"];
    $article    = $row["article"];
    $inst    = $row["Requester lib"];
    $address    = $row["saddress"];
    $caddress    = $row["caddress"];
    $needbydatet    = $row["needbydate"];
    $reqnote      =  $row["reqnote"];
    $fname    = $row["Requester person"];
    $email    = $row["requesterEMAIL"];
    $wphone    = $row["requesterPhone"];
    $article    = $row["article"];
    $reqLOCcode = $row["Requester LOC"];

    //setting this to empty so it will for a book request
    $arttile='';

    $destloc = trim($destloc);
    $reqLOCcode = trim($reqLOCcode);
    $illiadchecksql = "SELECT IlliadDATE,IlliadURL,Illiad,APIkey,LibEmailAlert FROM `$cdlcLIB` WHERE `loc`='$destloc'";
    $illiadGETLIST = mysqli_query($db, $illiadchecksql);
    $illiadGETLISTCOUNT = '1';
    $illiadrow = mysqli_fetch_assoc($illiadGETLIST);
    $libilliadurl = $illiadrow["IlliadURL"];
    $libilliaddate = $illiadrow["IlliadDATE"];
    $libilliad = $illiadrow["Illiad"];
    $libilliadkey = $illiadrow["APIkey"];
    $libemailalert = $illiadrow["LibEmailAlert"];


    $sqlilliadmp = "SELECT * FROM `$cdlcILLiadMapping` WHERE `LOC`='$reqLOCcode' and `illiadID`='$destloc'";
    $sqlilliadmpGETLIST = mysqli_query($db, $sqlilliadmp);
    $sqlilliadmpGETLISTCOUNT = '1';
    $sqlilliadmprow = mysqli_fetch_assoc($sqlilliadmpGETLIST);
    $illiadADDnumb  = $sqlilliadmprow["illiadADDnumb"];
    $illiadLIBSymbol =  $sqlilliadmprow["illiadLIBSymbol"];
    // Add slashes to these string to prevent coding issue
    $ititle=addslashes($ititle);
    $iauthor=addslashes($iauthor);
      //Generate the due date, requrired for ILLiad Loans
           echo "number is days is".$libilliaddate."\n";
    if (ctype_digit($libilliaddate)) {
        $date = date("Y-m-d");
        $illduedateCAL= date('Y-m-d', strtotime($date. ' + '.$libilliaddate.' days'));
    }
           echo "my due date is".$illduedateCAL."\n";
        // Store data for request in array
    if (empty($arttile)) {
        $jsonstr = array( 'Username' =>'Lending','LendingString'=>$reqnote,'RequestType'=>Loan,'DueDate'=>$illduedateCAL.'T00:00:00-04:00','ProcessType'=>'Lending','LenderAddressNumber'=>$illiadADDnumb,'Len
dingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lending Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'ILLNumber'=>$illnum);
    } else {
        $jsonstr = array('Username' =>'Lending','LendingString'=>$reqnote,'ProcessType'=>Lending,'LenderAddressNumber'=>$illiadADDnumb,'LendingLibrary'=>$illiadLIBSymbol,'TransactionStatus'=>'Awaiting Lendi
ng Request Processing','LoanTitle'=>$ititle,'LoanAuthor'=>$iauthor,'CallNumber'=>$itemcall,'LoanDate'=>$pubdate,'PhotoArticleTitle'=>$arttile,'PhotoArticleAuthor'=>$artauthor,'PhotoJournalVolume'=>$artvolume,'Ph
otoJournalIssue'=>$artissue,'PhotoJournalYear'=>$artyear,'PhotoJournalInclusivePages'=>$artpage,'ISSN'=>$issn,'ILLNumber'=>$illnum);
    }
     

      // Enocde the array in to json data
      $json_enc=json_encode($jsonstr);

      //just so we can see this on screen
      //echo "<br /><br /><br />";
      //echo $json_enc;
      //echo "<br /><br /><br />";
      // variables to pass through cURL

      define("ILLIAD_REQUEST_TOKEN_URL", $libilliadurl);

      $key = $libilliadkey;
      // create the cURL request
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, ILLIAD_REQUEST_TOKEN_URL);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json_enc);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // commenting this out prints to screen (via echo)
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
                 "Content-Type: application/json",
                 "Content-Length: " . strlen($json_enc),
                 "ApiKey: $key")
    );

    // make the call
    if (!curl_errno($ch)) {
        // $output contains the output string
        $output = curl_exec($ch);
    }

    // close curl resource to free up system resources
    curl_close($ch);


    // print the results of the call to the screen
    echo "<!--API output-->";
    echo "<!--".$output."-->";
    $output_decoded = json_decode($output, true);
    $illiadtxnub= $output_decoded['TransactionNumber'];
    $illstatus = $output_decoded['TransactionStatus'];

    if (strlen($illiadtxnub)<4) {
        $headers = "From: CDLC eForm <dontreply@CDCL.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $messagereq = "Request did not go to ILLiad Ill ".$illnum." ".$output." ";
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail("spalding@senylrc.org", "CDLC ILLiad Failure", $messagereq, $headers, "-f donotreply@cdlc.org");
    } //end check if ILLad transaction did not happen




    //save API output to the request
    $sqlupdate2 = "UPDATE `$cdlcSTAT` SET `IlliadStatus` = '$illstatus', `IlliadTransID` = '$illiadtxnub' WHERE `index` = $illnumb";
    //echo $sqlupdate2;

    if (mysqli_query($db, $sqlupdate2)) {
        //mysqli_query($db, $sqlupdate2);
               //no error and everthing is fine
    } else {
        // Something happen and could not update request, will email the sql to admin
        $headers = "From: CDLC eForm <dontreply@CDCL.org>\r\n" ;
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
        $messagereq = "UPDATE ".$cdlcSTAT." IlliadStatus = ".$illstatus.", IlliadTransID = ".$illiadtxnub." WHERE index = ".$illnumb." ";
        $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
        mail("spalding@senylrc.org", "sql update Failure", $messagereq, $headers, "-f donotreply@cdlc.org");
    }
}//end while loop
