<?php

  // Load settings and class.
  include_once "settings.php";
  include_once "../src/Sermepa.php";

  // Create a new instance.
  $gateway = new Sermepa();

  // Initialize with our settings.
  $gateway->setTitular($settings['titular'])
          ->setMerchantCode($settings['merchantCode'])
          ->setMerchantSignature($settings['merchantSignature'])
          ->setTerminal($settings['terminal'])
          ->setEncryptionMethod($settings['encryptionMethod'])
          ->setEnvironment($settings['environment']);

  if ($feedback = $gateway->getFeedback()) {
    // Load the payment from ???? and set the necessary values.
    $payment_id = $feedback['Ds_MerchantData'];
    $amount = 15050;

    if ($gateway->checkFeedback($feedback, $amount)) {
      // Transaction valid. Save your data here.
      $transaction_remote_id = $feedback['Ds_AuthorisationCode'];
      $transaction_message = $gateway->handleResponse($feedback['Ds_Response']);
    }
    else {
      // Transaction no valid. Save your data here.
      $transaction_message = $gateway->handleResponse($feedback['Ds_Response']);
    }
  }
