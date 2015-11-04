<?php

/**
 * @file
 * Example of use.
 */
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>:: SERMEPA payment gateway Demo ::</title>
    <style type="text/css">
      body {
          font: 100%/1.4 Verdana, Arial, Helvetica, sans-serif;
          background-color: #42413C;
          margin: 0;
          padding: 0;
          color: #000;
      }
      h1, h2, h3, h4, h5, h6, p {
          margin-top: 0;
          padding-right: 15px;
          padding-left: 15px;
      }
      a:link {
          color: #42413C;
          text-decoration: underline;
      }
      a:visited {
          color: #6E6C64;
          text-decoration: underline;
      }
      a:hover, a:active, a:focus {
          text-decoration: none;
      }
      .container {
          width: 960px;
          background-color: #FFF;
          margin: 0 auto;
      }
      .content {
          padding: 10px 0;
          overflow-x: auto;
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="content">
<?php
  // Load settings and class.
  include_once "settings.php";

  include_once "../src/SermepaException.php";
  include_once "../src/SermepaInterface.php";
  include_once "../src/Sermepa.php";

  use CommerceRedsys\Payment\Sermepa;

  try {
    // Create a new instance and initialize it.
    $gateway = new Sermepa($settings['name'], $settings['merchantCode'], $settings['terminal'], $settings['merchantPassword'], $settings['environment']);

    // Load the payment from ???? and set the necessary values.
    $amount = 15050;
    $currency = 978;
    $payment_id = 1;
    $product_description = 'My example!';
    $consumer_language = '001';
    $transaction_type = 0;
    $feedback_url = 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . 'getFeedback.php';
    $ko_url = 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . 'ko.php';
    $ok_url = 'http://'. $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . 'ok.php';

    $gateway->setAmount($amount)
            ->setCurrency($currency)
            ->setOrder(substr(date('ymdHis') . 'Id' . $payment_id, -12, 12))
            ->setProductDescription($product_description)
            ->setConsumerLanguage($consumer_language)
            ->setMerchantData($payment_id)
            ->setTransactionType($transaction_type)
            ->setMerchantURL($feedback_url)
            ->setUrlKO($ko_url)
            ->setUrlOK($ok_url);

    // Get the trasaction fields for the sermepa form.
    $parameters = $gateway->composeMerchantParameters();
    if ($parameters) {
      $languages = $gateway->getAvailableConsumerLanguages();
      $currencies = $gateway->getAvailableCurrencies();
      $transaction_types = $gateway->getAvailableTransactionTypes();
      $output = '        <h1>Payment data!</h1>';
      $output .= '<p>';
      $output .= 'Environment: ' . $gateway->getEnvironment() . '<br />';
      $output .= 'Order: ' . $gateway->getOrder() . '<br />';
      $output .= 'Amount: ' . number_format($amount / 100, 2, ',', '') . '<br />';
      $output .= 'Currency: ' . $currencies[$currency] . '<br />';
      $output .= 'Payment identifier: ' . $payment_id . '<br />';
      $output .= 'Product description: ' . $product_description . '<br />';
      $output .= 'Consumer language: ' . $languages[$consumer_language] . '<br />';
      $output .= 'Transaction type: ' . $transaction_types[$transaction_type] . '<br />';
      $output .= 'Feedback URL: ' . $feedback_url . '<br />';
      $output .= 'KO URL: ' . $ko_url . '<br />';
      $output .= 'OK URL: ' . $ok_url . '<br />';
      $output .= '</p>';
      $output .= '<h1>Fields to send!</h1>';
      $output .= '<form action="' . $gateway->getEnvironment() . '" method="POST" id="' . $gateway->getOrder() . '">';
      $output .= '<p>';
      $output .= 'Ds_Merchant_SignatureVersion: ' . $gateway->getSignatureVersion() . '<br /><br />';
      $output .= 'Ds_Merchant_MerchantParameters: ' . $parameters . '<br /><br />';
      $output .= 'Ds_Merchant_Signature: ' . $gateway->composeMerchantSignature() . '<br /><br />';
      $output .= '<p>';
      $output .= '<input type="hidden" name="Ds_SignatureVersion" value="' . $gateway->getSignatureVersion() . '">';
      $output .= '<input type="hidden" name="Ds_MerchantParameters" value="' . $parameters . '">';
      $output .= '<input type="hidden" name="Ds_Signature" value="' . $gateway->composeMerchantSignature() . '">';
      $output .= '<input type="submit" value="Send">';
      $output .= '</p>';
      $output .= '</p>';
      $output .= '</form><br />';
    }
    else {
      $output = '        <h1>Error</h1><p>Failed collecting all information necessary to send to Sermepa.</p><p>Please check your settings and/or data.</p><br />';
    }

    echo $output;
  }
  catch (SermepaException $e) {
    echo $e;
  }
?>
      </div>
    </div>
  </body>
</html>
