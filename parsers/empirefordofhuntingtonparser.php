<?php

require_once __DIR__ . '/src/empirefordofhuntingtonparser.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

set_time_limit(0);

function main()
{
    $config = require __DIR__ . '/config.php';

    $parser = new EmpireFordOfHuntingtonParser($config['proxy']);

    try {
        $carsInfo = $parser->getInfoCars();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        sendMail($errorMessage);
        throw new Exception('Error: ' .  $errorMessage);
    }

    $fieldsCsvFile =  [
        'Title',
        'Price',
        'Mileage',
        'Image link',
        'VIN',
        'link_template',
        'Brand',
        'Color',
        'Model',
        'Year',
        'Condition',
        'Google product category',
        'ID',
        'store_code',
        'vehicle_fulfillment(option:store_code)',
    ];

    $todaysDate = date("Y-m-d");

    $carsInfoCsv = fopen(dirname(__FILE__) . "/results/empirefordofhuntingtonparser_{$todaysDate}.csv", 'w');

    if (!$carsInfoCsv) {
        throw new Exception('File csv not found.');
    }

    fputcsv($carsInfoCsv, $fieldsCsvFile);

    foreach ($carsInfo as $carInfo) {
        fputcsv($carsInfoCsv, $carInfo);
    }

    fclose($carsInfoCsv);
}

function sendMail($errorMessage)
{
    $mail = new PHPMailer(true);

    $config = require __DIR__ . '/config.php';
    $settingsSmtp = $config['smtp'];

    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $settingsSmtp['host'];
    $mail->SMTPSecure = $settingsSmtp['secure'];
    $mail->Port = $settingsSmtp['port'];
    $mail->Username = $settingsSmtp['username'];
    $mail->Password = $settingsSmtp['password'];

    $fromEmail = $config['from-email'];
    $mail->setFrom($fromEmail['address'], $fromEmail['name']);

    $addressesToSend = $config['parsing-error-emails'];

    foreach ($addressesToSend as $addressToSend) {
        $mail->addAddress($addressToSend);
    }

    $mail->isHTML(true);
    $mail->Subject = "Parser error";
    $mail->Body = "<p>The parser encountered the following error: <b> " . $errorMessage . " in empirefordofhuntingtonparser.php</b></p>";

    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}

main();
