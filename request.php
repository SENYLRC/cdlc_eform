<script>

(function($) {

Drupal.behaviors.DisableInputEnter = {
  attach: function(context, settings) {
    $('input', context).once('disable-input-enter', function() {
      $(this).keypress(function(e) {
        if (e.keyCode == 13) {
          e.preventDefault();
        }
      });
    });
  }
}



})(jQuery);
</script>
<?php
require '/var/www/cdlc_script/cdlc_function.php';


#Get the IDs needed for curl command
$jession= $_GET['jsessionid'];
$windowid= $_GET['windowid'];
$idc= $_GET['id'];
#Define the server to make the CURL request to
$reqserverurl='https://cdlc.indexdata.com/service-proxy/?command=record\\&windowid=';
#Define the CURL command
$cmd= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc);
#put in curl command in as html comment for development
#echo "<!-- my cmd is  $cmd \n-->";
#Run the CURL to get XML data
$output = shell_exec($cmd);
#put xml in html src for development
#echo "<!-- \n";
#print_r($output);
#echo "\n-->\n\n";


echo "<p>Please review the details of your request and then select a library to send your request to.</p>";
echo "<form action='sent' method='post'>";
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
echo "<h1>Request Details</h1>";
echo "Need by date <input type='text' name='needbydate'><br><br>";
echo "Note <input type='text' size='100' name='reqnote'><br><br>";
echo "Patron Name or Barcode <input SIZE=100 MAXLENGTH=255 type='text' size='100' name='patronname'><br><br>";
echo "Is this a request for an article?";
echo "Yes <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='yesCheck'>";
echo "No <input type='radio' onclick='javascript:yesnoCheck();' name='yesno' id='noCheck' checked='checked'><br>";
echo "<div id='ifYes' style='display:none'>";
echo "Article Title: <input size='80' type='text' name='arttile'><br>";
echo "Article Author: <input size='80' type='text' name='artauthor'><br>";
echo "Volume: <input size='80' type='text' name='artvolume'><br>";
echo "Issue:  <input type='text' name='artissue'><br>";
echo "Pages: <input type='text' name='artpage' ><br>";
echo "Issue Month: <input type='text' name='artmonth' ><br>";
echo "Issue Year: <input type='text' name='artyear' ><br>";
echo "Copyright compliance:  <select name='artcopyright'>";
echo "<option value=''></option>";
echo "<option value='ccl'>CCL</option>";
echo "<option value='ccg'>CCG</option></select></div><br>";



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

echo "Requested Title: <b>" . $requestedtitle  ."  ". $requestedtitle2 . "</b><br>";
echo "Requested Author: <b>" . $requestedauthor ."</b><br>";
echo "Item Type:  " . $itemtype."<br>";
echo "Publication Date: " . $pubdate."<br>";
if (strlen($issn)>0) {
    echo "ISSN: " . $issn."<br>";
}
if (strlen($isbn)>0) {
    echo "ISBN: " . $isbn."<br>";
}
echo "<br>";

#Covert single quotes to code so they don't get cut off
$requestedtitle=htmlspecialchars($requestedtitle, ENT_QUOTES);
$requestedtitle2=htmlspecialchars($requestedtitle2, ENT_QUOTES);
$requestedauthor =htmlspecialchars($requestedauthor, ENT_QUOTES);
#echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." : ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibtitle' value= ' ".$requestedtitle ." ". $requestedtitle2 ." '>";
echo "<input type='hidden' name='bibauthor' value= ' ".$requestedauthor ." '>";
echo "<input type='hidden' name='bibtype' value= ' ".$itemtype ." '>";
echo "<input type='hidden' name='pubdate' value= ' ".$pubdate ." '>";
echo "<input type='hidden' name='isbn' value= ' ".$isbn ." '>";
echo "<input type='hidden' name='issn' value= ' ".$issn ." '>";

echo "<p>Select the library you would like to request from.<br>";
echo "Please limit multiple copy requests to classroom sets or book clubs.</p>";
echo "<p>This is a request for: <br>";
echo "<input type='radio' name='singlemulti' id='singleCheck' checked='checked' onclick='javascript:multiRequest();'>";
echo "a single copy <input type='radio' name='singlemulti' id='multiCheck' onclick='javascript:multiRequest();'> multiple copies<br><p>";



$loccount='0'; #Counts available locations
$deadlibraries = array(); #Initializes the array which keeps the unavailable libraries.
foreach ($records->location as $location) { #Locations loop start
    $catalogtype = find_catalog($location['name']);
    $urlrecipe = $location->{'md-url_recipe'};
    $mdid = $location->{'md-id'};
    //echo "zack my location is ".$location['name']."<br>";
    foreach ($location->holdings->holding as $holding) { #generic holding loop start
        $itemavail=$holding->localAvailability;
#        if ($catalogtype == "OPALS") {
#            $itemavail=$itemavail>0 ? $itemavail="-" : $itemavail="0";
#            echo "the OPALS itemavail is ".$itemavail."<br>";
#        } #OPALS might return (-1 through +X
        $itemavail=normalize_availability($itemavail); #0=No, 1=Yes
        $itemavailtext=set_availability($itemavail);
        $itemcallnum=$holding->callNumber;
        $itemcallnum=htmlspecialchars($itemcallnum, ENT_QUOTES); #Sanitizes callnumbers with special characters in them
        $itemlocation=$holding->localLocation; #Gets the alias
        if ($catalogtype == "Worldcat" || $catalogtype == 'cdlc' || $catalogtype == "Millennium") {
            $itemlocation=$location['name'];
        }
        if (($catalogtype == "Alma") ||  ($catalogtype == "Voyager")||($catalogtype == "Folio")|| ($catalogtype == "Symphony")|| ($catalogtype == "SirsiDynix")) {
            $itemlocation=$location['name'];
        }
        if (($catalogtype == "OPALS") || ($catalogtype == "Polaris")) {
            $itemlocation=  $holding->localLocation;
        }
        $locationinfo=find_locationinfo($itemlocation, $location['name']);
        $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); #Sanitizes locations with special characters in them
        $destill=$locationinfo[0]; #Destination ILL Code
        $destpart=$locationinfo[1]; #0=No, 1=Yes
        $destemail=$locationinfo[2]; #Destination emails
        $destsuspend=$locationinfo[3]; #0=No, 1=Yes
        $destlibsystem=$locationinfo[4]; #Destination library system
        $destlibname=$locationinfo[5]; #Destination library name
        $destAlias=$locationinfo[6]; #Destination Alias
        $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); #Sanitizes library names with special characters in them
        $desttypeloan=check_itemtype($destill, $itemtype); #0=No, 1=Yes
        if (($catalogtype == "Innovative") && ($itemlocation == "ODY Folio")) {
            $desttypeloan=1;
        }
        $itemlocallocation=$itemlocation; #Needed in sent.php
        echo "<!-- \n";
        echo "catalogtype: $catalogtype \n";
        echo "itemavail: $itemavail (1) \n";
        echo "itemavailtext: $itemavailtext \n";
        echo "itemlocallocation: $itemlocallocation \n";
        echo "itemlocation: $itemlocation \n";
        echo "destill: $destill \n";
        echo "destpart: $destpart (1)\n";
        echo "destemail: $destemail \n";
        echo "destsuspend: $destsuspend (0)\n";
        echo "destlibsystem: $destlibsystem \n";
        echo "destlibname: $destlibname \n";
        echo "desttypeloan: $desttypeloan (1)\n";
        echo "failmessage: $failmessage\n";
        echo "--> \n\n";
        $destfail=0; #0=No, 1=Yes
        if ($itemavail == 0) {
            $destfail = 1;
            $failmessage = "Material unavailable, see source ILS/LMS for details";
        }
        if ($destpart == 0) {
            $destfail = 1;
            $failmessage = "Library not particpating in CaDiLaC";
        }
        if (strlen($destemail) < 2) {
            $destfail = 1;
            $failmessage = "Library has no ILL email configured";
        }
        if ($destsuspend == 1) {
            $destfail = 2;
            $failmessage = "Library not loaning / closed";
        }
        if ($desttypeloan == 0) {
            $destfail = 2;
            $failmessage = "Library not loaning this material type";
        }
        if (($destlibsystem == $field_home_library_system[0]['value']) && ($field_filter_own_system[0]['value'] == 1)) {
            $destfail = 1;
            $failmessage = "Library a member of your system, please request through your ILS/LMS";
        }
        if ($destill == "") {
            $destfail = 1;
            $destlibname = $itemlocation;
            $destlibsystem = "Unknown";
            $failmessage = "No alias match in eForm directory";
        }
        if ($destfail == 0) {
            $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
            $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
            $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
            //note in html.html.twig I set the body tag to   <body onload="multiRequest()"; {{ attributes }}>
            echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
            echo"<div class='singlereq'><input type='radio' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
            $loccount=$loccount+1;
        } elseif ($destfail == 1) {
        //only showing error code 2
        } else {
            $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
            echo "<!-- Holding location failed checks. --> \n";
        }
    } #Generic holding loop end
    //do a loop for Albany Law School
    if ($location['name']== 'Albany Law School') {
        $itemtype=$records->{'md-medium'};

        #####Pull the checksum for the location
        $seslcchecksum=$location['checksum'];
        #####################redo the curl statement to includes the checksum
        $cmdseslc= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$seslcchecksum\&offset=1";
        $outputseslc = shell_exec($cmdseslc);
        ######This echo will show the CURL statment as an HTML comment
        #echo "\n<br><!-- my cmd albay law cmd is $cmdseslc \n-->";
        $recordssSESLC= new SimpleXMLElement($outputseslc); // for production
        $itemcallnum=$recordssSESLC->d050->sa;
        ######Go through the holding records
        foreach ($recordssSESLC->d994 as $d994) {
            $itemlocation=$d994->sb;
            $locationinfo=find_locationinfo($itemlocation, $location['name']);
            $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); #Sanitizes locations with special characters in them
            $destill=$locationinfo[0]; #Destination ILL Code
            $destpart=$locationinfo[1]; #0=No, 1=Yes
            $destemail=$locationinfo[2]; #Destination emails
            $destsuspend=$locationinfo[3]; #0=No, 1=Yes
            $destlibsystem=$locationinfo[4]; #Destination library system
            $destlibname=$locationinfo[5]; #Destination library name
            $destAlias=$locationinfo[6]; #Destination Alias
            $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); #Sanitizes library names with special characters in them
            $desttypeloan=check_itemtype($destill, $itemtype); #0=No, 1=Yes

            echo "<!-- \n";
            echo "catalogtype: $catalogtype \n";
            echo "itemavail: $itemavail (1) \n";
            echo "itemavailtext: $itemavailtext \n";
            echo "itemlocallocation: $itemlocallocation \n";
            echo "itemlocation: $itemlocation \n";
            echo "destill: $destill \n";
            echo "destpart: $destpart (1)\n";
            echo "destemail: $destemail \n";
            echo "destsuspend: $destsuspend (0)\n";
            echo "destlibsystem: $destlibsystem \n";
            echo "destlibname: $destlibname \n";
            echo "desttypeloan: $desttypeloan (1)\n";
            echo "failmessage: $failmessage\n";
            echo "--> \n\n";
            $destfail=0; #0=No, 1=Yes
            if ($itemavail == 1) {
                $destfail = 1;
                $failmessage = "Material unavailable, see source ILS/LMS for details";
            }
            if ($destpart == 0) {
                $destfail = 1;
                $failmessage = "Library not particpating in CaDiLaC";
            }
            if (strlen($destemail) < 2) {
                $destfail = 1;
                $failmessage = "Library has no ILL email configured";
            }
            if ($destsuspend == 1) {
                $destfail = 2;
                $failmessage = "Library not loaning / closed";
            }
            if ($desttypeloan == 0) {
                $destfail = 2;
                $failmessage = "Library not loaning this material type";
            }

            if (strlen($$destAlias) < 2) {
                $destfail = 1;
                $destlibname = $itemlocation;
                $destlibsystem = "Unknown";
                $failmessage = "No alias match in eForm directory";
            }
            echo "<!-- \n";
            echo "destfail: $destfail\n";
            echo "--> \n\n";
            if ($destfail == 0) {
                $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
                $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
                $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
                echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                echo"<div class='singlereq'><input type='radio' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                $loccount=$loccount+1;
            } elseif ($destfail == 1) {
                //not showing fail code 1 to end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
            } else {
                //will show other error to inform end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
                echo "<!-- Holding location failed checks. --> \n";
            }
        }//end foreach loop for albany law school 994
    }//end if check for albany law school


    //want to add Koha locations to selection
    if (($catalogtype == "Koha")|| ($catalogtype == "Alexandria")) {
        #####Pull the checksum for the location
        $seslcchecksum=$location['checksum'];
        #####################redo the curl statement to includes the checksum
        $cmdseslc= "curl -b JSESSIONID=$jession $reqserverurl$windowid\\&id=". urlencode($idc)."\&checksum=$seslcchecksum\&offset=1";
        $outputseslc = shell_exec($cmdseslc);
        ######This echo will show the CURL statment as an HTML comment
        #echo "\n<br><!-- my cmd koha is $cmdseslc \n-->";
        $recordssSESLC= new SimpleXMLElement($outputseslc); // for production
        ######Go through the holding records
        foreach ($recordssSESLC->d952 as $d952) {
            //$itemavai=$d952['i1'];
            $itemlocation=$d952->sb;
            $itemcallnum=$d952->s6;
            $itemavail=$d952->s7;
            #Remove colon from call numbers
            $seslccall= str_replace(':', '.', $seslccall);
            $itemavailtext=set_koha_availability($itemavail);
            $locationinfo=find_locationinfo($itemlocation, $location['name']);
            $itemlocation=htmlspecialchars($itemlocation, ENT_QUOTES); #Sanitizes locations with special characters in them
            $destill=$locationinfo[0]; #Destination ILL Code
            $destpart=$locationinfo[1]; #0=No, 1=Yes
            $destemail=$locationinfo[2]; #Destination emails
            $destsuspend=$locationinfo[3]; #0=No, 1=Yes
            $destlibsystem=$locationinfo[4]; #Destination library system
            $destlibname=$locationinfo[5]; #Destination library name
            $destAlias=$locationinfo[6]; #Destination Alias
            $destlibname=htmlspecialchars($destlibname, ENT_QUOTES); #Sanitizes library names with special characters in them
            $desttypeloan=check_itemtype($destill, $itemtype); #0=No, 1=Yes
            $itemlocallocation=$itemlocation; #Needed in sent.php
            echo "<!-- \n";
            echo "catalogtype: $catalogtype \n";
            echo "itemavail: $itemavail (1) \n";
            echo "itemavailtext: $itemavailtext \n";
            echo "itemlocallocation: $itemlocallocation \n";
            echo "itemlocation: $itemlocation \n";
            echo "destill: $destill \n";
            echo "destpart: $destpart (1)\n";
            echo "destemail: $destemail \n";
            echo "destsuspend: $destsuspend (0)\n";
            echo "destlibsystem: $destlibsystem \n";
            echo "destlibname: $destlibname \n";
            echo "desttypeloan: $desttypeloan (1)\n";
            echo "failmessage: $failmessage\n";
            echo "--> \n\n";
            $destfail=0; #0=No, 1=Yes
            if ($itemavail == 1) {
                $destfail = 1;
                $failmessage = "Material unavailable, see source ILS/LMS for details";
            }
            if ($destpart == 0) {
                $destfail = 1;
                $failmessage = "Library not particpating in CaDiLaC";
            }
            if (strlen($destemail) < 2) {
                $destfail = 1;
                $failmessage = "Library has no ILL email configured";
            }
            if ($destsuspend == 1) {
                $destfail = 2;
                $failmessage = "Library not loaning / closed";
            }
            if ($desttypeloan == 0) {
                $destfail = 2;
                $failmessage = "Library not loaning this material type";
            }
            //  if (($destlibsystem == $field_home_library_system[0]['value']) && ($field_filter_own_system[0]['value'] == 1)) {
            //        $destfail = 1;
            //          $failmessage = "Library a member of your system, please request through your ILS/LMS";
            //      }
            if ($destAlias == "") {
                $destfail = 1;
                $destlibname = $itemlocation;
                $destlibsystem = "Unknown";
                $failmessage = "No alias match in eForm directory";
            }
            echo "<!-- \n";
            echo "destfail: $destfail\n";
            echo "--> \n\n";
            if ($destfail == 0) {
                $itemcallnum= preg_replace('/[:]/', ' ', $itemcallnum);
                $itemlocation= preg_replace('/[:]/', ' ', $itemlocation);
                $itemlocallocation= preg_replace('/[:]/', ' ', $itemlocallocation);
                echo"<div class='multiplereq'><input type='checkbox' class='librarycheck' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                echo"<div class='singlereq'><input type='radio' class='librarycheck[]' name='libdestination[]' value='". $itemlocation .":".$destlibname.":".$destlibsystem.":".$itemavailtext.":".$itemcallnum.":".$itemlocallocation.":".$destemail.":".$destill."'><strong>".$destlibname."</strong> (".$destlibsystem."), Availability: $itemavailtext, Call Number:$itemcallnum  </br></div>";
                $loccount=$loccount+1;
            } elseif ($destfail == 1) {
                //not showing fail code 1 to end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
            } else {
                //will show other error to inform end user
                $deadlibraries[] = "<div class='grayout'>$destlibname ($destlibsystem), $failmessage</div>";
                echo "<!-- Holding location failed checks. --> \n";
            }
        }//end foreach $recordssSESLC
    }//end if cat type koha
} #End generic handler
echo "</select>";
foreach ($deadlibraries as $line) {
    echo $line;
}
if ($loccount > 0) {
    echo "<br><input type=Submit value=Submit> ";
#If we have no locations don't show submit and display error
} else {
    echo "<br><b>Sorry, no available library to route your request at this time.</b>  <a href='https://eform.cdlc.org'>Would you like to try another search ?</a>";
}
echo "</form>";
?>
