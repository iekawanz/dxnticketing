<?php
//***************************************************
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'MailerComp/autoload.php';

function tep_email($emailTo, $emailFrom, $subject, $body, $type = 'html', $attachments = null)
{
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->Host = "mail.dxnnwn.com";
  $mail->Port = 587;
  $mail->Username = "admin@dxnnwn.com";
  $mail->Password = "Steven9871512";
  $mail->SMTPSecure = 'tls';

  $mail->CharSet = 'UTF-8';
  $mail->Encoding = 'base64';

  /*$mail->Host       = "smtp.gmail.com";
  $mail->Port       = 587;
  $mail->Username   = "aaronfoo86@gmail.com";
  $mail->Password   = "crxa glht gbde from";*/

  if (is_array($emailFrom)) {
    $mail->From = $emailFrom['email'];
    $mail->FromName = isset($emailFrom['name']) ? $emailFrom['name'] : '';
  } else {
    $mail->From = $emailFrom;
    $mail->FromName = '';
  }

  $mail->Subject = $subject;
  $body = htmlspecialchars_decode($body);
  $mail->Body = $body;
  $mail->AltBody = strip_tags($body);
  //$mail->AltBody    = "This is the body when user views in plain text format"; //Text Body

  //$mail->MsgHTML($body);

  if (is_array($emailTo)) {
    for ($i = 0; $i < count($emailTo); $i++) {
      if (is_array($emailTo[$i])) {
        $name = (isset($emailTo[$i]['name'])) ? $emailTo[$i]['name'] : '';
        $mail->AddAddress($emailTo[$i]['email'], $name);
      } else {
        $mail->AddAddress($emailTo[$i]);
      }
    }
  } else {
    $temp = explode(',', $emailTo);
    for ($i = 0; $i < count($temp); $i++) {
      $mail->AddAddress($temp[$i], '');
    }
  }

  if ($attachments != null) {
    if (is_array($attachments)) {
      $len = count($attachments);
      for ($i = 0; $i < $len; $i++) {
        $mail->AddAttachment($attachments[$i]['destPath'], $attachments[$i]['fileName']);
      }
    } else {
      $mail->AddAttachment($attachments);
    }
  }

  $mail->addCC('laszlo@kocso.com');
  $mail->addCC('krisztian@dxnshop.com');
  //$mail->addCC('steven@dxnshop.com');

  if ($type == 'html')
    $mail->IsHTML(true); // send as HTML

  if (!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
    return false;
  } else {
    return true;
  }
}

function tep_reg_email($emailTo, $emailFrom, $subject, $body, $type = 'html', $attachments = null)
{
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->Host = "mail.dxnnwn.com";
  $mail->Port = 587;
  $mail->Username = "admin@dxnnwn.com";
  $mail->Password = "Steven9871512";
  $mail->SMTPSecure = 'tls';

  /*$mail->Host       = "smtp.gmail.com";
  $mail->Port       = 587;
  $mail->Username   = "aaronfoo86@gmail.com";
  $mail->Password   = "crxa glht gbde from";*/

  if (is_array($emailFrom)) {
    $mail->From = $emailFrom['email'];
    $mail->FromName = isset($emailFrom['name']) ? $emailFrom['name'] : '';
  } else {
    $mail->From = $emailFrom;
    $mail->FromName = '';
  }

  $mail->Subject = $subject;
  $body = htmlspecialchars_decode($body);
  $mail->Body = $body;
  $mail->AltBody = strip_tags($body);
  //$mail->AltBody    = "This is the body when user views in plain text format"; //Text Body

  //$mail->MsgHTML($body);

  if (is_array($emailTo)) {
    for ($i = 0; $i < count($emailTo); $i++) {
      if (is_array($emailTo[$i])) {
        $name = (isset($emailTo[$i]['name'])) ? $emailTo[$i]['name'] : '';
        $mail->AddAddress($emailTo[$i]['email'], $name);
      } else {
        $mail->AddAddress($emailTo[$i]);
      }
    }
  } else {
    $temp = explode(',', $emailTo);
    for ($i = 0; $i < count($temp); $i++) {
      $mail->AddAddress($temp[$i], '');
    }
  }

  if ($attachments != null) {
    if (is_array($attachments)) {
      $len = count($attachments);
      for ($i = 0; $i < $len; $i++) {
        $mail->AddAttachment($attachments[$i]['destPath'], $attachments[$i]['fileName']);
      }
    } else {
      $mail->AddAttachment($attachments);
    }
  }

  if ($type == 'html')
    $mail->IsHTML(true); // send as HTML

  if (!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
    return false;
  } else {
    return true;
  }
}

function test_tep_email($emailTo, $emailFrom, $subject, $body, $type = 'html', $attachments = null)
{
  $mail = new PHPMailer();
  $mail->IsSMTP();
  $mail->SMTPAuth = true;
  $mail->Host = "mail.dxnnwn.com";
  $mail->Port = 587;
  $mail->Username = "admin@dxnnwn.com";
  $mail->Password = "Steven9871512";
  $mail->SMTPSecure = 'tls';

  /*$mail->Host       = "smtp.gmail.com";
  $mail->Port       = 587;
  $mail->Username   = "aaronfoo86@gmail.com";
  $mail->Password   = "crxa glht gbde from";*/

  if (is_array($emailFrom)) {
    $mail->From = $emailFrom['email'];
    $mail->FromName = isset($emailFrom['name']) ? $emailFrom['name'] : '';
  } else {
    $mail->From = $emailFrom;
    $mail->FromName = '';
  }

  $mail->Subject = $subject;
  $mail->AltBody = "This is the body when user views in plain text format"; //Text Body

  $mail->MsgHTML($body);

  if (is_array($emailTo)) {
    for ($i = 0; $i < count($emailTo); $i++) {
      if (is_array($emailTo[$i])) {
        $name = (isset($emailTo[$i]['name'])) ? $emailTo[$i]['name'] : '';
        $mail->AddAddress($emailTo[$i]['email'], $name);
      } else {
        $mail->AddAddress($emailTo[$i]);
      }
    }
  } else {
    $temp = explode(',', $emailTo);
    for ($i = 0; $i < count($temp); $i++) {
      $mail->AddAddress($temp[$i], '');
    }
  }

  if ($attachments != null) {
    if (is_array($attachments)) {
      $len = count($attachments);
      for ($i = 0; $i < $len; $i++) {
        $mail->AddAttachment($attachments[$i]['path'], $attachments[$i]['name']);
      }
    } else {
      $mail->AddAttachment($attachments);
    }
  }

  if ($type == 'html')
    $mail->IsHTML(true); // send as HTML

  if (!$mail->Send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
    return false;
  } else {
    return true;
  }
}