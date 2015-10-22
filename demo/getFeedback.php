<?php

/**
 * @file
 * Get the Sermepa feedback from GET + POST parameters.
 */

  // Load settings and class.
  include_once "settings.php";

  include_once "../src/SermepaException.php";
  include_once "../src/SermepaInterface.php";
  include_once "../src/Sermepa.php";

  use facine\Payment\Sermepa;

  try {
    // Create a new instance and initialize it.
    $gateway = new Sermepa($settings['titular'], $settings['merchantCode'], $settings['terminal'], $settings['merchantPassword'], $settings['environment']);

    // Get response data.
    if ($feedback = $gateway->getFeedback()) {
      // Check if the signatures are valid.
      if ($gateway->checkFeedback($feedback)) {
        // Load the payment from ???? and store the necessary values.
        $payment_id = $gateway->getFeedbackValue('Ds_MerchantData');

        $response_code = (int) $gateway->getFeedbackValue('Ds_Response');
        if ($response_code <= 99) {
          // Transaction valid. Save your data here.
          $transaction_remote_id = $gateway->getFeedbackValue('Ds_AuthorisationCode');
          $transaction_message = $gateway->handleResponse($response_code);
        }
        else {
          // Transaction no valid. Save your data here.
          $transaction_message = $gateway->handleResponse($response_code);
        }
      }
      else {
        // Bad feedback response.
      }
    }
  }
  catch (SermepaException $e) {
    echo $e;
  }
