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


function build_notes($reqnote, $lendnote) {
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
function build_renewnotes($renewNote, $renewNoteLender) {
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
function build_return_notes($returnnote, $returnmethodtxt) {
    if ((strlen($returnnote) > 2) || (strlen($returnmethod) > 2)) {
        $dispalyreturnnotes = "Return Note: " .$returnnote."  <Br>Return Method: ".$returnmethodtxt;
    }
    return $dispalyreturnnotes;
}
function checked($filter_value) {
    if (($filter_value == "yes")) {
        $filterout="checked";
    } else {
        $filterout="";
    }
    return $filterout;
}
function shipmtotxt($shipmethod) {
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
    if ($shipmethod=="other") {
        $shiptxt='Other';
    }
    if ($shipmethod=="") {
        $shiptxt='';
    }
    return $shiptxt;
}
function itemstatus($fill, $receiveaccount, $returnaccount, $returndate, $receivedate, $checkinaccount, $checkindate) {
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
function selected($days, $filter_value) {
    if ($days == $filter_value) {
        $filterout = "selected";
    } else {
        $filterout = "";
    }
    return $filterout;
}
function elementHunt($startdated, $hunting) {
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
function convertDate($InputDate) {
    $Y = elementHunt($InputDate, "Y");
    $M = elementHunt($InputDate, "M");
    $D = elementHunt($InputDate, "D");
    $OutputDate = $Y . "-" . $M . "-" . $D;
    return $OutputDate;
}
function returnLimits($Offset, $filter_numresults) {
    if (($Offset == "") || ($$Offset = 0)) {
        $startint = 0;
    } else {
        $startint = $Offset * $filter_numresults;
    }
    $endint = $startint + $filter_numresults;
}
#start of DueNorth Functions
function normalize_availability($itemavail) {
  $itemavail = str_replace (" ","", $itemavail);
  $itemavail = str_replace ("\n","", $itemavail);
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
function set_availability($itemavail) {
  if ($itemavail == 1) return "Available";
  if ($itemavail == 0) return "Unavailable";
  if ($itemavail == 2) return "UNKNOWN";
}
function find_locationinfo ($locationalias) {
  $libparticipant='';
  require '/var/www/cdlc_script/cdlc_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT loc,participant,`ill_email`,suspend,system,Name FROM `$cdlcLIB` where alias = '$locationalias' ";
  $result=mysqli_query($db, $GETLISTSQL);
  $row = mysqli_fetch_row($result);
  $libparticipant = $row;
  return $libparticipant;
}

function check_itemtype ($destill,$itemtype) {
  require '/var/www/cdlc_script/cdlc_db.inc';
  $db = mysqli_connect($dbhost, $dbuser, $dbpass);
  mysqli_select_db($db,$dbname);
  $GETLISTSQL="SELECT book_loan,av_loan,ejournal_request,theses_loan,ebook_request FROM `$cdlcLIB` where loc = '$destill' ";
  $result=mysqli_query($db, $GETLISTSQL);
  while($row = $result->fetch_assoc() ) {
    if ((strcmp($itemtype, 'book') == 0) || (strcmp($itemtype, 'book (large print)') == 0)) {
      #See if  request is for a book
      if ( $row['book']==1   ) {
      #Checking if book is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'journal') == 0) || (strcmp($itemtype, 'journal (electronic)') == 0) ) {
    #See if  request is for a journal
      if ( $row['journal']==1   ) {
      #Checking if journal is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'book (electronic)') == 0) || (strcmp($itemtype, 'web') == 0) ) {
    #See if  request is for ebook
      if ( $row['ebook']==1   ) {
      #Checking if e-book is allowed
        return 1;
      }
    }
    if ( (strpos($itemtype, 'recording') !== FALSE) || (strpos($itemtype, 'video') !== FALSE) || (strpos($itemtype, 'audio') !== FALSE) ){
    #See if  request is  audio video related
      if ( $row['av']==1   ) {
      #Checking if AV is allowed
        return 1;
      }
    }
    if ( (strcmp($itemtype, 'other') == 0) || (strcmp($itemtype, 'music-score') == 0) || (strcmp($itemtype, 'map') == 0) || (strcmp($itemtype, 'other (electronic)') == 0) ) {
    #See if  request is for reference
      if ( $row['reference']==1   ) {
      #Checking if reference is allowed
        return 1;
      }
    }
  }
  return 0; #Matched none of the above
} # end check_itemtype
