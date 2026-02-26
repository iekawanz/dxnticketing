<?php
//***************************************************
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'MailerComp/autoload.php';

$smtpserver="mail.dxnnwn.com";
$smtpserverport=587;
$sendusername="admin@dxnnwn.com";
$sendpassword="Steven9871512";
$smtpusessl=true;


//****************************************************
//*** AUTO EMAIL ***
//****************************************************
function SendEmail($strTo,$strSubject,$strBody){
 	global $smtpserver,$smtpserverport,$sendusername,$sendpassword,$smtpusessl;
	$mail = new PHPMailer(true);
	try {
		$mail->isSMTP();
		$mail->SMTPDebug = 4;
		$mail->Host=$smtpserver;
		$mail->SMTPAuth=$smtpusessl;
		$mail->Username=$sendusername;
		$mail->Password=$sendpassword;
		$mail->SMTPSecure='tls';
		$mail->Port=$smtpserverport;
		//***************************************************************
		$mail->setFrom($sendusername);
		$mail->addAddress($strTo);
		$mail->addReplyTo($sendusername);
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject=$strSubject;
		$mail->Body=$strBody;
		$mail->AltBody=$strBody;

		// Send the email
		$mail->send();
	} catch (Exception $e) {
	  echo "Mailer Error: {$mail->ErrorInfo}";	
	}
}

SendEmail("foo_cheewah@dxn2u.com","Hello There","Hello Body");
?>