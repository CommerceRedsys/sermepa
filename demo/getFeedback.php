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

  use CommerceRedsys\Payment\Sermepa;

  try {
    // Create a new instance and initialize it.
    $gateway = new Sermepa($settings['name'], $settings['merchantCode'], $settings['terminal'], $settings['merchantPassword'], $settings['environment']);

    // Get response data.
    if (!$feedback = $gateway->getFeedback()) {
      // No feedback response.
      return;
    }

    $encoded_parameters = $feedback['Ds_MerchantParameters'];
    $decoded_parameters = $gateway->decodeMerchantParameters($encoded_parameters);

    $feedback_signature = $feedback['Ds_Signature'];
    $composed_signature = $gateway->composeMerchantSignatureFromFeedback($encoded_parameters);

    // Check if the signatures are valid.
    if ($feedback_signature != $composed_signature) {
    // Or...
    //if (!$gateway->areValidSignatures($feedback)) {
      // Signatures don't match.
      return;
    }

    // Load the payment from ???? and store the necessary values.
    $payment_id = $decoded_parameters['Ds_MerchantData'];

    $response_code = (int) $decoded_parameters['Ds_Response'];
    if ($response_code <= 99) {
      // Transaction valid. Save your data here.
      $transaction_remote_id = $decoded_parameters['Ds_AuthorisationCode'];
      $transaction_message = $gateway->handleResponse($response_code);
    }
    else {
      // Transaction no valid. Save your data here.
      $transaction_message = $gateway->handleResponse($response_code);
    }
  }
  catch (SermepaException $e) {
    echo $e;
  }
