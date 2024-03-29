<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

<script>
    $(document).ready(function() {
        $("#datepicker").datepicker();
    });
</script>
<?php
// renew.php###
require '/var/www/cdlc_script/cdlc_function.php';


$reqnumb = $_REQUEST["num"];
$renewNote = $_REQUEST["renewNote"];
$duedate = $_REQUEST["duedate"];
$renewNoteLender = $_REQUEST["renewNoteLender"];

if (isset($_REQUEST['a'])) {
    $renanswer = $_REQUEST['a'];
} elseif (isset($_POST['a'])) {
    $renanswer = $_POST['a'];
} else {
    $renanswer = '';
}

$timestamp = date("Y-m-d H:i:s");
$todaydate = date("Y-m-d");

// Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

// Escape values for security
$reqnumb = mysqli_real_escape_string($db, $reqnumb);
$renewNote = mysqli_real_escape_string($db, $renewNote);
$renewNoteLender = mysqli_real_escape_string($db, $renewNoteLender);
$renanswer = mysqli_real_escape_string($db, $renanswer);
$wholename = mysqli_real_escape_string($db, $wholename);

// Answers
// 1  renew is approved
// 2  renew is not approved
// 3 request a renew
// 4 is to let the lender edit the due date
if ($renanswer == '1') {
    // This is for approving the renew
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "UPDATE `$cdlcSTAT` SET `renewNoteLender` ='$renewNoteLender', `renewAnswer` ='1', `renewTimeStamp` = '$timestamp', `renewAccountLender` = '" . $wholename . "', `DueDate` = '$duedate'  WHERE `illNUB` = '$reqnumb'";
        if (mysqli_query($db, $sql)) {
            echo "The renew request has been approved, <a href='/lender-history'>click here to go back to lender history</a>";

            // Get the borrower email
            $GETREQEMAIL = "SELECT title,requesterEMAIL FROM `$cdlcSTAT` WHERE `illNUB` = '" . $reqnumb . "'";
            $result = mysqli_query($db, $GETREQEMAIL);
            $value = mysqli_fetch_object($result);
            $reqemail = $value->requesterEMAIL;
            $title = $value->title;
            // Message for the destination library
            $messagedest = "Your renewal request for ILL# " . $reqnumb . " for " . $title . " has been approved with a due date of " . $duedate . "  <br><br>Title:" . $title . "<br>";
            if (strlen($renewNoteLender) > 1) {
                $messagedest .= "Lender Note:" . $renewNoteLender . "            <br>";
            }
            // Set email subject for renewal
            $subject = "Linx Renewal Approved: for ILL# $reqnumb";
            $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
            // SEND EMAIL to Detestation Library
            // Check if $destemailarray is a string and convert it to an array if needed
            if (!is_array($destemailarray)) {
                // Assuming $destemailarray is a comma-separated string, convert it to an array
                $destemailarray = explode(',', $destemailarray);
            }
            $email_to = implode(',', $destemailarray);
            $headers = "From: CDLC Linx <donotreply@cdlc.org>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at CDLC for development
            //$reqemail="mwakeman@cdlc.org";
            mail($reqemail, $subject, $messagedest, $headers, "-f donotreply@cdlc.org");
        } else {
            echo "Was not able to make updates, please contact Southeastern of this error";
        }
    } else {
        echo "<form action=" . $_SERVER['REDIRECT_URL'] . " method='post'>";
        echo "<input type='hidden' name='a' value= '1'>";
        echo "<input type='hidden' name='num' value= '" . $reqnumb . "'>";
        echo "New Due Date: <input id='datepicker' name='duedate'/><br>";
        echo "Renew notes:<br> <textarea name='renewNoteLender' rows='10' cols='30' maxlength='255'></textarea><br><br> ";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    }
} elseif ($renanswer == '2') {
    // this is for rejecting the renew
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "UPDATE `$cdlcSTAT` SET `renewNoteLender` ='$renewNoteLender', `renewAnswer` ='2', `renewTimeStamp` = '$timestamp', `renewAccountLender` = '" . $wholename . "'  WHERE `illNUB` = '$reqnumb'";
        if (mysqli_query($db, $sql)) {
            echo "The renew request has been rejected, <a href='/lender-history'>click here to go back to lender history</a>";
            // Get the borrower's email
            $GETREQEMAIL = "SELECT title,requesterEMAIL FROM `$cdlcSTAT` WHERE `illNUB` = '" . $reqnumb . "'";
            $result = mysqli_query($db, $GETREQEMAIL);
            $value = mysqli_fetch_object($result);
            $reqemail = $value->requesterEMAIL;
            $title = $value->title;
            // Message for the destination library
            $messagedest = "Your renewal request for ILL# " . $reqnumb . " for " . $title . " has been denied; please return the book to the lender by the original due date. <br><br>";
            if (strlen($renewNoteLender) > 1) {
                $messagedest .= "Lender Note:" . $renewNoteLender . "            <br>";
            }
            // Set email subject for renewal
            $subject = "Linx Renewal Denied: for ILL# $reqnumb";
            $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
            // SEND EMAIL to Detestation Library
            // Check if $destemailarray is a string and convert it to an array if needed
            if (!is_array($destemailarray)) {
                // Assuming $destemailarray is a comma-separated string, convert it to an array
                $destemailarray = explode(',', $destemailarray);
            }
            $email_to = implode(',', $destemailarray);
            $headers = "From: CDLC Linx <donotreply@cdlc.org>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at CDLC for development
            //$reqemail="mwakeman@cdlc.org";
            mail($reqemail, $subject, $messagedest, $headers, "-f donotreply@cdlc.org");
        } else {
            echo "Was not able to make updates, please contact Southeastern of this error";
        }
    } else {
        echo "<form action=" . $_SERVER['REDIRECT_URL'] . " method='post'>";
        echo "<input type='hidden' name='a' value= '2'>";
        echo "<input type='hidden' name='num' value= '" . $reqnumb . "'>";

        echo "Comments about request:<br> <textarea name='renewNoteLender' rows='10' cols='30' maxlength='255'></textarea><br><br> ";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    }
} elseif ($renanswer == '4') {
    // This will allow the lender to edit the due date
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "UPDATE `$cdlcSTAT` SET `renewTimeStamp` = '$timestamp', `renewAccountLender` = '" . $wholename . "', `DueDate` = '$duedate'  WHERE `illNUB` = '$reqnumb'";
        if (mysqli_query($db, $sql)) {
            echo "The due date has been updated, <a href='/lender-history'>click here to go back to lender history</a>";
            // Get the borrower email
            $GETREQEMAIL = "SELECT Title,requesterEMAIL FROM `$cdlcSTAT` WHERE `illNUB` = '" . $reqnumb . "'";
            $result = mysqli_query($db, $GETREQEMAIL);
            $value = mysqli_fetch_object($result);
            $reqemail = $value->requesterEMAIL;
            $title = $value->Title;
            // Message for the destination library
            $messagedest = "The lender has changed the due date for ILL# " . $reqnumb . "<br> Title: " . $title . " <br>New Due Date: " . $duedate . ", please return the book to the lender by that date. <br><br>";
            // Set email subject for renewal
            $subject = "Linx Due Date Modify: for ILL# $reqnumb";
            $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
            // SEND EMAIL to Detestation Library
            // Check if $destemailarray is a string and convert it to an array if needed
            if (!is_array($destemailarray)) {
                // Assuming $destemailarray is a comma-separated string, convert it to an array
                $destemailarray = explode(',', $destemailarray);
            }
            $email_to = implode(',', $destemailarray);
            $headers = "From: CDLC Linx <donotreply@cdlc.org>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at CDLC for development
            //$reqemail="mwakeman@cdlc.org";
            mail($reqemail, $subject, $messagedest, $headers, "-f donotreply@cdlc.org");
        } else {
            echo "Was not able to receive the item, please contact Southeastern of this error";
        }
    } else {
        // Get the current due date
        $sqlduedate = "SELECT DueDate FROM `$cdlcSTAT` WHERE `illNUB` = '" . $reqnumb . "' LIMIT 1 ";

        $result = mysqli_query($db, $sqlduedate);
        $value = mysqli_fetch_object($result);
        echo "<h2>Edit Due Date For ILL #: " . $reqnumb . " <br>Current due date: " . $value->DueDate . "</h2>";
        echo "<form action=" . $_SERVER['REDIRECT_URL'] . " method='post'>";
        echo "<input type='hidden' name='a' value= '4'>";
        echo "<input type='hidden' name='num' value= '" . $reqnumb . "'>";
        echo "Due Date: <input id='datepicker' name='duedate'/><br>";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    }
} elseif ($renanswer == '3') {
    // This is for the borrower to request a renewed
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $sql = "UPDATE `$cdlcSTAT` SET `renewTimeStamp` = '" . $timestamp . "', `renewAccountRequester` = '" . $wholename . "', `renewNote` = '$renewNote'  WHERE `illNUB` = '$reqnumb'";
        if (mysqli_query($db, $sql)) {
            echo "Renew request for ILL " . $reqnumb . " has been sent, <br><a href='/requesthistory'>click here to go back to request history</a>";

            // Get the Lending ID for the request
            $sqlrenew = "SELECT RequesterEMAIL,Title,Destination FROM `$cdlcSTAT` WHERE `illNUB` = '" . $reqnumb . "' LIMIT 1 ";
            $result = mysqli_query($db, $sqlrenew);
            $value = mysqli_fetch_object($result);
            $lenderid = $value->Destination;
            $title = $value->Title;
            $reqemail = $value->RequesterEMAIL;
            // Get the Destination Name
            $GETLISTSQLDEST = "SELECT `Name`, `ill_email` FROM `$cdlcLIB` where loc = '$lenderid'  limit 1";
            $resultdest = mysqli_query($db, $GETLISTSQLDEST);
            while ($rowdest = mysqli_fetch_assoc($resultdest)) {
                $destlib = $rowdest["Name"];
                $destemail = $rowdest["ill_email"];
            }
            // In case the ILL email for the destination library is more than one, break it down to a comma for PHP mail
            $destemailarray = explode(';', $destemail);
            $destemail_to = implode(',', $destemailarray);

            // Message for the destination library
            $messagedest = $field_your_institution . " has requested a renewal for ILL# " . $reqnumb . "<br>Title: " . $title . "<br><br>
            <br>
            How do you wish to answer the renewal?  <a href='http://linx.cdlc.org/renew?num=$reqnumb&a=1' >Approved</a> &nbsp;&nbsp;&nbsp;&nbsp;<a href='http://linx.cdlc.org/renew?num=$reqnumb&a=2' >Deny</a>
            <Br>
            <hr style='width:200px;text-align:left;margin-left:0'>
            <br>  This is an automated message from the Linx. Responses to this email will be sent back to Capital District Library Council staff. If you would like to contact the other library in this ILL transaction, email " . $reqemail . ".";
            // Set email subject for renewal
            $subject = "Linx Renew Request: from " . $field_your_institution . " ILL# $reqnumb";
            $subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
            // SEND EMAIL to Detestation Library
            $email_to = implode(',', $destemailarray);
            $headers = "From: CDLC Linx <donotreply@cdlc.org>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $messagedest = preg_replace('/(?<!\r)\n/', "\r\n", $messagedest);
            $headers = preg_replace('/(?<!\r)\n/', "\r\n", $headers);
            //mail has been sent to meg at CDLC for development
            //$destemail_to="mwakeman@cdlc.org";
            mail($destemail_to, $subject, $messagedest, $headers, "-f donotreply@cdlc.org");
        } else {
            // email error function here
            echo "Was not able to renew item due to a technical issue, please let Southeastern know this error occurred";
        }
    } else {
        echo "<h2>Renew ILL: " . $reqnumb . "</h2>";
        echo "<form action=" . $_SERVER['REDIRECT_URL'] . " method='post'>";
        echo "<input type='hidden' name='a' value= '3'>";
        echo "<input type='hidden' name='num' value= '" . $reqnumb . "'>";
        echo "Reason for Renew:<br> <textarea name='renewNote' rows='10' cols='30' maxlength='255'></textarea><br><br> ";
        echo "<input type='submit' value='Submit'>";
        echo "</form>";
    }
} else {
    echo "I am not sure what to do";
}
?>