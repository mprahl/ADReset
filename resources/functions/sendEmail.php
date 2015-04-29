<?php
    require_once(RESOURCE_DIR . 'classes/PHPMailer/PHPMailerAutoload.php');
    

    function sendEmail($to, $subject, $body, $showExceptions = false) {
        if (isset($to) && isset($subject) && isset($body)) {

            if ($systemSettings = new SystemSettings()) {
                if ($emailSettings = $systemSettings->getEmailSettings()) {
                    if (isset($emailSettings['fromEmail']) && isset($emailSettings['fromName']) && isset($emailSettings['username']) && isset($emailSettings['password']) && isset($emailSettings['server']) && isset($emailSettings['port']) && isset($emailSettings['encryption'])) {
                        $mail = new PHPMailer($showExceptions);
                        $mail->isSMTP();
                        $mail->Host = $emailSettings['server'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $emailSettings['username'];
                        $mail->Password = $emailSettings['password'];

                        if ($emailSettings['encryption'] == 'TLS') {
                            $mail->SMTPSecure = 'tls';
                        }
                        elseif ($emailSettings['encryption'] == 'SSL') {
                            $mail->SMTPSecure = 'ssl';
                        }
                        
                        $mail->Port = $emailSettings['port'];
                        $mail->From = $emailSettings['fromEmail'];
                        $mail->FromName = $emailSettings['fromName'];
                        $mail->addAddress($to);
                        $mail->addReplyTo($emailSettings['fromEmail'], $emailSettings['fromName']);
                        $mail->isHTML(true);
                        $mail->Subject = $subject;
                        $mail->Body = $body;

                        if($mail->send()) {
                            $systemSettings = null;
                            return true;
                        }
                    }
                }
            }
        }

        $systemSettings = null;
        return false;
    }
