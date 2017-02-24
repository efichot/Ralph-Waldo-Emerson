<?php
//require composer
require("vendor/autoload.php");
date_default_timezone_set("Europe/Paris");
// use Monolog\Logger;
// use Monolog\Handler\StreamHandler;

// $log = new Logger('name');
// $log->pushHandler(new StreamHandler('app.log', Logger::WARNING));
// $log->addWarning('Foo');

//instantiate slim app
$app = new \Slim\Slim([
    "view" => new \Slim\Views\Twig()
]);

//debug
$view = $app->view();
$view->parserOptions = [
    "debug" => true
];
$view->parserExtensions = [
    new \Slim\Views\TwigExtension()
];

//get routes
$app->get("/", function() use($app) {
    $app->render("about.twig");
})->name("home");

$app->get("/contact", function() use($app) {
    $app->render("contact.twig");
})->name("contact");

//post routes
$app->post("/contact", function() use($app) {
    $name = $app->request->post("name");
    $email = $app->request->post("email");
    $msg = $app->request->post("msg");

    if (!$name || !$email || !$msg)
    {
        //Redirect the user
        $app->redirect("/contact");
    }
    $cleanName = filter_var($name, FILTER_SANITIZE_STRING);
    $cleanEmail = filter_var($email, FILTER_SANITIZE_EMAIL);
    $cleanMsg = filter_var($msg, FILTER_SANITIZE_STRING);

    //transporter
    $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
    //mailer
    $mailer = \Swift_Mailer::newInstance($transport);
    //compose the message object
    $message = \Swift_Message::newInstance();
    $message->setSubject("Email from our website");
    $message->setFrom([
        $cleanEmail => $cleanName
    ]);
    $message->setTo([
        "fichotetienne@gmail.com"
    ]);
    $message->setBody($cleanMsg);
    //send the object $message
    if ($result = $mailer->send($message))
    {
        //message send thx
        $app->redirect("/");
    }
    else
    {
        //failed
        //log that there an error
        $app->redirect("/contact");
    }
    /*
    $mail = new PHPMailer;
    //Email verif
    if (!$mail->ValidateAddress($cleanEmail))
    {
        echo "Invalide Email address" . $mail->ErrorInfo;
    }
    $mail->setFrom($cleanEmail, $cleanName);
    $mail->addAddress("fichotetienne@gmail.com", "Etienne");
    $mail->isHTML(false);
    $mail->Subject = "Mail from my Website";
    $mail->Body = $cleanMsg;
    $mail->send();*/
});

//run app
$app->run();
