<?php 
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require '../phpmailer/src/Exception.php';
    require '../phpmailer/src/PHPMailer.php';
    require '../phpmailer/src/SMTP.php';

    require_once __DIR__ . '/../config/env.php';

    if(isset($_POST['send'])){

        session_start();

        // SMTP settings come from the environment (see .env.example); no
        // credential is hardcoded here. Port and secure mode keep their
        // historical defaults (465 / ssl) when not provided.
        $smtpHost   = getenv('SMTP_HOST');
        $smtpPort   = getenv('SMTP_PORT');
        $smtpSecure = getenv('SMTP_SECURE');
        $smtpUser   = getenv('SMTP_USER');
        $smtpPass   = getenv('SMTP_PASS');
        $mailFrom   = getenv('MAIL_FROM');
        $mailTo     = getenv('MAIL_TO');

        if ($smtpPort === false || $smtpPort === '') {
            $smtpPort = 465;
        }
        if ($smtpSecure === false || $smtpSecure === '') {
            $smtpSecure = 'ssl';
        }

        // Required settings: fail with a clear message instead of attempting a
        // connection with empty credentials.
        if ($smtpHost === false || $smtpHost === ''
            || $smtpUser === false || $smtpUser === ''
            || $smtpPass === false || $smtpPass === ''
            || $mailFrom === false || $mailFrom === ''
            || $mailTo === false || $mailTo === '') {
            die('Mail configuration is incomplete: set SMTP_HOST, SMTP_USER, SMTP_PASS, MAIL_FROM and MAIL_TO.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPass;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = (int) $smtpPort;

        $subject = 'Subject: '.$_POST['subject'] . ' From : '.$_POST['email'];

        $message = $_POST['message']. ' FROM : ' .$_POST['email'];

        // Envelope sender stays the configured identity; the visible From and the
        // message text keep the visitor's address, exactly as before.
        $mail->Sender = $mailFrom;
        $mail->setFrom($_POST['email']);

        $mail->addAddress($mailTo);

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $message;

        $mail->send();


        header('Location: ../index.php?email_snt=Sent Successfully');

    }

?>
