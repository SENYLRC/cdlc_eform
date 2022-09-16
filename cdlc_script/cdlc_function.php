<?php

$user_id = \Drupal::currentUser()->id();
$user = \Drupal\user\Entity\User::load($user_id);

//Getting the values
$display_username = $user->getAccountName();
//For testing
//echo "$display_username<br><br>\n";
$field_first_name = $user->get('field_first_name')->value;
$field_last_name = $user->get('field_last_name')->value;
$field_your_institution = $user->get('field_your_institution')->value;
$field_loc_location_code = $user->get('field_loc_location_code')->value;
$field_street_address = $user->get('field_street_address')->value;
$field_street_address2 = $user->get('field_street_address2')->value;
$field_city_state_zip = $user->get('field_city_state_zip')->value;
$field_work_phone = $user->get('field_work_phone')->value;
$field_home_library_system = $user->get('field_home_library_system')->value;
$field_filter_own_system = $user->get('field_filter_own_system')->value;
$field_backup_email = $user->get('field_backup_email')->value;
$email = $user->getEmail();

$AuthedUser ="1";
$firstname = $field_first_name;
$lastname = $field_last_name;
$wholename = "$firstname $lastname";


function build_notes($reqnote, $lendnote)
{
    $displaynotes = "";
    if ((strlen($reqnote) > 2) && (strlen($lendnote) > 2)) {
        $displaynotes = $reqnote . "</br>Lender Note: " . $lendnote;
    }
    if ((strlen($reqnote) > 2) && (strlen($lendnote) < 2)) {
        $displaynotes=$reqnote;
    }
    if ((strlen($reqnote) < 2) && (strlen($lendnote) > 2)) {
        $displaynotes= "Lender Note: " . $lendnote;
    }
    return $displaynotes;
}
function build_renewnotes($renewNote, $renewNoteLender)
{
    $displayrenewnotes = "";
    if ((strlen($renewNote) > 2) && (strlen($renewNoteLender) > 2)) {
        $displayrenewnotes = "Renew Note: ".$renewNote . "</br>Lender Note: " . $renewNoteLender;
    }
    if ((strlen($renewNote) > 2) && (strlen($renewNoteLender) < 2)) {
        $displayrenewnotes="Renew Note: ".$renewNote;
    }
    if ((strlen($renewNote) < 2) && (strlen($renewNoteLender) > 2)) {
        $displaynotes= "Lender Note: " . $renewNoteLender;
    }
    return $displayrenewnotes;
}
function build_return_notes($returnnote, $returnmethodtxt)
{
    if ((strlen($returnnote) > 2) || (strlen($returnmethod) > 2)) {
        $dispalyreturnnotes = "Return Note: " .$returnnote."  <Br>Return Method: ".$returnmethodtxt;
    }
    return $dispalyreturnnotes;
}
function checked($filter_value)
{
    if (($filter_value == "yes")) {
        $filterout="checked";
    } else {
        $filterout="";
    }
    return $filterout;
}
function shipmtotxt($shipmethod)
{
    if ($shipmethod=="usps") {
        $shiptxt='US Mail';
    }
    if ($shipmethod=="mhls") {
        $shiptxt='Mid-Hudson Courier';
    }
    if ($shipmethod=="rcls") {
        $shiptxt='RCLS Courier';
    }
    if ($shipmethod=="empire") {
        $shiptxt='Empire Delivery';
    }
    if ($shipmethod=="ups") {
        $shiptxt='UPS';
    }
    if ($shipmethod=="fedex") {
        $shiptxt='FedEx';
    }
    if ($shipmethod=="crb") {
        $shiptxt='Capital Region BOCES Courier';
    }
    if ($shipmethod=="other") {
        $shiptxt='Other';
    }
    if ($shipmethod=="") {
        $shiptxt='';
    }
    return $shiptxt;
}
function itemstatus($fill, $receiveaccount, $returnaccount, $returndate, $receivedate, $checkinaccount, $checkindate)
{
    if ($fill=="1") {
        $fill="Filled";
    }
    if ($fill=="0") {
        $fill="Not Filled";
    }
    if ($fill=="3") {
        $fill="No Answer";
    }
    if ($fill=="4") {
        $fill="Expired";
    }
    if ($fill=="6") {
        $fill="Canceled";
    }
    if ((strlen($receiveaccount)>1)&&(strlen($returnaccount)<1)&&(strlen($checkinaccount)<1)) {
        $fill="Loan Item Received<br>".$receivedate."";
    }
    if ((strlen($checkinaccount)<1)&&(strlen($receiveaccount)>1)&&(strlen($returnaccount)>1)) {
        $fill="Loan Item Returned<Br>".$returndate."";
    }
    if (strlen($checkinaccount)>1) {
        $fill="Item Checkin by Lender<Br>".$checkindate."";
    }
    return $fill;
}
function selected($days, $filter_value)
{
    if ($days == $filter_value) {
        $filterout = "selected";
    } else {
        $filterout = "";
    }
    return $filterout;
}
function elementHunt($startdated, $hunting)
{
    switch ($hunting) {
        case "D":
            $hunted = substr($startdated, 3, 2);
            break;
        case "M":
            $hunted = substr($startdated, 0, 2);
            break;
        case "Y":
            $hunted = substr($startdated, 6, 4);
            break;
    }
    return $hunted;
}
function convertDate($InputDate)
{
    $Y = elementHunt($InputDate, "Y");
    $M = elementHunt($InputDate, "M");
    $D = elementHunt($InputDate, "D");
    $OutputDate = $Y . "-" . $M . "-" . $D;
    return $OutputDate;
}
function returnLimits($Offset, $filter_numresults)
{
    if (($Offset == "") || ($$Offset = 0)) {
        $startint = 0;
    } else {
        $startint = $Offset * $filter_numresults;
    }
    $endint = $startint + $filter_numresults;
}
#start of eform Functions
function normalize_availability($itemavail)
{
    $itemavail = str_replace(" ", "", $itemavail);
    $itemavail = str_replace("\n", "", $itemavail);
    switch ($itemavail) {
        case "-":
            return 1;
            break;
        case "AVAILABLE":
            return 1;
            break;
        case "Available":
            return 1;
            break;
        case "CheckedIn":
            return 1;
            break;
        default:
            return 0;
    }
}
function set_availability($itemavail)
{
    if ($itemavail == 1) {
        return "Available";
    }
    if ($itemavail == 0) {
        return "Unavailable";
    }
    if ($itemavail == 2) {
        return "UNKNOWN";
    }
}
function set_koha_availability($itemavail)
{
    if ($itemavail == 0) {
        return "Available";
    }
    if ($itemavail == 1) {
        return "Unavailable";
    }
    if ($itemavail == 2) {
        return "UNKNOWN";
    }
}

function find_catalog($location)
{
    switch ($location) {
        case "CDLC Partial Union Catalog (PUC)":
            return "Koha";
            break;
        case "HFM BOCES":
            return "Alexandria";
            break;
        case "Albany College of Pharmacy and Health Sciences":
            return "Worldcat";
            break;
        case "New York State Department of Health":
            return "OPALS";
            break;
        case "Siena College":
            return "Alma";
            break;
        case "Albany Law School":
            return "Symphony";
            break;
        case "Hudson Valley Community College":
            return "Alma";
            break;
        case "University at Albany":
            return "Alma";
            break;

        case "Fulton Montgomery Community College":
            return "Alma";
            break;
        case "Rudolf Steiner Library":
            return "OPALS";
            break;
        case "Union College":
            return "Alma";
            break;
        case "SUNY Adirondack":
            return "Alma";
            break;
        case "SUNY Cobleskill":
            return "Alma";
            break;
        case "Schenectady County Community College":
            return "Alma";
            break;
        case "Russell Sage College":
            return "Alma";
            break;
        case "Skidmore College":
            return "Folio";
            break;
        case "College of Saint Rose":
            return "Voyager";
            break;
        case "Rensselaer Polytechnic Institute":
            return "Worldcat";
            break;
        case "MVLS and SALS combined catalog":
            return "Polaris";
            break;
        case "Upper Hudson Library System":
            return "Innovative";
            break;
        case "WSWHE BOCES School Library System":
            return "OPALS";
            break;
        case "Scotia Glenville School District":
            return "OPALS";
            break;
        case "Questar School Library System":
            return "OPALS";
            break;
        case "Capital Region BOCES":
            return "SirsiDynix";
            break;
        case "Emma Willard":
            return "SirsiDynix";
            break;
        case "New York State Library":
            return "SirsiDynix";
            break;

    }
}
function find_locationinfo($locationalias, $locationname)
{
    //make sure we tailing white space
    $locationalias=trim($locationalias);
    $locationname=trim($locationname);
    $libparticipant='';
    require '/var/www/cdlc_script/cdlc_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    if ($locationname == "MVLS and SALS combined catalog") {
        $a2= explode(":", $locationalias);
        $locationalias=strtok($a2[0], ' ');
        $GETLISTSQL="SELECT loc,participant,`ill_email`,suspend,system,Name,alias FROM `$cdlcLIB` where alias LIKE '%".$locationalias."%'  and (`system`='mvls' or `system`='sals')";
    } else {
        $GETLISTSQL="SELECT loc,participant,`ill_email`,suspend,system,Name,alias FROM `$cdlcLIB` where alias = '$locationalias' ";
    }
    #for test list of libraries on request page
    #echo $GETLISTSQL."<br>";
    #echo $locationalias."<br>";
    #echo $locationname."<br>";

    $result=mysqli_query($db, $GETLISTSQL);
    $row = mysqli_fetch_row($result);
    $libparticipant = $row;
    return $libparticipant;
}

function check_itemtype($destill, $itemtype)
{
    require '/var/www/cdlc_script/cdlc_db.inc';
    $db = mysqli_connect($dbhost, $dbuser, $dbpass);
    mysqli_select_db($db, $dbname);
    $GETLISTSQL="SELECT book_loan,av_loan,ejournal_request,theses_loan,ebook_request FROM `$cdlcLIB` where loc = '$destill' ";
    $result=mysqli_query($db, $GETLISTSQL);
    while ($row = $result->fetch_assoc()) {
        if ((strcmp($itemtype, 'book') == "No") || (strcmp($itemtype, 'book (large print)') == "No")) {
            #See if  request is for a book
            if ($row['book_loan']=="Yes") {
                #Checking if book is allowed
                return 1;
            }
        }
        if ((strcmp($itemtype, 'journal') == "No") || (strcmp($itemtype, 'journal (electronic)') == "No")) {
            #See if  request is for a journal
            if ($row['ejournal_request']=="Yes") {
                #Checking if journal is allowed
                return 1;
            }
        }
        if ((strcmp($itemtype, 'book (electronic)') == "No") || (strcmp($itemtype, 'web') == "No")) {
            #See if  request is for ebook
            if ($row['ebook_request']=="Yes") {
                #Checking if e-book is allowed
                return 1;
            }
        }
        if ((strpos($itemtype, 'recording') !== false) || (strpos($itemtype, 'video') !== false) || (strpos($itemtype, 'audio') !== false)) {
            #See if  request is  audio video related
            if ($row['av_loan']=="Yes") {
                #Checking if AV is allowed
                return 1;
            }
        }
        if ((strcmp($itemtype, 'other') == 0) || (strcmp($itemtype, 'music-score') == "No") || (strcmp($itemtype, 'map') == "No") || (strcmp($itemtype, 'other (electronic)') == "No")) {
            #See if  request is for reference
            if ($row['theses_loan']=="Yes") {
                #Checking if reference is allowed
                return 1;
            }
        }
    }
    return 0; #Matched none of the above
} # end check_itemtype
