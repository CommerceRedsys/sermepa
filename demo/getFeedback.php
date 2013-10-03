<?php

/**
 * @file
 * Get the Sermepa feedback from GET + POST parameters.
 */

  // Load settings and class.
  include_once "settings.php";
  include_once "../src/Sermepa.php";

  // Create a new instance and initialize it.
  $gateway = new Sermepa($settings['titular'], $settings['merchantCode'], $settings['terminal'], $settings['merchantSignature'], $settings['environment'], $settings['encryptionMethod']);

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
