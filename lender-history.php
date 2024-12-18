<?php
###lender-history.php###

require '/var/www/cdlc_script/cdlc_function.php';

if (isset($_GET['loc'])) {
    $loc = $field_loc_location_code;
    $filter_yes = "yes";
    $filter_no = "yes";
    $filter_noans = "yes";
    $filter_expire = "";
    $filter_cancel = "";
    $filter_recevied = "";
    $filter_return = "";
    $filter_checkin = "";
    $filter_renew = "";
    $filter_days = "365";
    $filter_destination = "";
    $filter_illnum = "";
} else {
    if (isset($_REQUEST['loc'])) {
        $loc = $field_loc_location_code;
        if (isset($_REQUEST['filter_illnum'])) {
            $filter_illnum = $_REQUEST['filter_illnum'];
        }
        if ($filter_illnum != "") { #resets the other options for the best possible ILL search
            $filter_yes = "yes";
            $filter_no = "yes";
            $filter_noans = "yes";
            $filter_expire = "yes";
            $filter_cancel = "yes";
            $filter_recevied = "yes";
            $filter_return = "yes";
            $filter_checkin = "yes";
            $filter_renew = "yes";
            $filter_days = "all";
            $filter_destination = "";
        } else {
            $loc = $field_loc_location_code;

            $filter_yes = (isset($_REQUEST['filter_yes']) ? $_REQUEST['filter_yes'] : "");
            $filter_no = (isset($_REQUEST['filter_no']) ? $_REQUEST['filter_no'] : "");
            $filter_noans = (isset($_REQUEST['filter_noans']) ? $_REQUEST['filter_noans'] : "");
            $filter_expire = (isset($_REQUEST['filter_expire']) ? $_REQUEST['filter_expire'] : "");
            $filter_cancel = (isset($_REQUEST['filter_cancel']) ? $_REQUEST['filter_cancel'] : "");
            $filter_recevied = (isset($_REQUEST['filter_recevied']) ? $_REQUEST['filter_recevied'] : "");
            $filter_return = (isset($_REQUEST['filter_return']) ? $_REQUEST['filter_return'] : "");
            $filter_checkin = (isset($_REQUEST['filter_checkin']) ? $_REQUEST['filter_checkin'] : "");
            $filter_renew = (isset($_REQUEST['filter_renew']) ? $_REQUEST['filter_renew'] : "");
            $filter_days = (isset($_REQUEST['filter_days']) ? $_REQUEST['filter_days'] : "");
            $filter_destination = (isset($_REQUEST['filter_destination']) ? $_REQUEST['filter_destination'] : "");
            $filter_illnum = (isset($_REQUEST['filter_illnum']) ? $_REQUEST['filter_illnum'] : "");
        }
    } else {
        $loc = $field_loc_location_code;
        $filter_yes = "yes";
        $filter_no = "yes";
        $filter_noans = "yes";
        $filter_expire = "yes";
        $filter_cancel = "yes";
        $filter_recevied = "";
        $filter_return = "";
        $filter_checkin = "";
        $filter_renew = "";
        $filter_days = "365";
        $filter_destination = "";
        $filter_illnum = "";
    }
}

#Filter options
echo "<form action='lender-history' method='post'>";
echo "<input type='hidden' name='loc' value= '$loc'>";
echo "<h3>Limit Results</h3>";
echo "<p><b>By Fill Status:</b><br>";
echo "<input type='checkbox' name='filter_yes' value='yes' " . checked($filter_yes) . ">Yes  ";
echo "<input type='checkbox' name='filter_no' value='yes' " . checked($filter_no) . ">No  ";
echo "<input type='checkbox' name='filter_noans' value='yes' " . checked($filter_noans) . ">No Answer  ";
echo "<input type='checkbox' name='filter_expire' value='yes' " . checked($filter_expire) . ">Expired  ";
echo "<input type='checkbox' name='filter_cancel' value='yes' " . checked($filter_cancel) . ">Canceled  ";
echo "<input type='checkbox' name='filter_recevied' value='yes' " . checked($filter_recevied) . ">Received ";
echo "<input type='checkbox' name='filter_return' value='yes' " . checked($filter_return) . ">Return  ";
echo "<input type='checkbox' name='filter_checkin' value='yes' " . checked($filter_checkin) . ">Check In  ";
echo "<input type='checkbox' name='filter_renew' value='yes' " . checked($filter_renew) . ">Renew Pending<br><br> ";
echo "<b>Time Frame  </b> ";
echo "<select name='filter_days'>";
echo "<option value='365' " . selected("365", $filter_days) . ">365 days</option>";
echo "<option value='90' " . selected("90", $filter_days) . ">90 days</option>";
echo "<option value='30' " . selected("30", $filter_days) . ">30 days</option>";
echo "<option value='60' " . selected("60", $filter_days) . ">60 days</option>";
echo "<option value='all' " . selected("all", $filter_days) . ">all days</option>";
echo "</select> ";
echo "<b>ILL #  </b><input name='filter_illnum' type='text' value='$filter_illnum'>  ";
echo "<b>Destination  </b> <input name='filter_destination' type='text' value='$filter_destination'><br><br>";

echo "<button><a href='lender-history?clear=yes'>Reset Filters</a></button>  <b>OR</b>  ";
echo "<input type=Submit value='Update Results'>";
echo "</p>";
echo "</form>";

#Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);

#Sanitize data
$loc = mysqli_real_escape_string($db, $loc);

$SQLBASE = "SELECT *, DATE_FORMAT(`Timestamp`, '%Y/%m/%d') FROM `$cdlcSTAT` WHERE `Destination` = '$loc'";
$SQLEND = " ORDER BY `Timestamp`  DESC";

if ($filter_days == "all") {
    $SQL_DAYS = "";
} else {
    $SQL_DAYS = " AND (DATE(`Timestamp`) BETWEEN NOW() - INTERVAL " . $filter_days . " DAY AND NOW() )";
}

if (strlen($filter_illnum) > 2) {
    $SQLILL = " AND `illNUB` LIKE '%" . $filter_illnum . "%'";
} else {
    $SQLILL = "";
}

if (strlen($filter_destination) > 2) {
    $SQL_Dest_Search = "SELECT `loc` FROM `$cdlcLIB`  where `Name` like '%$filter_destination%'";
    #for testing
    //echo $SQL_Dest_Search."<br>";
    $PossibleDests = mysqli_query($db, $SQL_Dest_Search);
    while ($rowdest = mysqli_fetch_assoc($PossibleDests)) {
        $destloc = $rowdest["loc"];
        if (strlen($SQL_DESTINATION) > 2) {
            $SQL_DESTINATION = $SQL_DESTINATION . " OR `Requester LOC` = '$destloc'";
        } else {
            $SQL_DESTINATION = " AND (`Requester LOC` = '$destloc'";
        }
    }
    $SQL_DESTINATION = $SQL_DESTINATION . ")";
} else {
    $SQL_DESTINATION = "";
}

$SQLMIDDLE = '';
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
        $SQLMIDDLE = $SQLMIDDLE . "OR `checkinAccount` IS NOT NULL ";
    } else {
        $SQLMIDDLE = "`checkinAccount` IS NOT NULL ";
    }
}
if ($filter_recevied == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `receiveAccount` IS NOT NULL AND `returnAccount` IS NULL ";
    } else {
        $SQLMIDDLE = "`receiveAccount` IS NOT NULL AND `returnAccount` IS NULL ";
    }
}
if ($filter_return == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL ";
    } else {
        $SQLMIDDLE = " `returnAccount` IS NOT NULL AND `checkinAccount` IS NULL ";
    }
}
if ($filter_renew == "yes") {
    if (strlen($SQLMIDDLE) > 2) {
        $SQLMIDDLE = $SQLMIDDLE . "OR `renewAnswer` >1";
    } else {
        $SQLMIDDLE = " OR `renewAnswer` >1";
    }
}
$GETLISTSQL = $SQLBASE . $SQL_DESTINATION . $SQL_DAYS . $SQLILL . " AND (" . $SQLMIDDLE . ")" . $SQLEND;
//echo $GETLISTSQL; #Diagnostic... displays sql string
$GETLIST = mysqli_query($db, $GETLISTSQL);
$GETLISTCOUNTwhole = mysqli_num_rows($GETLIST);
#This is the form to process bulk actions
?>
<hr>
<h4>Perform Bulk Action</h4>
<form action=bulkaction method='post'>
    <select name="bulkaction" id="bulkaction">
        <option value="5">Request Not Filled</option>
        <option value="6">Check Item Back In</option>
    </select>
    <input type="submit" name="Submit Bulk Action" value="Submit" onclick="return confirm('Confirm, you want to continue with bulk update.');">
    <br><br>
    <?php
    echo "$GETLISTCOUNTwhole results<bR>";

    echo "<table><TR><TH>Bulk Action</TH><TH width='5%'>ILL #</TH><TH width='25%'>Title / Author</TH><TH>Type</TH><TH>Need By</TH><TH>Borrower / Contact</TH><TH>Due Date / Shipping</TH><TH>Timestamp / Status</TH><TH>ILLiad ID</TH><TH>Action</TH></TR>";
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
        $reqemail = $row["requesterEMAIL"];
        $timestamp = $row["Timestamp"];
        $shipmethod = $row["shipMethod"];
        $receiveAccount = $row['receiveAccount'];
        $returnAccount = $row['returnAccount'];
        $returnnote = $row['returnNote'];
        $returnmethod = $row['returnMethod'];
        $returndate = $row['returnDate'];
        $receivedate = $row['receiveDate'];
        $fillNoFillDate = $row['fillNofillDate'];

        $checkinAccount = $row['checkinAccount'];
        $checkindate = $row['checkinTimeStamp'];
        $duedate = $row["DueDate"];
        $illiadnumb = $row["IlliadTransID"];
        $renewNote = $row["renewNote"];
        $renewNoteLender = $row["renewNoteLender"];
        $renewAccountRequester = $row["renewAccountRequester"];
        $renewAnswer = $row["renewAnswer"];
        $now = date("Y-m-d");


        $fill = $row["Fill"];
        $statustxt = itemstatus($fill, $receiveAccount, $returnAccount, $returndate, $receivedate, $checkinAccount, $checkindate, $fillNoFillDate);
        $shiptxt = shipmtotxt($shipmethod);
        $returnmethodtxt = shipmtotxt($returnmethod);

        $dest = trim($dest);
        #Get the Destination Name
        if (strlen($dest) > 2) {
            $GETLISTSQLDEST = "SELECT`Name`,`ill_email` FROM `$cdlcLIB` where loc like '$dest'  limit 1";
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
        $timestamp =     date("Y-m-d", strtotime($timestamp));
        $daysdiff = round(abs(strtotime($now) - strtotime($timestamp)) / 86400);

        echo "<TR class='$rowclass'><td><input type='checkbox' name='check_list[]'' value=$illNUB></td><TD>$illNUB</TD><TD>$title</br><i>$author</i></TD><TD>$itype</TD><TD>$needby</TD><TD>$reqp</br><a href='mailto:$reqemail?Subject=NOTE Request ILL# $illNUB' target='_blank'>$reql</a></TD><TD>$duedate<br>$shiptxt</TD><TD>$timestamp<br>$statustxt</TD><TD>$illiadnumb</TD>";
        if (($fill == 3) || (strlen($receiveAccount) < 1) && ($daysdiff < '30') && ($fill != 0) && ($fill != 6)) {
            #Only show cancel button if request has not been answered and not received.
            echo "<TD><a href='/respond?num=$illNUB&a=1'>Yes, Will Fill</a><hr><a href='/respond?num=$illNUB&a=0'>No, Can't Fill</a></TD></TR> ";
        } elseif ((strlen($returnAccount) < 2) && ($fill == 1) && ($renewAnswer == 0) && (strlen($renewAccountRequester) > 1) && (strlen($checkinAccount) < 2)) {
            #Only show renew if someon requested a renwall
            echo "<td><a href ='/renew?num=" . $illNUB . "&a=1'>Approve Renewal</a><br><br><a href ='/renew?num=" . $illNUB . "&a=2'>Deny Renewal</a><br> ";
            echo "</td></tr>";
        } elseif (($daysdiff > '14') && (strlen($checkinAccount) < 2) && ($fill != 4) && ($fill != 6)) {
            echo "<td><a  href ='/status?num=$illNUB&a=3'>Check Item Back In</a> ";
            echo "</td></tr>";
        } elseif ((strlen($returnAccount) < 2) && (strlen($renewAccountRequester) < 1) && (strlen($receiveAccount) > 1) && (strlen($checkinAccount) < 2)) {
            #Give the option for lender to change due date as long as it has been recived and not returned or renewed
            echo "<td><a href ='/renew?num=" . $illNUB . "&a=4'>Edit Due Date</a>";
            $timestamp =     date("Y-m-d", strtotime($timestamp));
            $daysdiff = round(abs(strtotime($now) - strtotime($timestamp)) / 86400);
            if ($daysdiff > '14') {
                echo "<br><br><a href ='/status?num=$illNUB&a=3'>Check Item Back In</a> ";
            }
            echo "</td></tr>";
        } elseif ((strlen($checkinAccount) < 2) && (strlen($receiveAccount) > 1)) {
            #Give the option for lender to check the item back in
            echo "<td><a href ='/status?num=$illNUB&a=3'>Check Item Back In</a></td></tr> ";
        } else {
            echo "<td>&nbsp</td>";
        }

        if ((isset($reqnote) && strlen($reqnote) > 2) || (isset($lendnote) && strlen($lendnote) > 2)) {
            echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displaynotes</TD></TR>";
        }
        
        if ((isset($returnnote) && strlen($returnnote) > 2) || (isset($returnmethod) && strlen($returnmethod) > 2)) {
            echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$dispalyreturnnotes</TD></TR>";
        }
        
        if ((isset($renewNote) && strlen($renewNote) > 2) || (isset($renewNoteLender) && strlen($renewNoteLender) > 2)) {
            echo "<TR class='$rowclass'><TD></TD><TD></TD><TD colspan=8>$displayrenewnotes</TD></TR>";
        }
        


        $rowtype = $rowtype + 1;
    }
    echo "</table>";
    ?>

    </from>
    <?php //end for to process bulk action
    ?>
    <script type="text/javascript">
        var elems = document.getElementsByClassName('confirmation');
        var confirmIt = function(e) {
            if (!confirm('Are you sure?')) e.preventDefault();
        };
        for (var i = 0, l = elems.length; i < l; i++) {
            elems[i].addEventListener('click', confirmIt, false);
        }
    </script>