<script>
(function($) {

Drupal.behaviors.DisableInputEnter = {
  attach: function(context, settings) {
    $('input', context).once('disable-input-enter', function() {
      $(this).keypress(function(e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }requesterEMAIL
      });
    });
  }
}
})(jQuery);
</script>
<p>Please review the details of your request and then select a library to send your request to.</p>
<form action="sent" method="post">
<?php
require '/var/www/cdlc_script/cdlc_function.php';


function find_catalog($location){
  #from DueNorth
  switch ($location) {
    case "Albany College of Pharmacy and Health Sciences":
      return "Worldcat";
      break;
    case "Albany Law School":
      return "SirsiDynix";
      break;
    case "CDLC Partial Union Catalog (PUC) ":
      return "KOHA";
      break;
    case "Capital Region BOCES":
      return "Follett";
      break;
    case "College of Saint Rose":
      return "exlibris";
      break;
    case "Fulton Montgomery Community College":
      return "Primo";
      break;
    case "Hudson Valley Community College":
      return "Primo";
      break;
    case "MVLS and SALS combined catalog":
      return "Polaris";
      break;
    case "New York State Department of Health":
      return "Opals";
      break;
    case "New York State Library":
      return "SirsiDynix";
      break;
    case "Questar School Library System":
      return "Opals";
      break;
      case "Siena College":
      return "Millennium";
      break;
    case "Rensselaer Polytechnic Institute":
      return "Worldcat";
      break;
    case "SUNY Plattsburgh":
      return "Generic";
      break;
    case "Jefferson Community College":
      return "Generic";
      break;
    case "North Country Community College":
      return "Generic";
      break;
    case "Clarkson University Library":
      return "Worldcat";
      break;
    case "Fort Drum McEwen Library":
      return "Millennium";
      break;
  }
}


#Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];

#######This function is used for the encoding of the curl command
function myUrlEncode($string)
{
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

####Define the server to make the CURL request to
$reqserverurl='https://cdlc.indexdata.com/service-proxy/?command=record\\&windowid=';
###Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);

######put in curl coammd in as html comment for development
 echo "<!-- my cmd is  $cmd \n-->";

####Run the CURL to get XML data
$output = shell_exec($cmd);

#####/put xml in html src for development
#echo "<!-- \n";
#print_r ($output);
#echo "\n-->\n\n";

$user_id = \Drupal::currentUser()->id();
$user = \Drupal\user\Entity\User::load($user_id);

//Getting the values
$display_username = $user->getAccountName();
echo "$display_username<br><br>\n";
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


if (filter_var($backupemail, FILTER_VALIDATE_EMAIL)) {
    //valid address do nothing;
} else {
    //not valid address unset the variable
    unset($backupemail);
}
//check if backup email is set
if (isset($backupemail)) {
    // Use == operator
    if ($email == $backupemail) {
        //email and backup are the same, do nothing
    } else {
        //email and backup are different add backup to the request
        $email =$backupemail.','.$email;
    }
}
########Display the details of the person making the request
echo "<h1>Requester Details</h1>";
echo "First Name:  " .$field_first_name. "<br>";
echo "Last Name:  ".$field_last_name. "<Br>";
echo "E-mail:  ".$email. "<br>";
echo  "Institution:  ".$field_your_institution ."<br>";
echo    "Work Phone: ".$field_work_phone ."<br>";
echo   "Mailing Address:<br>  ".$field_street_address."<br> ".$field_city_state_zip."<br><br>";
echo "<input type='hidden' name='fname' value= ' ".$field_first_name ." '>";
echo "<input type='hidden' name='lname' value= ' ".$field_last_name ." '>";
echo "<input type='hidden' name='email' value= ' ".$email ."'>";
$field_your_institution_clean=htmlspecialchars($field_your_institution, ENT_QUOTES);
echo "<input type='hidden' name='inst' value= ' ".$field_your_institution_clean." '>";
echo "<input type='hidden' name='address' value= ' ".$field_street_address ." '>";
echo "<input type='hidden' name='caddress' value= ' ".$field_city_state_zip ." '>";
echo "<input type='hidden' name='wphone' value= ' ".$field_work_phone ." '>";
echo "<input type='hidden' name='reqLOCcode' value= ' ".$field_loc_location_code ." '>";
#Display the request form to user
?>
Need by date <input type="text" name="needbydate"><br>
Note <input type="text" size="100" name="reqnote"><br><br>
Is this a request for an article?
Yes <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="yesCheck">
No <input type="radio" onclick="javascript:yesnoCheck();" name="yesno" id="noCheck"><br>
<div id="ifYes" style="display:none">
Article Title: <input size="80" type="text" name="arttile"><br>
Article Author: <input size="80" type='text' name='artauthor'><br>
Volume: <input size="80" type='text' name='artvolume'><br>
Issue:  <input type='text' name='artissue'><br>
Pages: <input type='text' name='artpage' ><br>
Issue Month: <input type='text' name='artmonth' ><br>
Issue Year: <input type='text' name='artyear' ><br>
Copyright compliance:  <select name="artcopyright">  <option value=""></option> <option value="ccl">CCL</option>   <option value="ccg">CCG</option>  </select>
</div><br><hr>
<?php
#Now we process the xml for Indexdata
$records = new SimpleXMLElement($output); // for production
$requestedtitle=$records->{'md-title-complete'};
$requestedtitle2=$records->{'md-title-number-section'};
$requestedauthor=$records->{'md-author'};
$requested=$records->{'md-title'};
$itemtype=$records->{'md-medium'};
#Remove any white space stored in item type
$itemtype=trim($itemtype);
$pubdate=$records->{'md-date'};
$isbn=$records->{'md-isbn'};
$issn=$records->location->{'md-issn'};

$requestedauthor = preg_replace('/[[:^print:]]/', '', $requestedauthor);
$requestedtitle = preg_replace('/[[:^print:]]/', '', $requestedtitle);
$requestedtitle2 = preg_replace('/[[:^print:]]/', '', $requestedtitle2);
echo "Requested Title:<b>: " . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "Requested Author:<b>: " . $requestedauthor ."</b><br>";
echo "Item Type:  " . $itemtype."<br>";
echo "Publication Date: " . $pubdate."<br>";
if (strlen($issn)>0) {
    echo "ISSN: " . $issn."<br>";
}
if (strlen($isbn)>0) {
    echo "ISBN: " . $isbn."<br>";
}
echo "<be>";
#Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor, ENT_QUOTES);



echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";
########Pull holding info and make available to requester to choose one#################################
###Set receiver email to senylrc for testing
#$destemail="noc@senylrc.org";

##This will loop through all the libraries that have title and see if they should be in drop down to a make a request
echo "<select required name='destination'>";
echo "<option style='background-color:#d4d4d4;'  value=''> Please Select a library</option>";
#This variable is used to count destination libraries available to make the request

#This section of listing locations was done by Chuck at NNYLN
$loccount='0'; #Counts available locations
$deadlibraries = array(); #Initializes the array which keeps the unavailable libraries.
foreach ($records->location as $location) { #Locations loop start
  $catalogtype = find_catalog($location['name']);
  $urlrecipe = $location->{'md-url_recipe'};
  $mdid = $location->{'md-id'};
foreach ($location->holdings->holding as $holding) { #generic holding loop start
} #end foreach generic holding loop
} #end foreach location loop

    echo "</form>";
    ?>
