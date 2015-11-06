<?php

/**
 * @file
 * Contains \CommerceRedsys\Payment\SermepaInterface.
 */

namespace CommerceRedsys\Payment;

interface SermepaInterface {
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
   * Get all available payment methods.
   *
   * @return array
   *   Return an array with all available payment methods.
   */
  public static function getAvailablePaymentMethods();

  /**
   * Get all available transaction types.
   *
   * @return array
   *   Return an array with all available transaction types.
   */
  public static function getAvailableTransactionTypes();

  /**
   * Get the Sermepa feedback from GET + POST parameters.
   *
   * @return array
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *   Returns FALSE if the Ds_SignatureVersion parameter is missing
   *   (indicating missing or invalid Sermepa feedback).
   */
  public static function getFeedback();

  /**
   * Get merchant code maxlength.
   *
   * @return integer
   *   Return merchant code maxlength.
   */
  public static function getMerchantCodeMaxLength();

  /**
   * Get merchant name maxlength.
   *
   * @return integer
   *   Return merchant name maxlenght.
   */
  public static function getMerchantNameMaxLength();

  /**
   * Get merchant password maxlength.
   *
   * @return integer
   *   Return merchant password maxlenght.
   */
  public static function getMerchantPasswordMaxLength();

  /**
   * Get merchant terminal maxlength.
   *
   * @return integer
   *   Return merchant terminal maxlenght.
   */
  public static function getMerchantTerminalMaxLength();

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
  public static function handleResponse($response = NULL);

  /**
   * Check if callback feedback signature is valid.
   *
   * @param array $feedback
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *
   * @return boolean
   *   Boolean indicating whether or not the transaction was valdiated.
   *
   * @throws \CommerceRedsys\Payment\SermepaException
   */
  public function validSignatures($feedback);

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
   * Decode Ds_MerchantParameters.
   *
   * @param string $encoded_parameters
   *   The encoded Ds_MerchantParameters.
   *
   * @return array
   *   An associative array of merchant parameters values:
   *   - Ds_Date: Transaction date (dd/mm/yyyy).
   *   - Ds_Hour: Transaction time (HH:mm).
   *   - Ds_Amount: Same of the transaction.
   *   - Ds_Currency: Same of the transaction.
   *   - Ds_Order: Same of the transaction.
   *   - Ds_MerchantCode: Same of the transaction.
   *   - Ds_Terminal: Assigned by Sermepa.
   *   - Ds_Response: Response values, see $this->handleResponse.
   *   - Ds_MerchantData: Optional sended from commerce form.
   *   - Ds_SecurePayment: 0 for no secure payment, 1 for secure.
   *   - Ds_TrasactionType: Trasaction type sended from commerce form.
   *   - Ds_Card_Country: (Optional) Country of issuance of the card that has
   *       tried to make the payment..
   *   - Ds_AuthorisationCode: (Optional) Assigned authorisation code.
   *   - Ds_ConsumerLanguage: (Optional) 0 indicates that has not been
   *       determined the customer's language..
   *   - Ds_Card_Type: (Optional) C for credit, D for debit.
   */
  public function decodeMerchantParameters($encoded_parameters);

  /**
   * Setter for Sermepa::DsMerchantAmount property.
   *
   * @param integer $amount
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * Setter for Sermepa::DsMerchantPaymentMethod property.
   *
   * @param string $payment_methods
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \CommerceRedsys\Payment\SermepaException
   */
  public function setPaymentMethod($payment_methods);

  /**
   * Getter for Sermepa::DsMerchantPaymentMethod property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getPaymentMethod();

  /**
   * Setter for Sermepa::DsMerchantSumTotal property.
   *
   * @param integer $sum_total
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * @throws \CommerceRedsys\Payment\SermepaException
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
   * Setter for Sermepa::environment property.
   *
   * @param string $environment
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws \CommerceRedsys\Payment\SermepaException
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
