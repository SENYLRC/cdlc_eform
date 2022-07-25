<?php

if (isset($_REQUEST['task'])) {
    $task = $_REQUEST['task'];
}
if (isset($_REQUEST['system'])) {
    $system = $_REQUEST['system'];
}
if (isset($_REQUEST['proceed'])) {
    $proceed = $_REQUEST['proceed'];
}
if (isset($_REQUEST['enddate'])) {
    $enddate = $_REQUEST['enddate'];
}

#If suspenson is set with no end date, a default one of 7 days is calulated
if (($suspend==1)&&(strlen($enddate)<2)) {
    $enddate = strtotime("+7 day");
    $enddate = date('Y-m-d', $enddate);
} else {
    $enddate = date('Y-m-d', strtotime(str_replace('-', '/', $enddate)));
}


if (($system == "none") || ($system == "")) {
    $action="stop";
} elseif ($proceed == "Proceed") {
    $action="doit";
} else {
    $action="go";
}



if ($action == "go") {
    if ($system == "CDLC") {
        $displaysystem="Capital District Library Council";
    }
    if ($system == "CRB") {
        $displaysystem="Capital Region BOCES";
    }
    if ($system == "HFM") {
        $displaysystem="Hamilton-Fulton-Montgomery BOCES";
    }
    if ($system == "MVLS") {
        $displaysystem="Mohawk Valley Library System";
    }
    if ($system == "Q3S") {
        $displaysystem="Questar III SLS";
    }
    if ($system == "SALS") {
        $displaysystem="Southern Adirondack Library System";
    }
    if ($system == "UHLS") {
        $displaysystem="Upper Hudson Library System";
    }
    if ($system == "WSWHE") {
        $displaysystem="WSWHE BOCES";
    }
    echo "You have chosen to <b>$task lending</b> for all libraries of the <b>$displaysystem</b>.<br><br>";
    echo "This will overwrite the setting for these libraries. Are you sure you wish to proceed? "; ?><form action="/status-confirmation" method="post">
  <input type="hidden" name="task" value="<?php echo $task; ?>">
  <input type="hidden" name="system" value="<?php echo $system; ?>">
  <input type="hidden" name="enddate" value="<?php echo $enddate; ?>">
  <input type="submit" name="proceed" value="Proceed"> <a href='/adminlib'>Cancel</a></form><?php
} elseif ($action == "doit") {
        echo "<b>The libraries have been updated!<b>";
        #Connect to database
        require '/var/www/cdlc_script/cdlc_db.inc';
        $db = mysqli_connect($dbhost, $dbuser, $dbpass);
        mysqli_select_db($db, $dbname);


        if ($task == "suspend") {
            #Suspend
            $sqlupdate = "UPDATE `$cdlcLIB` SET suspend='1', SuspendDateEnd='$enddate' WHERE `participant` = '1' and `suspend` = '0' and `system` = '$system' ";
        } else {
            #Activate
            $sqlupdate = "UPDATE `$cdlcLIB` SET suspend='0' WHERE `participant` = '1' and `suspend` = '1' and `system` = '$system' ";
        }
        echo $sqlupdate;
        $result = mysqli_query($db, $sqlupdate);

        #Close the database
        mysqli_close($db);
    } else {
        echo "Sorry! We cannot complete your action.  <a href='/adminlib'>Please go back</a> and select a library system.";
    }
?>
