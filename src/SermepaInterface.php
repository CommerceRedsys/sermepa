<?php

/**
 * @file
 * Contains \facine\Payment\SermepaInterface.
 */

namespace facine\Payment;

interface SermepaInterface {
  /**
   * Static constructor/factory.
   *
   * @param string $titular
   *   This field will display to the holder on the screen confirmation of
   *   purchase.
   *
   * @param string $merchant_code
   *   FUC Code assigned to commerce.
   *
   * @param string $merchant_terminal
   *   Terminal number will be assigned your bank. 3 is considered the maximum
   *   length.
   *
   * @param string $merchant_password
   *   Commerce SHA256 password.
   *
   * @param string $environment
   *   Environment: live, test or an override url.
   *
   * @param array $options
   *   (Optional) An associative array of additional options, with the following
   *   elements:
   *   - amount: To Euros the last two places are considered decimals.
   *   - authorisation_code: Represents the authorization code necessary to
   *       identify a recurring transaction returns successively in subsequent
   *       recurring transactions.
   *   - charge_expiry_date: Format yyyy-mm-dd date for recurring transactions.
   *   - consumer_language: The value 000, indicating that there isn't
   *       determined the customer's language.
   *   - currency: Numeric currency code.
   *   - date_frecuency: Frequency in days for recurring transactions, recurring
   *       deferred.
   *   - merchant_data: Field for commerce to be included in the data sent by
   *       the "on-line" answer to trade if you have chosen this option.
   *   - merchant_name: Commerce name will appear on the ticket that the client.
   *   - merchant_url: URL of commerce that will receive a post with transaction
   *       data.
   *   - order: Order identifier. The first 4 digits must be numeric.
   *   - product_description: This field will display to the holder on the
   *       screen confirmation of purchase.
   *   - sum_total: Represents the sum of the amounts of installments. The latter
   *       two are considered decimal positions.
   *   - transaction_date: Format yyyy-MM-dd. Represents the date of subsequent
   *       recurrent operation is required to identify the transaction in
   *       successive returns from continuing operations.
   *   - transaction_type: What type of transaction it is.
   *   - url_ko: If you send will be used as ignoring the configured URLKO the
   *       administration module if you have it.
   *   - url_ok: If you send will be used as ignoring the configured URLOK the
   *       administration module if you have it.
   *
   * @return \facine\Payment\Sermepa
   *
   * @throws \facine\Payment\SermepaException
   */
  public static function configure($titular, $merchant_code, $merchant_terminal, $merchant_password, $environment, $options = array());

  /**
   * Compose the merchant parameters.
   *
   * @return mixed
   *   Base64 encoded JSON parameters or FALSE.
   */
  public function composeMerchantParameters();

  /**
   * Compose the merchant signature.
   *
   * @return string
   *   Base64 encoded signature.
   */
  public function composeMerchantSignature();

  /**
   * Compose the merchant signature from feedback values.
   *
   * @param string $encoded_parameters
   *   The feedback Ds_MerchantParameters.
   *
   * @return string
   *   Base64 encoded signature.
   */
  public function composeMerchantSignatureFromFeedback($encoded_parameters);

  /**
   * Get the Sermepa feedback from GET + POST parameters.
   *
   * @return array
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *   Returns FALSE if the Ds_SignatureVersion parameter is missing
   *   (indicating missing or invalid Sermepa feedback).
   */
  public function getFeedback();

  /**
   * Check if callback feedback is valid.
   *
   * @param array $feedback
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *
   * @return boolean
   *   Boolean indicating whether or not the transaction was valdiated.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function checkFeedback($feedback);

  /**
   * Handle the response of the payment transaction.
   *
   * Messages from "Manual de integración con el TPV Virtual para comercios con
   *  conexión por Redirección" v1.0 - 10/06/2015.
   *
   * @param integer $response
   *   The response feedback code.
   *
   * @return string
   *   The handle response message.
   */
  public function handleResponse($response = NULL);

  /**
   * Get all available languages.
   *
   * @return array
   *   Return an array with all available languages.
   */
  public static function getAvailableConsumerLanguages();

  /**
   * Get all available currencies.
   *
   * @return array
   *   Return an array with all available currencies.
   */
  public static function getAvailableCurrencies();

  /**
   * Get all available environments.
   *
   * @return array
   *   Return an array with all available environments.
   */
  public static function getAvailableEnvironments();

  /**
   * Get all available transaction types.
   *
   * @return array
   *   Return an array with all available transaction types.
   */
  public static function getAvailableTransactionTypes();

  /**
   * Setter for Sermepa::DsMerchantAmount property.
   *
   * @param integer $amount
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setAmount($amount);

  /**
   * Getter for Sermepa::DsMerchantAmount property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getAmount();

  /**
   * Setter for Sermepa::DsMerchantAuthorisationCode property.
   *
   * @param integer $authorisation_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setAuthorisationCode($authorisation_code);

  /**
   * Getter for Sermepa::DsMerchantAuthorisationCode property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getAuthorisationCode();

  /**
   * Setter for Sermepa::DsMerchantChargeExpiryDate property.
   *
   * @param string $charge_expiry_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setChargeExpiryDate($charge_expiry_date);

  /**
   * Getter for Sermepa::DsMerchantChargeExpiryDate property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getChargeExpiryDate();

  /**
   * Setter for Sermepa::DsMerchantConsumerLanguage property.
   *
   * @param integer $consumer_language
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setConsumerLanguage($consumer_language);

  /**
   * Getter for Sermepa::DsMerchantConsumerLanguage property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getConsumerLanguage();

  /**
   * Setter for Sermepa::DsMerchantCurrency property.
   *
   * @param integer $currency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setCurrency($currency);

  /**
   * Getter for Sermepa::DsMerchantCurrency property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getCurrency();

  /**
   * Setter for Sermepa::DsMerchantDateFrecuency property.
   *
   * @param string $date_frecuency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setDateFrecuency($date_frecuency);

  /**
   * Getter for Sermepa::DsMerchantDateFrecuency property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getDateFrecuency();

  /**
   * Setter for Sermepa::DsMerchantMerchantCode property.
   *
   * @param integer $merchant_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setMerchantCode($merchant_code);

  /**
   * Getter for Sermepa::DsMerchantMerchantCode property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getMerchantCode();

  /**
   * Setter for Sermepa::DsMerchantMerchantData property.
   *
   * @param string $merchant_data
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setMerchantData($merchant_data);

  /**
   * Getter for Sermepa::DsMerchantMerchantData property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantData();

  /**
   * Setter for Sermepa::DsMerchantMerchantName property.
   *
   * @param string $merchant_name
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setMerchantName($merchant_name);

  /**
   * Getter for Sermepa::DsMerchantMerchantName property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantName();

  /**
   * Setter for Sermepa::DsMerchantPassword property.
   *
   * @param string $merchant_password
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setMerchantPassword($merchant_password);

  /**
   * Getter for Sermepa::DsMerchantMerchantSignature property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantPassword();

  /**
   * Setter for Sermepa::DsMerchantMerchantURL property.
   *
   * @param string $merchant_url
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setMerchantURL($merchant_url);

  /**
   * Getter for Sermepa::DsMerchantMerchantURL property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantURL();

  /**
   * Setter for Sermepa::DsMerchantOrder property.
   *
   * @param string $order
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setOrder($order);

  /**
   * Getter for Sermepa::DsMerchantOrder property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getOrder();

  /**
   * Setter for Sermepa::DsMerchantProductDescription property.
   *
   * @param string $product_description
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setProductDescription($product_description);

  /**
   * Getter for Sermepa::DsMerchantProductDescription property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getProductDescription();

  /**
   * Setter for Sermepa::DsMerchantSumTotal property.
   *
   * @param integer $sum_total
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setSumTotal($sum_total);

  /**
   * Getter for Sermepa::DsMerchantSumTotal property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getSumTotal();

  /**
   * Setter for Sermepa::DsMerchantTerminal property.
   *
   * @param integer $terminal
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setTerminal($terminal);

  /**
   * Getter for Sermepa::DsMerchantTerminal property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getTerminal();

  /**
   * Setter for Sermepa::DsMerchantTitular property.
   *
   * @param string $titular
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setTitular($titular);

  /**
   * Getter for Sermepa::DsMerchantTitular property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getTitular();

  /**
   * Setter for Sermepa::DsMerchantTransactionDate property.
   *
   * @param string $transaction_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setTransactionDate($transaction_date);

  /**
   * Getter for Sermepa::DsMerchantTransactionDate property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getTransactionDate();

  /**
   * Setter for Sermepa::DsMerchantTransactionType property.
   *
   * @param mixed $transaction_type
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setTransactionType($transaction_type);

  /**
   * Getter for Sermepa::DsMerchantCurrency property.
   *
   * @return mixed
   *   Return the requested property.
   */
  public function getTransactionType();

  /**
   * Setter for Sermepa::DsMerchantUrlKO property.
   *
   * @param string $url_ko
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setUrlKO($url_ko);

  /**
   * Getter for Sermepa::DsMerchantUrlKO property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getUrlKO();

  /**
   * Setter for Sermepa::DsMerchantUrlOK property.
   *
   * @param string $url_ok
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setUrlOK($url_ok);
  /**
   * Getter for Sermepa::DsMerchantUrlOK property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getUrlOK();
  /**
   * Getter for Sermepa::feedbackParameters property.
   *
   * @param string $key
   *   The array key value to get.
   *
   * @return string
   *   Return the requested array value.
   */
  public function getFeedbackValue($key);
  /**
   * Setter for Sermepa::environment property.
   *
   * @param string $environment
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \facine\Payment\SermepaException
   */
  public function setEnvironment($environment);
  /**
   * Getter for Sermepa::environment property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getEnvironment();

  /**
   * Getter for Sermepa::SERMEPA_DS_SIGNATUREVERSION constant.
   *
   * @return string
   *   Return the requested constant.
   */
  public function getSignatureVersion();
}
