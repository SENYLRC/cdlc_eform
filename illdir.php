<?php
###illdir_search.php####
require '/var/www/cdlc_script/cdlc_function.php';


#check if an action has been requested
if (isset($_REQUEST['action'])) {
    #set the pageaction to what has been requested
    $pageaction = $_REQUEST['action'];
} else {
    $pageaction = '0';
}

if (isset($_REQUEST['libname'])) {
    $libname = $_REQUEST['libname'];
}else{
  $libname='';
}
if (isset($_REQUEST['system'])) {
    $system = $_REQUEST['system'];
}else{
  $system='';
}

#Connect to database
require '/var/www/cdlc_script/cdlc_db.inc';
$db = mysqli_connect($dbhost, $dbuser, $dbpass);
mysqli_select_db($db, $dbname);


if (($_SERVER['REQUEST_METHOD'] == 'POST')   || (isset($_GET{'page'}))) {
    #Display the searched results
    $libname = mysqli_real_escape_string($db, $libname);
    //don't think i need this line 9/16/2022
    //$libemail = mysqli_real_escape_string($db, $libemail);
    $GETLISTSQL="SELECT * FROM `$cdlcLIB` WHERE `Name` LIKE '%$libname%' and `system` LIKE '%$system%' and participant = '1' ORDER BY Name Asc";
    $retval = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNTwhole = mysqli_num_rows($retval);
    $rec_limit = 50;
    $rowpage = mysqli_fetch_array($retval, MYSQLI_NUM);
    $rec_count = $rowpage[0];
    //I don't think I need these two lines 9/16/2022
    //$GETLIST = mysqli_query($db, $GETLISTSQL1);
    //$GETLISTCOUNT = mysqli_num_rows($GETLIST);
    #echo " $GETLISTCOUNTwhole  results";
    if (isset($_GET{'page'})) {
        $page = $_GET['page'] + 1;
        $offset = $rec_limit * $page ;
    } else {
        $page = 0;
        $offset = 0;
    }
    $left_rec = $rec_count - ($page * $rec_limit);
    $GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
    #echo $GETLISTSQL;
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = mysqli_num_rows($GETLIST);
} else {
    $GETLISTSQL="SELECT * FROM `$cdlcLIB` where participant = '1' ORDER BY Name Asc";
    $retval = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNTwhole = mysqli_num_rows($retval);
    $rec_limit = 50;
    $rowpage = mysqli_fetch_array($retval, MYSQLI_NUM);
    $rec_count = $rowpage[0];

    if (isset($_GET{'page'})) {
        $page = $_GET['page'] + 1;
        $offset = $rec_limit * $page ;
    } else {
        $page = 0;
        $offset = 0;
    }
    $left_rec = $rec_count - ($page * $rec_limit);
    $GETLISTSQL="$GETLISTSQL LIMIT $offset, $rec_limit";
    #echo $GETLISTSQL;
    $GETLIST = mysqli_query($db, $GETLISTSQL);
    $GETLISTCOUNT = mysqli_num_rows($GETLIST);
}
?>
  <h3>Search the directory</h3>
  <form action="<?php echo "".$_SERVER['REDIRECT_URL']."?". $_SERVER['QUERY_STRING']."";?>" method="post">
  <b>Library Name:</b> <input type="text" SIZE=60 MAXLENGTH=255  name="libname"><br>
  <b>Library System:</b> <select name="system">
    <option value=""></option>
    <option value="CDLC">Capital District Library Council</option>
    <option value="CRB">Capital Region BOCES</option>
    <option value="HFM">Hamilton-Fulton-Montgomery BOCES</option>
    <option value="MVLS">Mohawk Valley Library System</option>
    <option value="Q3S">Questar III SLS</option>
    <option value="SALS">Southern Adirondack Library System</option>
    <option value="UHLS">Upper Hudson Library System</option>
    <option value="WSWHE">WSWHE BOCES</option>
  </select>
  <br>
  <input type="submit" value="Submit">
  </form>
  <?php
#List All Libraries
echo "$GETLISTCOUNTwhole results<bR>";
echo "<div class='illDirTable'>";
echo "<div class='illDirTableRow'>";
$count = 1;
$rowcount =1;
while ($row = mysqli_fetch_assoc($GETLIST)) {
    $libaddress2 = $row["address2"];
    $libaddress3 = $row["address3"];
    $libname = $row["Name"];
    $libphone = $row["phone"];
    $illemail = $row["ill_email"];
    $libparticipant = $row["participant"];
    $oclc = $row["oclc"];
    $loc = $row["loc"];
    $libsuspend = $row["suspend"];
    $system = $row["system"];
    if ($system =="CDLC") {
        $system ="Capital District Library Council";
    } elseif ($system=="CRB") {
        $system ="Capital Region BOCES";
    } elseif ($system=="HFM") {
        $system ="Hamilton-Fulton-Montgomery BOCES";
    } elseif ($system=="MVLS") {
        $system ="Mohawk Valley Library System";
    } elseif ($system=="Q3S") {
        $system="Questar III SLS";
    } elseif ($system=="SALS") {
        $system="Southern Adirondack Library System";
    } elseif ($system=="UHLS") {
        $system="Upper Hudson Library System";
    } elseif ($system=="WSWHE") {
        $system="WSWHE BOCES";
    } else {
        $system="Unknow";
    }
    $book = $row["book_loan"];
    $journal = $row["periodical_loan"];
    $av = $row["av_loan"];
    $reference = $row["theses_loan"];
    $ebook = $row["ebook_request"];
    $ejrnl = $row["ejournal_request"];
    if ($libsuspend=="0") {
        $libsuspend="Yes";
    } else {
        $libsuspend="No";
    }
    if ($libparticipant =="1") {
        $libparticipant ="Yes";
    } else {
        $libparticipant ="No";
    }
    if ($book =="Yes") {
        $book ="Yes";
    } else {
        $book ="No";
    }
    if ($journal =="Yes") {
        $journal ="Yes";
    } else {
        $journal ="No";
    }
    if ($av =="Yes") {
        $av ="Yes";
    } else {
        $av ="No";
    }
    if ($reference =="Yes") {
        $reference ="Yes";
    } else {
        $reference ="No";
    }
    if ($ebook =="Yes") {
        $ebook ="Yes";
    } else {
        $ebook ="No";
    }
    if ($ejrnl =="Yes") {
        $ejrnl ="Yes";
    } else {
        $ejrnl ="No";
    }
    echo "<div class='illDirTableCell'>";
    echo "Name: <strong> $libname</strong><br>";
    echo "Address: <strong> $libaddress2 </strong><br>";
    echo "&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;   &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong> $libaddress3 </strong><br>";
    echo "Phone: <strong> $libphone</strong><br>";
    echo "Library System:<strong> $system</strong><br>";
    if ($user_id>0) {
        echo "ILL Email(s): <a href='mailto:$illemail' target='_blank'>$illemail</a><br>";
    }

    echo "OCLC Symbol: <strong> $oclc</strong><br>";
    echo "LOC Code: <strong> $loc</strong><br>";
    echo "Accepting Requests: <strong> $libsuspend </strong>";
    echo "<br><br>";
    echo "<button onclick='showHide($count)'>Show loaning options</button><br><br>";
    echo "<span class='loadoptions' id='showhide-$count' style='display: none'>";
    echo "Loaning Print Book: <strong>$book</strong><br>";
    echo "Loaning Print Journal or Article: <strong>$journal</strong><br>";
    echo "Loaning Audio Video Materials: <strong>$av</strong><br>";
    echo "Loaning Reference/Microfilm: <strong>$reference</strong><br>";
    echo "Loaning Electronic Book: <strong>$ebook</strong><br>";
    echo "Loaning Electronic Journal: <strong>$ejrnl</strong><br><br>";
    echo "</div>"; #end the illDirTableCell
    if ($count++ % 2 == 0) {
        echo "</div>"; #end the illDirTableRow
        if ($rowcount++ % 2 == 0) {
            echo "<div class='illDirTableRow'>"; #Start the next illDirTableRow
        } else {
            echo "<div class='illDirTableRowGrey'>"; #Start the next illDirTableRow
        }
        //not sure why this is there 9/16/2022
        //$no++;
    }
}
echo "</div>";  #end the illDirTable
if (($page > 0) && (($offset +  $rec_limit)<$GETLISTCOUNTwhole)) {
    $last = $page - 2;
    echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$last\'>Last 50 Records</a> |";
    echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$page\'>Next 50 Records</a>";
} elseif (($page == 0) && ($GETLISTCOUNTwhole  > $rec_limit)) {
    echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$page\'>Next 50 Records</a>";
} elseif (($left_rec < $rec_limit)  && ($GETLISTCOUNTwhole > $rec_limit)) {
    $last = $page - 2;
    echo "<a href='".$_SERVER['REDIRECT_URL']."?page=$last\'>Last 50 Records</a>";
}


?>
