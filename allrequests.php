<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>

<script>
    $(document).ready(function() {
        $("#startdate").datepicker();
        $("#enddate").datepicker();
    });
</script>
<?php
// allrequests.php###
require '/var/www/cdlc_script/cdlc_function.php';


$firstpass = (isset($_REQUEST['firstpass']) ? "no" : "yes");
$filter_illnum = (isset($_REQUEST['filter_illnum']) ? $filter_illnum = $_REQUEST['filter_illnum'] : "");

if ($firstpass == "no") {
    // Setting options to user's chosen
    if ($filter_illnum != "") { // If looking for ILL num then set the other options
        if (isset($_REQUEST['filter_startdate'])) {
            $filter_startdate = $_REQUEST['filter_startdate'];
        }
        if (isset($_REQUEST['filter_enddate'])) {
            $filter_enddate = $_REQUEST['filter_enddate'];
        }
        if (isset($_REQUEST['filter_numresults'])) {
            $filter_numresults = $_REQUEST['filter_numresults'];
        }
        $filter_lender = "";
        $filter_borrower = "";
        $filter_title = "";
        $filter_yes = "yes";
        $filter_no = "yes";
        $filter_noans = "yes";
        $filter_expire = "yes";
        $filter_cancel = "yes";
        $filter_recevied = "yes";
        $filter_return = "yes";
        $filter_checkin = "yes";
        $filter_destination = "";
        $filter_system = "";
        $filter_offset = 0;
    } else {
        if (isset($_REQUEST['filter_yes'])) {
            $filter_yes = $_REQUEST['filter_yes'];
        }
        if (isset($_REQUEST['filter_no'])) {
            $filter_no = $_REQUEST['filter_no'];
        }
        if (isset($_REQUEST['filter_noans'])) {
            $filter_noans = $_REQUEST['filter_noans'];
        }
        if (isset($_REQUEST['filter_expire'])) {
            $filter_expire = $_REQUEST['filter_expire'];
        }
        if (isset($_REQUEST['filter_cancel'])) {
            $filter_cancel = $_REQUEST['filter_cancel'];
        }
        if (isset($_REQUEST['filter_recevied'])) {
            $filter_recevied = $_REQUEST['filter_recevied'];
        }
        if (isset($_REQUEST['filter_return'])) {
            $filter_return = $_REQUEST['filter_return'];
        }
        if (isset($_REQUEST['filter_checkin'])) {
            $filter_checkin = $_REQUEST['filter_checkin'];
        }

        if (isset($_REQUEST['filter_system'])) {
            $filter_system = $_REQUEST['filter_system'];
        }
        if (isset($_REQUEST['filter_lender'])) {
            $filter_lender = $_REQUEST['filter_lender'];
        }
        if (isset($_REQUEST['filter_borrower'])) {
            $filter_borrower = $_REQUEST['filter_borrower'];
        }
        if (isset($_REQUEST['filter_title'])) {
            $filter_title = $_REQUEST['filter_title'];
        }
        if (isset($_REQUEST['filter_startdate'])) {
            $filter_startdate = $_REQUEST['filter_startdate'];
        }
        if (isset($_REQUEST['filter_enddate'])) {
            $filter_enddate = $_REQUEST['filter_enddate'];
        }
        if (isset($_REQUEST['filter_numresults'])) {
            $filter_numresults = $_REQUEST['filter_numresults'];
        }
        if (isset($_REQUEST['filter_offset'])) {
            $filter_offset = $_REQUEST['filter_offset'];
        }
    }
} else {
    // Setting options to default values
    $firstpass = "no";
    $filter_illnum = "";
    $filter_startdate = "08/01/2022";
    $filter_enddate = date("m/d/Y");
    $filter_lender = "";
    $filter_borrower = "";
    $filter_title = "";
    $filter_numresults = 25;
    $filter_yes = "yes";
    $filter_no = "yes";
    $filter_noans = "yes";
    $filter_expire = "yes";
    $filter_cancel = "yes";

    $filter_recevied = "yes";
    $filter_return = "yes";
    $filter_checkin = "yes";
    $filter_system = "";
    $filter_offset = 0;
}

// Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

// Sanitize data
$loc = mysqli_real_escape_string($db, $loc);

$SQLBASE = "SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$cdlcSTAT` WHERE ";
$SQLEND = " ORDER BY `Timestamp` DESC ";

if (strlen($filter_illnum) > 2) {
    $SQLILL = " AND `illNUB` = '" . $filter_illnum . "'";
}

if (strlen($filter_lender) > 2) {
    $SQL_Search = "SELECT `loc` FROM `$cdlcLIB` where `Name` like '%$filter_lender%'";
    $Possibles = mysqli_query($db, $SQL_Search);
    // Return the number of rows in result set
    $rowcount = mysqli_num_rows($Possibles);
    if ($rowcount > 0) {
        while ($rowposs = mysqli_fetch_assoc($Possibles)) {
            $possloc = $rowposs["loc"];
            if (strlen($SQL_LENDER) > 2) {
                $SQL_LENDER = $SQL_LENDER . " OR `destination` = '$possloc'";
            } else {
                $SQL_LENDER = " AND (`destination` = '$possloc'";
            }
        }
        $SQL_LENDER = $SQL_LENDER . ")";
    }
}

if (strlen($filter_borrower) > 2) {
    $SQL_Search = "SELECT `loc` FROM `$cdlcLIB` where `Name` like '%$filter_borrower%'";
    $Possibles = mysqli_query($db, $SQL_Search);
    // Return the number of rows in result set
    $rowcount = mysqli_num_rows($Possibles);
    if ($rowcount > 0) {
        while ($rowposs = mysqli_fetch_assoc($Possibles)) {
            $possloc = $rowposs["loc"];
            if (strlen($SQL_BORROWER) > 2) {
                $SQL_BORROWER = $SQL_BORROWER . " OR `Requester LOC` = '$possloc'";
            } else {
                $SQL_BORROWER = " AND (`Requester LOC` = '$possloc'";
            }
        }
        $SQL_BORROWER = $SQL_BORROWER . ")";
    }
}

if (strlen($filter_title) > 2) {
    $SQLTITLE = " AND `Title` like '%" . $filter_title . "%'";
}

// Meddling with dates
$sql_startdate = convertDate($filter_startdate);
$sql_enddate = convertDate($filter_enddate);
$SQLDATES = "`Timestamp` >= '" . $sql_startdate . " 00:00:00' AND `Timestamp` <= '" . $sql_enddate . " 23:59:59' ";

// Adding the system
if ($filter_system != "") {
    $SQLSYSTEM = " AND (`ReqSystem` = '" . $filter_system . "' OR `DestSystem` = '" . $filter_system . "')";
}

$SQLMIDDLE = ''; // This builds the display options for the SQL
if ($filter_yes == "yes") {
    $SQLMIDDLE = "`fill`= 1 ";
}
if ($filter_no == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 0 ";
    } else {
        $SQLMIDDLE = "`fill`= 0 ";
    }
}
if ($filter_noans == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 3 ";
    } else {
        $SQLMIDDLE = "`fill`= 3 ";
    }
}
if ($filter_expire == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 4 ";
    } else {
        $SQLMIDDLE = "`fill`= 4 ";
    }
}
if ($filter_cancel == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `fill`= 6 ";
    } else {
        $SQLMIDDLE = "`fill`= 6 ";
    }
}
if ($filter_checkin == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . " OR `checkinAccount` IS NOT NULL";
    } else {
        $SQLMIDDLE = "`checkinAccount` IS NOT NULL";
    }
}
if ($filter_recevied == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . " OR `receiveAccount` IS NOT NULL AND `returnAccount` IS NULL";
    } else {
        $SQLMIDDLE = "`receiveAccount` IS NOT NULL AND `returnAccount` IS NULL";
    }
}
if ($filter_return == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . " OR `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL";
    } else {
        $SQLMIDDLE = " `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL";
    }
}
if ($filter_numresults != "all") {
    $sqllimiter = $filter_numresults * $filter_offset;
    $SQLLIMIT = " LIMIT " . $sqllimiter . ", " . $filter_numresults;
} else {
    $SQLLIMIT = "";
}

$GETFULLSQL = $SQLBASE . $SQLDATES . $SQLTITLE . $SQL_LENDER . $SQL_BORROWER . $SQLILL . $SQLSYSTEM . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
$GETLISTSQL = $SQLBASE . $SQLDATES . $SQLTITLE . $SQL_LENDER . $SQL_BORROWER . $SQLILL . $SQLSYSTEM . " AND (" . $SQLMIDDLE . ")" . $SQLEND . $SQLLIMIT;
//for testing
//echo $GETLISTSQL . "</br>";

$GETLIST = mysqli_query($db, $GETLISTSQL);
$GETCOUNT = mysqli_query($db, $GETFULLSQL);
$GETLISTCOUNTwhole = mysqli_num_rows($GETCOUNT);

// echo "<p>Diagnostic Block";
// echo "</br>First Pass:" . $firstpass;
// echo "</br>Yes: " . $filter_yes;
// echo "</br>No: " . $filter_no;
// echo "</br>No Ans: " . $filter_noans;
// echo "</br>Exp: " . $filter_expire;
// echo "</br>Cancel: " . $filter_cancel;
// echo "</br>Recived: " . $filter_recevied;
// echo "</br>Return: " . $filter_return;
// echo "</br>Checkin: " . $filter_checkin;
// echo "</br>Start Date: " . $filter_startdate;
// echo "</br>End Date: " . $filter_enddate;
// echo "</br>Library System: " . $filter_system;
// echo "</br>Results PP: " . $filter_numresults;
// echo "</br>Lender: " . $filter_lender;
// echo "</br>Borrower: " . $filter_borrower;
// echo "</br>Title: " . $filter_title;
// echo "</br>ILL Num: " . $filter_illnum;
// echo "</br>Offset: " . $filter_offset;
// echo "</p>";

// Filter options
echo "<form action='" . $_SERVER['REDIRECT_URL'] . "' method='post'>";
echo "<input type='hidden' name='firstpass' value='no'>";
echo "<input type='hidden' name='filter_offset' value='" . $filter_offset . "'>";
echo "<p>Display Fill Status: ";
echo "<input type='checkbox' name='filter_yes' value='yes' " . checked($filter_yes) . ">Yes  ";
echo "<input type='checkbox' name='filter_no' value='yes' " . checked($filter_no) . ">No  ";
echo "<input type='checkbox' name='filter_noans' value='yes' " . checked($filter_noans) . ">No Answer  ";
echo "<input type='checkbox' name='filter_expire' value='yes' " . checked($filter_expire) . ">Expired  ";
echo "<input type='checkbox' name='filter_cancel' value='yes' " . checked($filter_cancel) . ">Canceled  ";
echo "<input type='checkbox' name='filter_recevied' value='yes' " . checked($filter_recevied) . ">Received  ";
echo "<input type='checkbox' name='filter_return' value='yes' " . checked($filter_return) . ">Return  ";
echo "<input type='checkbox' name='filter_checkin' value='yes' " . checked($filter_checkin) . ">Check In  ";
echo "<br>";
echo "Start Date <input id='startdate' name='filter_startdate' value='$filter_startdate'> ";
echo "End Date <input id='enddate' name='filter_enddate' value='$filter_enddate'></br>";
echo "Library System <select name='filter_system'></br>";
echo "<option " . selected('', $filter_system) . " value=''>All</option>";
echo "<option " . selected('CDLC', $filter_system) . " value = 'CDLC'>Capital District Library Council</option>";
echo "<option " . selected('CRB', $filter_system) . " value = 'CRB'>Capital Region BOCES</option>";
echo "<option " . selected('HFM', $filter_system) . " value = 'HFM'>Hamilton-Fulton-Montgomery BOCES</option>";
echo "<option " . selected('MVLS', $filter_system) . " value = 'MVLS'>Mohawk Valley Library System</option>";
echo "<option " . selected('Q3S', $filter_system) . " value = 'Q3S'>Questar III SLS</option>";
echo "<option " . selected('SALS', $filter_system) . "value = 'SALS'>Southern Adirondack Library System</option>";
echo "<option " . selected('UHLS', $filter_system) . " value = 'UHLS'>Upper Hudson Library System</option>";
echo "<option " . selected('WSWHE', $filter_system) . " value = 'WSWHE'>WSWHE BOCES</option>";
echo "</select></br>";
echo "Lender <input name='filter_lender' type='text' value='$filter_lender'> ";
echo "Borrower <input name='filter_borrower' type='text' value='$filter_borrower'></br>";
echo "Title <input name='filter_title' type='text' value='$filter_title'> </br>";
echo "ILL # <input name='filter_illnum' type='text' value='$filter_illnum'></br>";
echo "</br>" . $GETLISTCOUNTwhole . " results with <select name='filter_numresults'></br>";
echo "<option " . selected("25", $filter_numresults) . " value = '25'>25</option>";
echo "<option " . selected("50", $filter_numresults) . " value = '50'>50</option>";
echo "<option " . selected("100", $filter_numresults) . " value = '100'>100</option>";
echo "<option " . selected("all", $filter_numresults) . " value = 'all'>All</option>";
echo "</select> results per page. ";
if (is_numeric($filter_numresults)) {
    $resultpages = ceil($GETLISTCOUNTwhole / $filter_numresults);
}
$display_page = $filter_offset + 1;
if ($filter_numresults != "all") {
    echo "Currently on page <select name='filter_offset'>";
    for ($x = 1; $x <= $resultpages; $x++) {
        $localoffset = $x - 1;
        echo "<option " . selected($localoffset, $filter_offset) . " value = '" . $localoffset . "'>" . $x . "</option>";
    }
    echo "</select> of " . $resultpages . ".";
}
echo "</br><a href='allrequests'>clear</a>  ";
echo "<input type=Submit value=Update><br>";
echo "</p>";
echo "</form>";

echo "<table><TR><TH width='5%'>ILL #</TH><TH width='25%'>Title / Author</TH><TH>Type</TH><TH>Need By</TH><TH>Lender</TH><TH>Borrower</TH><TH>Due Date & Shipping & ILLiad #</TH><TH>Timestamp</TH><TH>Status</TH></TR>";
$rowtype = 1;
while ($row = mysqli_fetch_assoc($GETLIST)) {
    $illNUB = $row["illNUB"];
    $title = $row["Title"];
    $author = $row["Author"];
    $itype = $row["Itype"];
    $reqnote = $row["reqnote"];
    $lendnote = $row["responderNOTE"];
    $needby = $row["needbydate"];
    $dest = $row["Destination"];
    $reqp = $row["Requester person"];
    $reql = $row["Requester lib"];
    $destsys = $row["DestSystem"];
    $reqsys = $row["ReqSystem"];
    $reqemail = $row["requesterEMAIL"];
    $timestamp = $row["Timestamp"];
    $shipmethod = $row["shipMethod"];
    $receiveAccount = $row['receiveAccount'];
    $returnAccount = $row['returnAccount'];
    $returnnote = $row['returnNote'];
    $returnmethod = $row['returnMethod'];
    $returndate = $row['returnDate'];
    $fillNoFillDate = $row['fillNofillDate'];
    $receivedate = $row['receiveDate'];
    $checkinAccount = $row['checkinAccount'];
    $checkindate = $row['checkinTimeStamp'];
    $duedate = $row["DueDate"];
    $illiadnumb = $row["IlliadTransID"];
    $renewNote = $row["renewNote"];
    $renewNoteLender = $row["renewNoteLender"];
    $renewAccountRequester = $row["renewAccountRequester"];
    $fill = $row["Fill"];
    $statustxt = itemstatus($fill, $receiveAccount, $returnAccount, $returndate, $receivedate, $checkinAccount, $checkindate, $fillNoFillDate);
    $shiptxt = shipmtotxt($shipmethod);
    $returnmethodtxt = shipmtotxt($returnmethod);
    $dest = trim($dest);
    //for testing
    //echo $dest."<br>";
    // Get the Destination Name
    if (strlen($dest) > 0) {
        $GETLISTSQLDEST = "SELECT`Name`,`ill_email` FROM `$cdlcLIB` where loc like '$dest'  limit 1";
        //for testing
        //echo $GETLISTSQLDEST."<br>";
        $resultdest = mysqli_query($db, $GETLISTSQLDEST);
        while ($rowdest = mysqli_fetch_assoc($resultdest)) {
            $dest = $rowdest["Name"];
            $destemail = $rowdest["ill_email"];
        }
    } else {
        $dest = "Error No Library Selected";
    }
    if ($rowtype & 1) {
        $rowclass = "odd";
    } else {
        $rowclass = "even";
    }
    $displaynotes = build_notes($reqnote, $lendnote);
    $dispalyreturnnotes = build_return_notes($returnnote, $returnmethodtxt);
    $displayrenewnotes = build_renewnotes($renewNote, $renewNoteLender);
    echo "<TR class='$rowclass'><TD>$illNUB</TD><TD>$title</br><i>$author</i></TD><TD>$itype</TD><TD>$needby</TD><TD><a href='mailto:$destemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$dest</a></br><i>$destsys</i></TD><TD><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reqp</a></br>$reql</br><i>$reqsys</i></TD><TD>$duedate<br>$shiptxt<br>$illiadnumb</TD><TD>$timestamp</TD><TD>$statustxt</TD></TR> ";
    if ((strlen($reqnote) > 2) || (strlen($lendnote) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displaynotes</TD></TR>";
    }
    if ((strlen($returnnote) > 2) || (strlen($returnmethod) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$dispalyreturnnotes</TD></TR>";
    }
    if ((strlen($renewNote) > 2) || (strlen($renewNoteLender) > 2)) {
        echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displayrenewnotes</TD></TR>";
    }

    $rowtype = $rowtype + 1;
}
echo "</table>";
?>