<?php
    //Marios Hadjimichael
    //11 June 2012
	//This PHP Library uses CYTAmobile-Vodafone's API
	//to send SMS messages in Cyprus
	
	/*LICENSE: The MIT License (MIT)
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE. */
	
	//SETUP
	//>You need to register for an account @ https://www.cyta.com.cy
	// and activate the WebSMS Api in order to get your "Secret Key"
	//>You need to have PHP XML and PHP cURL extensions installed
	
	//USAGE
	//Include this file in your project
	//and call function WEBSMS_SEND($WEBSMS_RECIPIENTS, $WEBSMS_MESSAGE);
	//@WEBSMS_RECIPIENTS: A phone number OR an array of phone numbers to send the message to
	//@WEBSMS_MESSAGE: The message you want to send

	//EXAMPLE
	//	WEBSMS_SEND("99123123","This is the message")
	
    /*************************************************************************
	CONFIG
    **************************************************************************/

    $WEBSMS_USERNAME = '<YOUR USERNAME>';
    $WEBSMS_SECRETKEY = '<YOUR SECRET KEY>'; //NOT password, SECRET KEY from API page

    date_default_timezone_set('Europe/Nicosia');

    //$WEBSMS_LANGUAGE = "EN"; //EL/EN
    //$WEBSMS_OUTPUT_RESULT = true; //DEFAULT: false (true/false -> Prints the RESULT)

    $WEBSMS_URL = "https://www.cytamobile-vodafone.com/cytamobilevodafone/dev/websmsapi/sendsms.aspx";
	
    /*************************************************************************
	YOU SHOULD NOT EDIT ANYTHING BELOW THIS LINE!
    **************************************************************************/

    //Check whether the configuration is OK
    //username and secretkey
    if(!isset($WEBSMS_USERNAME) || !isset($WEBSMS_SECRETKEY))
        die("ERROR: WEBSMS_USERNAME or WEBSMS_SECRETKEY NOT SET.");

    //If no language set, set to default
    if(!isset($WEBSMS_LANGUAGE))
        $WEBSMS_LANGUAGE = "EN";

    if(!isset($WEBSMS_OUTPUT_RESULT))
        $WEBSMS_OUTPUT_RESULT = false;

    /*WEBSMS_SEND: sends message to specified recipients
    @to: array containing recipients' mobiles (9xxxxxxx)
    @msg: Message to be sent (Not encoded)    
    */
    function WEBSMS_SEND($to, $msg){
        global $WEBSMS_USERNAME, $WEBSMS_SECRETKEY, $WEBSMS_LANGUAGE, $WEBSMS_URL, $WEBSMS_exitCodes, $WEBSMS_OUTPUT_RESULT;
        $queryxml = WEBSMS_getXML($WEBSMS_USERNAME, $WEBSMS_SECRETKEY, $to, $msg, $WEBSMS_LANGUAGE);
        $resultString = WEBSMS_postRequest($WEBSMS_URL, $queryxml);

        $resultXML = new SimpleXMLElement($resultString);

        $resultArr = array();
        foreach($resultXML->children() as $child){
            $resultArr[$child->getName()]  = $child."";
        }
            

        if($WEBSMS_OUTPUT_RESULT == true){
            echo "WEB SMS API Result<br />";
            echo "Status Code: " . $resultArr['status'] . "<br />";
            echo "Status Message: " . $WEBSMS_exitCodes[$resultArr['status']] . "<br />";

            if($resultArr['status'] == '0')
                echo "LOT: " . $resultArr['lot'];
        }        

        return $resultArr['status'];
    }

    /*getXML: returns the XML required as per CYTA (template 11 June 2012)
    @recipients: array of recipients' mobile numbers in format 9xxxxxxx
    @message: NON XML ENCODED message (it gets encoded here)
    @language: EN/EL (11 June 2012)
    */    

    function WEBSMS_getXML($username, $secretkey, $recipients, $message, $language){
        //Prepare the recipients part of the XML
        $countRecipients = count($recipients);
        $recipientsXML = "";
        for($i = 0; $i < $countRecipients; $i++)
            $recipientsXML .= "<m>$recipients[$i]</m>";

        $messageESCAPED = htmlspecialchars($message, ENT_QUOTES); //XML Encode the message


        //Create the XML
        return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
                   <websmsapi>
                       <version>1.0</version>
                       <username>$username</username>
                       <secretkey>$secretkey</secretkey>
                       <recipients>
                            <count>$countRecipients</count>
                            <mobiles>
                                $recipientsXML
                            </mobiles>
                        </recipients>
                        <message>$messageESCAPED</message>
                        <language>$language</language>
                    </websmsapi>";
    }


    /*postRequest: posts the REQUEST and returns the SERVER Result
    @URL: The URL to POST to
    @XML: The XML to be posted (as STRING)
    */

    function WEBSMS_postRequest($URL, $XML){
        $ch = curl_init($URL);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/xml; charset=\"utf-8\""));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$XML");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    
    //Based on WebSmsAPI Guide 11 June 2012
    $WEBSMS_exitCodes = array(
        "0" => "Send Sms success",
        "1" => "You are not allowed to use the service",
        "2" => "Generic send sms failure",      

       "10" => "User not found or Suspended cybee account/Invalid Secret Key",

        "11" => "configuration settings not found for Username",

        "12" => "Web Sms Api Suspended or Terms not accepted",

        "13" => "Client IP does not match expected IP",

        "19" => "Registered mobile number for username not found",

        "20" => "missing field values",

        "21" => "invalid username",

        "22" => "invalid characters in Recipieenst",

        "23" => "invalid characters in recipient count",

        "24" => "invalid language",

        "25" => "cybee recipients count and user entered count does not match",

        "26" => "Recipients list is bigger than allowed",

        "27" => "Invalid mobile number found",

        "28" => "Message length is bigger than allowed",

        "29" => "Unsupported Content Type",

        "30" => "Missing HTTP Post request body",

        "31" => "Max allowed sms messages per day threshold reached",

        "39" => "Invalid Version",

        "90" => "Exception",

        "91" => "Exception processing URL Encoded request",

        "92" => "Exception processing XML request",

        "93" => "Invalid XML request data"

    );    

?>
