<?php
{

    function format_header_output($headers, &$original_to, &$headerDate)
    {
        $table = '<table><tbody class="labels"><tr><td colspan="2">Headers</td></tr></tbody><tbody>';
        foreach ($headers as $key => $value) {
            foreach ($value as $key_sub => $value_sub) {
                $table .= '<tr>';
                $table .= '<td>' . $key_sub . '</td>';
                $table .= '<td style="background-color:lightgrey">' . htmlspecialchars($value_sub) . '</td>';
                $table .= '</tr>';
                if ($key_sub == 'To') $original_to = $value_sub;
                if ($key_sub == 'Date') $headerDate = $value_sub;
            }
        }
        $table .= '</tbody></table>';
        return $table;
    }

    function parseRequestHeaders()
    {
        $secret = Null;
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) != 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
            if ($header == "X-Messagesystems-Webhook-Token") {
                $secret = $value;
            }
        }
        return $secret;
    }

    function Archive($verb)
    {
   
        $sender = parseRequestHeaders();
        if ($verb == "POST") {
               
            //
            // Initialize Fields and obtain ini settings if there; otherwise use the following defaults
            //
            $original_to = NULL; $headerDate = NULL;
            $parametersFile = "ArchiveParameters.ini";
            $paramonly_array = parse_ini_file( $parametersFile, true );
            $cat = "archive";
            if ($paramonly_array[$cat]["ArchiveDirectory"]) $ArchiveDirectory = $paramonly_array[$cat]["ArchiveDirectory"]; else $ArchiveDirectory = "ArchiveDir";
            if ($paramonly_array[$cat]["ArchiveLogName"]) $ArchiveLogName = $paramonly_array[$cat]["ArchiveLogName"]; else $ArchiveLogName = "archivelog.txt";
            if ($paramonly_array[$cat]["MaxArchiveLogSize"]) $MaxArchiveLogSize = $paramonly_array[$cat]["MaxArchiveLogSize"]; else $MaxArchiveLogSize = 25000000;  //defaulting on the cautious side 
            if ($paramonly_array[$cat]["DefaultTimeZone"]) $DefaultTimeZone = $paramonly_array[$cat]["DefaultTimeZone"]; else $DefaultTimeZone = "America/Los_Angeles";  //defaulting on the cautious side 
            date_default_timezone_set($DefaultTimeZone);
            $previewTimestamp = localtime(time(),true);
            $monthName = date('F', mktime(0, 0, 0, $previewTimestamp["tm_mon"] + 1, 10));
            $sendDateTime = ($previewTimestamp["tm_year"] + 1900) . "." . $monthName . "." . $previewTimestamp["tm_mday"] . "." . $previewTimestamp["tm_hour"] . "." . $previewTimestamp["tm_min"] . "." . $previewTimestamp["tm_sec"];
            
            $body = file_get_contents("php://input");
            $fields = json_decode($body, true);
            $rcpt_to = $fields['0']['msys']['relay_message']['rcpt_to'];
            $friendly_from = $fields['0']['msys']['relay_message']['friendly_from'];
            $subject = $fields['0']['msys']['relay_message']['content']['subject'];
            $headers = $fields['0']['msys']['relay_message']['content']['headers'];
            $html = $fields['0']['msys']['relay_message']['content']['html'];
            $text = $fields['0']['msys']['relay_message']['content']['text'];
            $email_rfc822 = $fields['0']['msys']['relay_message']['content']['email_rfc822'];
            
            $headers_html_ready = format_header_output($headers, $original_to, $headerDate);
            $DirectoryName = $ArchiveDirectory . "/" . $original_to . $sendDateTime;
            mkdir ($DirectoryName, 0755, true);
            
            // Start building the output
            // HTML Head
            $html_output = "<html><body><table><tr><td>To: " . $original_to . "</td></tr><tr><td>From: " . friendly_from . "</td></tr><tr><td>Subject: " . $subject . "</td></tr><tr><td><br><br><br>" . $html . "</td></tr></body></html>";
            $text_output = "To: " . $original_to . "\nFrom: " . friendly_from . "\nSubject: " . $subject . "\n\n\n" . $text;
            // Create our Archive Files
            $rawJSONSparkPostArchiveOutput = $DirectoryName . '/rawoutput.json';
            file_put_contents($rawJSONSparkPostArchiveOutput, $body, LOCK_EX); // backup of the raw JSON data event
            $fileOut = $DirectoryName . "/htmlOutput.htm";
            file_put_contents($fileOut, $html_output, LOCK_EX);
            $fileOut = $DirectoryName . "/textOutput.txt";
            file_put_contents($fileOut, $text_output, LOCK_EX);
            $fileOut = $DirectoryName . "/headers.htm";
            file_put_contents($fileOut, $headers_html_ready, LOCK_EX);
            $fileOut = $DirectoryName . "/rfc822.eml";
            file_put_contents($fileOut, $email_rfc822, LOCK_EX);
             
            $archive_output = sprintf("\n>>>>>To: %-50s From: %-50s Subject: %-200s HeaderTimeStamp: %-42s ArchiveTimeStamp: %-38s ArchiveDirectory: %s>>>>>", $original_to, $friendly_from, $subject, $headerDate, $sendDateTime, $DirectoryName);
            $Jfile = $ArchiveLogName;  
            $cycleSize = $MaxArchiveLogSize;
            $logSize = filesize($Jfile);
            
            file_put_contents($Jfile, $archive_output, LOCK_EX | FILE_APPEND);
            if (filesize($Jfile) > $cycleSize) { 
                    $timestamp = time();
                    $JRotate .= $ArchiveLogName . $timestamp . '.log'; //Use this to rotate and keep data
                    //$JRotate = $ArchiveLogName . "backup.log"; // Use this for simple one file backup rotation                                  
                    
                    if (! copy($Jfile, $JRotate)) {
                        file_put_contents($Jfile, "\n\nRotation Failure\n\n", LOCK_EX | FILE_APPEND);
                        } else {
                            file_put_contents($Jfile, "", LOCK_EX);
                        }
                    }
            }
       }

    
    // Main Body - Sort of
    $verb = $_SERVER['REQUEST_METHOD'];
    
    Archive($verb);
}
?>
