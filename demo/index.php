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
      }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="content">
<?php
  // Load settings and class.
  include_once "settings.php";
  include_once "../src/Sermepa.php";

  // Create a new instance and initialize it.
  $gateway = new Sermepa($settings['titular'], $settings['merchantCode'], $settings['terminal'], $settings['merchantSignature'], $settings['environment'], $settings['encryptionMethod']);

  // Load the payment from ???? and set the necessary values.
  $amount = 15050;
  $currency = 978;
  $payment_id = 1;
  $product_description = 'My example!';
  $consumer_language = '001';

  $gateway->setAmount($amount)
          ->setCurrency($currency)
          ->setOrder(substr(date('ymdHis') . 'Id' . $payment_id, -12, 12))
          ->setProductDescription($product_description)
          ->setConsumerLanguage($consumer_language)
          ->setMerchantData($payment_id);

  // Get the trasaction fields for the sermepa form.
  if ($fields = $gateway->getFields()) {
    $languages = $gateway->getAvailableConsumerLanguages();
    $currencies = $gateway->getAvailableCurrencies();
    $output = '        <h1>Payment data!</h1>';
    $output .= '<p>';
    $output .= 'Amount: ' . number_format($amount / 100, 2, ',', '') . '<br />';
    $output .= 'Currency: ' . $currencies[$currency] . '<br />';
    $output .= 'Payment identifier: ' . $payment_id . '<br />';
    $output .= 'Product description: ' . $product_description . '<br />';
    $output .= 'Consumer language: ' . $languages[$consumer_language] . '<br />';
    $output .= '</p>';
    $output .= '<h1>Fields to send!</h1>';
    $output .= '<form action="' . $gateway->getEnvironment() . '" method="post" id="' . $gateway->getOrder() . '">';
    $output .= '<p>';
    foreach ($fields as $key => $value) {
      $output .= '<input type="hidden" name="' . $key . '" value="' . $value . '">';
      $output .= $key . ': ' . $value . '<br />';
    }
    $output .= '<p>';
    $output .= '<input type="submit" value="Send">';
    $output .= '</p>';
    $output .= '</p>';
    $output .= '</form><br />';
  }
  else {
    $output = '        <h1>Error</h1><p>Failed collecting the information necessary to send to Sermepa.</p><p>Please check your settings.</p><br />';
  }

  echo $output;
?>
      </div>
    </div>
  </body>
</html>
