<?php
/* -------------------------
   --- Bulk email Sender ---
   ------------------------- */
/*
This is a basic example of a real world PHP process for sending to bulk users using Sendgrid APIv3. 
It is currently untested but should point you in the right direction.
N.B. I have had to add SendGrid\ to objects to reference this correctly. You may need to remove these.
*/


// This requires the official Sendgrid API V3 Helper available at Sendgrid or via composer.

// If you are using Composer (recommended)
require 'vendor/autoload.php';

// If you are not using Composer
// require("path/to/sendgrid-php/sendgrid-php.php");

/* ------------------
   --- Get Emails ---
   ------------------ */
// Normally you would grab email addresses from your db. We are just hardocding these
$emails = 'test@test.com;testy.McTestFace@test.com;another.test@test.com';
$arrSendTo = explode(';', $emails);
$status = '';
/* ------------------
   --- Send Email ---
   ------------------ */
$apiKey    =  '{your key goes here}';
$sg = new \SendGrid($apiKey);

$mail = new SendGrid\Mail();
$email = new SendGrid\Email("Emergency Management", "weather.alerts@.gov.au");
$mail->setFrom($email);
$mail->setSubject($testMsg ."Emergency Alert: -district- for -date-"); // replaced below using substitution

$personalization = new SendGrid\Personalization();
$email = new SendGrid\Email("Emergency Management", "weather.alerts@.gov.au"); // It appears you need a "to" address for some reason.
$personalization->addTo($email);

/* -----------------------
   --- Add Email addrs ---
   ----------------------- */
// You can only add 999 emails per email. Add a loop for more. 
foreach ($arrSendTo as $emailaddr) {
    $email = new SendGrid\Email(null, $emailaddr); // You can use their real name instead of Null if you wish.
    $personalization->addBcc($email);
}


/* --------------------------------
   --- Use Template/substituion ---
   -------------------------------- */
// We are using the "Transactional template" from Sendgrid which is an excellent way to store your body html and then substitute it
$personalization->addSubstitution("-district-", "Central Forecast"); // Replace key words in your template and subject using this
$personalization->addSubstitution("-date-", "27/02/18");
$mail->setTemplateId("d12345-3885-4606-bba5-57cf8d12bd4f"); // The iD of your template
$mail->addCategory("Windchill"); // In case you want to do some analysis later on.
$mail->addCategory('Central');
$reply_to = new SendGrid\ReplyTo("weather.alerts@.gov.au"); // just enfocing who to annoy with replies.
$mail->setReplyTo($reply_to); 
$mail->addPersonalization($personalization);
$response = $sg->client->mail()->send()->post( $mail); // You've just sent the emails 

$strStatus  = $response->statusCode();
//$strID      = getMessageId($response->headers());  // WIP -doesn't work as expected.
if ($strStatus =='202') {
    $status .= 'Status: processed (code 202) '; // .parse_str($strID);
} else {
    $status .= 'Status: Error (code ' .$strStatus .')';
}