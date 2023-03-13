<?php

require_once __DIR__ . '/src/empirevolvocarssmithtown.php';
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

set_time_limit(0);

function main()
{
    $config = require __DIR__ . '/config.php';

    $parser = new EmpireVolvoCarsSmithtown($config['proxy']);

    try {
        $carsInfo = $parser->getInfoCars();
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        sendMail($errorMessage);
        throw new Exception('Error: ' .  $errorMessage);
    }

    $fieldsCsvFile =  [
        'Condition',
        'Google product category',
        'store_code',
        'Vehicle_fulfillment(option:store_code)',
        'Brand',
        'Model',
        'Year',
        'Mileage',
        'Price',
        'Image link',
        'link_template',
        'VIN',
        'ID',
        'Color'
    ];

    $todaysDate = date("Y-m-d");

    $carsInfoCsv = fopen(dirname(__FILE__) . "/results/empirevolvocarssmithtown_{$todaysDate}.csv", 'w');

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
    $mail->Body = "<p>The parser encountered the following error: <b> " . $errorMessage . " in empirevolvocarssmithtown.php</b></p>";

    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}

main();
