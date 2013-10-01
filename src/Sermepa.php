<?php

/**
 * @file
 * Payment gateway class for spanish banks that use Sermepa/Redsys systems.
 *
 * Full list of banks managed by sermepa:
 * http://www.servired.es/espanol/miembros.htm
 */

/**
 * Class implementation.
 */
class Sermepa {
  /**
   * Constant indicating the test environment.
   */
  const SERMEPA_URL_TEST = 'https://sis-t.sermepa.es:25443/sis/realizarPago';

  /**
   * Constant indicating the live environment.
   */
  const SERMEPA_URL_LIVE = 'https://sis.sermepa.es/sis/realizarPago';

  /**
   * Required. To Euros the last two positions are considered decimal.
   */
  private $DsMerchantAmount;

  /**
   * Required. Numeric currency code.
   */
  private $DsMerchantCurrency;

  /**
   * Required. Order identifier.The first 4 digits must be numeric, for the
   * remaining digits only use the following characters ASCII:
   * - From 30 = 0 to 39 = 9
   * - From 65 = A to 90 = Z
   * - From 97 = a to 122 = z
   */
  private $DsMerchantOrder;

  /**
   * Optional. 125 is considered its maximum length. This field will display
   * to the holder on the screen confirmation of purchase.
   */
  private $DsMerchantProductDescription;

  /**
   * Optional. 60 is considered its maximum length. This field will display
   * to the holder on the screen confirmation of purchase.
   */
  private $DsMerchantTitular;

  /**
   * Required. FUC Code assigned to commerce.
   */
  private $DsMerchantMerchantCode;

  /**
   * Required if commerce is "on-line" notification. URL of commerce that will
   * receive a post with transaction data.
   */
  private $DsMerchantMerchantURL;

  /**
   * Optional: If you send will be used as ignoring the configured URLOK the
   * administration module if you have it.
   */
  private $DsMerchantUrlOK;

  /**
   * Optional: If you send will be used as ignoring the configured URLKO the
   * administration module if you have it.
   */
  private $DsMerchantUrlKO;

  /**
   * Optional: commerce name will appear on the ticket that the client.
   */
  private $DsMerchantMerchantName;

  /**
   * Optional: 3 is considered its maximum length. The value 000, indicating
   * that there isn't determined the customer's language.
   */
  private $DsMerchantConsumerLanguage;

  /**
   * Required.
   */
  private $DsMerchantMerchantSignature;

  /**
   * Required. Terminal number will be assigned your bank. 3 is considered the
   * maximum length.
   */
  private $DsMerchantTerminal;

  /**
   * Required. Represents the sum of the amounts of fees.
   * The latter two are considered decimal positions.
   */
  private $DsMerchantSumTotal;

  /**
   * Required field for commerce to indicate what type of transaction it is.
   */
  private $DsMerchantTransactionType;

  /**
   * Optional field for commerce to be included in the data sent by the
   * "on-line" answer to trade if you have chosen this option.
   */
  private $DsMerchantMerchantData;

  /**
   * Frequency in days for recurring transactions, recurring deferred (required
   * to recurring).
   */
  private $DsMerchantDateFrecuency;

  /**
   * Format yyyy-MM-dd date for recurring transactions (required for recurring
   * and recurring deferred).
   */
  private $DsMerchantChargeExpiryDate;

  /**
   * Optional. Represents the authorization code necessary to identify a
   * recurring transaction returns successively in subsequent recurring
   * transactions. Required in continuing returner operations.
   */
  private $DsMerchantAuthorisationCode;

  /**
   * Optional. Format yyyy-MM-dd. Represents the date of subsequent recurrent
   * operation is required to identify the transaction in successive returns
   * from continuing operations. Required for returns from continuing operations
   * continuing operations and deferred.
   */
  private $DsMerchantTransactionDate;

  /**
   * Method of encryption, SHA or Enhanced SHA (sha1 or sha1-enhanced).
   */
  private $encryptionMethod;

  /**
   * Environment: live, test or an override url.
   */
  private $environment;

  /**
   * Empty constructor.
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Initialize the instance.
   */
  private function init() {
    $this->setEnvironment('live');
    $this->setEncryptionMethod('sha1');
    $this->setTerminal('001');
    $this->setCurrency(978);
    $this->setConsumerLanguage(000);
    $this->setTransactionType(0);
  }

  /**
   * Compose the transaction signature.
   *
   * @return string
   *   Return the composed transaction signature.
   */
  private function composeSignature() {
    if ($this->encryptionMethod == 'sha1-enhanced') {
      if ($this->DsMerchantAmount > $this->DsMerchantSumTotal) {
        $message = $this->DsMerchantAmount . $this->DsMerchantOrder . $this->DsMerchantMerchantCode . $this->DsMerchantCurrency . $this->DsMerchantSumTotal . $this->DsMerchantTransactionType . $this->DsMerchantMerchantURL . $this->DsMerchantMerchantSignature;
      }
      else {
        $message = $this->DsMerchantAmount . $this->DsMerchantOrder . $this->DsMerchantMerchantCode . $this->DsMerchantCurrency . $this->DsMerchantTransactionType . $this->DsMerchantMerchantURL . $this->DsMerchantMerchantSignature;
      }
      return strtoupper(sha1($message));
    }
    elseif ($this->encryptionMethod == 'sha1') {
      include_once "includes/sha1.php";
      $sha = new SHA1();
      if ($this->DsMerchantAmount > $this->DsMerchantSumTotal) {
        $message = $this->DsMerchantAmount . $this->DsMerchantOrder . $this->DsMerchantMerchantCode . $this->DsMerchantCurrency .  $this->DsMerchantSumTotal . $this->DsMerchantMerchantSignature;
      }
      else {
        $message = $this->DsMerchantAmount . $this->DsMerchantOrder . $this->DsMerchantMerchantCode . $this->DsMerchantCurrency . $this->DsMerchantMerchantSignature;
      }
      $digest = $sha->hash_string($message);
      return strtoupper($sha->hash_to_string($digest));
    }
  }

  /**
   * Validate all properties.
   *
   * @return boolean
   *   Boolean indicating whether or not the properties was valdiated.
   *
   * @throws ErrorException
   */
  private function check() {
    $validate = TRUE;
    if (empty($this->DsMerchantAmount)) {
      $validate = FALSE;
      throw new ErrorException('Must enter a valid Ds_Merchant_Amount.');
    }
    if (empty($this->DsMerchantOrder)) {
      $validate = FALSE;
      throw new ErrorException('Must enter a valid Ds_Merchant_Order.');
    }
    if (empty($this->DsMerchantTitular)) {
      $validate = FALSE;
      throw new ErrorException('Must enter a valid Ds_Merchant_Titular.');
    }
    if (empty($this->DsMerchantMerchantCode)) {
      $validate = FALSE;
      throw new ErrorException('Must enter a valid Ds_Merchant_MerchantCode.');
    }
    if (empty($this->DsMerchantSumTotal) || !empty($this->DsMerchantAmount)) {
      $this->setSumTotal($this->DsMerchantAmount);
    }

    return $validate;
  }

  /**
   * Get the trasaction fields for the sermepa form.
   */
  public function getFields() {
    if ($this->check() === FALSE) {
      return FALSE;
    }

    $hidden_fields = array(
      'Ds_Merchant_Amount' => $this->DsMerchantAmount,
      'Ds_Merchant_Currency' => $this->DsMerchantCurrency,
      'Ds_Merchant_Order' => $this->DsMerchantOrder,
      'Ds_Merchant_ProductDescription' => $this->DsMerchantProductDescription,
      'Ds_Merchant_Titular' => $this->DsMerchantTitular,
      'Ds_Merchant_MerchantCode' => $this->DsMerchantMerchantCode,
      'Ds_Merchant_MerchantURL' => $this->DsMerchantMerchantURL,
      'Ds_Merchant_UrlOK' => $this->DsMerchantUrlOK,
      'Ds_Merchant_UrlKO' => $this->DsMerchantUrlKO,
      'Ds_Merchant_MerchantName' => $this->DsMerchantMerchantName,
      'Ds_Merchant_ConsumerLanguage' => $this->DsMerchantConsumerLanguage,
      'Ds_Merchant_MerchantSignature' => $this->composeSignature(),
      'Ds_Merchant_Terminal' => $this->DsMerchantTerminal,
      'Ds_Merchant_SumTotal' => $this->DsMerchantSumTotal,
      'Ds_Merchant_TransactionType' => $this->DsMerchantTransactionType,
      'Ds_Merchant_MerchantData' => $this->DsMerchantMerchantData,
      'Ds_Merchant_DateFrecuency' => $this->DsMerchantDateFrecuency,
      'Ds_Merchant_ChargeExpiryDate' => $this->DsMerchantChargeExpiryDate,
      'Ds_Merchant_AuthorisationCode' => $this->DsMerchantAuthorisationCode,
      'Ds_Merchant_TransactionDate' => $this->DsMerchantTransactionDate,
    );

    // Remove empty fields.
    foreach ($hidden_fields as $name => $value) {
      if (empty($value)) {
        unset($hidden_fields[$name]);
      }
    }

    return $hidden_fields;
  }

  /**
   * Get the Sermepa feedback from GET + POST parameters.
   *
   * @return array
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *   Returns FALSE if the Ds_Order parameter is missing (indicating missing or
   *   invalid Sermepa feedback).
   */
  public function getFeedback() {
    $feedback = FALSE;
    if (isset($_REQUEST['Ds_Order'])) {
      // Prepare the feedback values sent by Sermepa for processing. We don't
      // use $_REQUEST since this includes the $_SESSION variables.
      $feedback = $_GET + $_POST;
      unset($feedback['q']);
    }
    return $feedback;
  }

  /**
   * Check if SHA1 in callback feedback is valid.
   *
   * @param array $feedback
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *
   * @param integer $payment_amount
   *   The original payment amount.
   *
   * @return boolean
   *   Boolean indicating whether or not the transaction was valdiated.
   *
   * @throws ErrorException
   */
  public function checkFeedback($feedback, $payment_amount) {
    $merchant_signature = $this->getMerchantSignature();
    if (empty($merchant_signature)) {
      throw new ErrorException('Must enter a valid Ds_Merchant_MerchantSignature.');
      return FALSE;
    }

    $message = $payment_amount . $feedback['Ds_Order'] . $feedback['Ds_MerchantCode'] . $feedback['Ds_Currency'] . $feedback['Ds_Response'] . $merchant_signature;
    if (empty($feedback['Ds_AuthorisationCode'])) {
      throw new ErrorException('No authorisation code for the transaction.');
      return FALSE;
    }
    elseif ($feedback['Ds_Signature'] != strtoupper(sha1($message))) {
      throw new ErrorException('Signature for the payment does not match.');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Handle the response of the payment transaction.
   *
   * Messages from "Guía de Comercios TPV Virtual SIS" v5.19.
   *
   * @param integer $response
   *   The response feedback code.
   *
   * @return string
   *   The handle response message.
   */
  public function handleResponse($response = NULL) {
    if ((int) $response <= 99) {
      $msg = 'Transaction authorized for payments and preauthorizations';
    }
    else {
      switch ((int) $response) {
        case 900:
          $msg = 'Transaction authorized for returns and confirmations';
        case 101:
          $msg = 'Expired card';
          break;

        case 102:
          $msg = 'Temporary exception card or on suspicion of fraud';
          break;

        case 104:
        case 9104:
          $msg = 'Operation not allowed for the card or terminal';
          break;

        case 116:
          $msg = 'Asset insufficient';
          break;

        case 118:
          $msg = 'Card not registered';
          break;

        case 129:
          $msg = 'Wrong security code (CVV2/CVC2)';
          break;

        case 180:
          $msg = 'Card out of the service';
          break;

        case 184:
          $msg = 'Error on owner authentication';
          break;

        case 190:
          $msg = 'Denied without specific reasons';
          break;

        case 191:
          $msg = 'Wrong expiration date';
          break;

        case 202:
          $msg = 'Temporary or emergency card on suspicion of withdrawal card fraud';
          break;

        case 912:
        case 9912:
          $msg = 'Issuer not available';
          break;

        case 913:
          $msg = 'Order duplicated';
          break;

        default:
          $msg = 'Transaction refused';
          break;
      }
    }

    return $msg;
  }

  /**
   * Get all available languages.
   *
   * @return array
   *   Return an array with all available languages.
   */
  public function getAvailableConsumerLanguages() {
    return array(
      '001' => 'Spanish',
      '002' => 'Catalan',
      '003' => 'Dutch',
      '004' => 'Portuguese',
      '005' => 'Valencian',
      '006' => 'Polish',
      '007' => 'Galician',
      '008' => 'English',
      '009' => 'German',
      '010' => 'Swedish',
      '011' => 'French',
      '012' => 'Italian',
      '013' => 'Euskera',
    );
  }

  /**
   * Get all available currencies.
   *
   * @return array
   *   Return an array with all available currencies.
   */
  public function getAvailableCurrencies() {
    return array(
      '978' => 'Euro',
      '840' => 'U.S. Dollar',
      '826' => 'Pound',
      '392' => 'Yen',
      '032' => 'Southern Argentina',
      '124' => 'Canadian Dollar',
      '152' => 'Chilean Peso',
      '170' => 'Colombian Peso',
      '356' => 'India Rupee',
      '484' => 'New Mexican Peso',
      '604' => 'Soles',
      '756' => 'Swiss Franc',
      '986' => 'Brazilian Real',
      '937' => 'Bolivar',
      '949' => 'Turkish lira',
    );
  }

  /**
   * Get all available encryption methods.
   *
   * @return array
   *   Return an array with all available encryption methods.
   */
  public function getAvailableEncryptionMethods() {
    return array(
      'sha1' => 'SHA',
      'sha1-enhanced' => 'Enhanced SHA',
    );
  }

  /**
   * Get all available environments.
   *
   * @return array
   *   Return an array with all available environments.
   */
  public function getAvailableEnvironments() {
    return array(
      'live' => 'Live environment',
      'test' => 'Test environment',
    );
  }

  /**
   * Get all available transaction types.
   *
   * @return array
   *   Return an array with all available transaction types.
   */
  public function getAvailableTransactionTypes() {
    return array(
      '1' => 'Authorization',
      '2' => 'Pre-authorization',
      '3' => 'Confirmation of preauthorization',
      '4' => 'Automatic return',
      '5' => 'Recurring transaction',
      '6' => 'Successive transaction',
      '7' => 'Pre-authentication',
      '8' => 'Confirmation of pre-authentication',
      '9' => 'Annulment of preauthorization',
      '0' => 'Authorization delayed',
      'P' => 'Confirmation of authorization in deferred',
      'Q' => 'Delayed authorization Rescission',
      'R' => 'Initial recurring deferred released',
      'S' => 'Successively recurring deferred released',
    );
  }

  /**
   * Global setter.
   *
   * @param string $key
   *   Name of the property.
   * @param mixed $value
   *   Value to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  protected function set($key, $value) {
    if (!property_exists($this, $key)) {
      throw new ErrorException('The property ' . $key . ' is not defined.');
    }
    $this->{$key} = $value;
    return $this;
  }

  /**
   * Setter for Sermepa::DsMerchantAmount property.
   *
   * @param integer $amount
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setAmount($amount) {
    if (!preg_match('/^([0-9]+)$/i', $amount)) {
      throw new ErrorException('The specified Ds_Merchant_Amount: ' . $amount . ' is not valid.');
    }
    return $this->set('DsMerchantAmount', $amount);
  }

  /**
   * Getter for Sermepa::DsMerchantAmount property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getAmount() {
    return $this->DsMerchantAmount;
  }

  /**
   * Setter for Sermepa::DsMerchantCurrency property.
   *
   * @param integer $currency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setCurrency($currency) {
    if (strlen($currency) != 3 &&
        !preg_match('/^([0-9]+)$/i', $currency) &&
        !array_key_exists($currency, $this->getAvailableCurrencies())) {
      throw new ErrorException('The specified Ds_Merchant_Currency: ' . $currency . ' is not valid/available.');
    }
    return $this->set('DsMerchantCurrency', $currency);
  }

  /**
   * Getter for Sermepa::DsMerchantCurrency property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getCurrency() {
    return $this->DsMerchantCurrency;
  }

  /**
   * Setter for Sermepa::DsMerchantOrder property.
   *
   * @param string $order
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setOrder($order) {
    if (strlen($order) > 12 &&
        !preg_match('/^([0-9]{4})$/i', $order) &&
        !preg_match('/^([a-zA-Z0-9]+)$/i', $order)) {
      throw new ErrorException('The specified Ds_Merchant_Order: ' . $order . ' is not valid.');
    }
    return $this->set('DsMerchantOrder', $order);
  }

  /**
   * Getter for Sermepa::DsMerchantOrder property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getOrder() {
    return $this->DsMerchantOrder;
  }

  /**
   * Setter for Sermepa::DsMerchantProductDescription property.
   *
   * @param string $product_description
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setProductDescription($product_description) {
    if (strlen($product_description) > 125) {
      throw new ErrorException('The specified Ds_Merchant_ProductDescription: ' . $product_description . ' is too long.');
    }
    return $this->set('DsMerchantProductDescription', $product_description);
  }

  /**
   * Getter for Sermepa::DsMerchantProductDescription property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getProductDescription() {
    return $this->DsMerchantProductDescription;
  }

  /**
   * Setter for Sermepa::DsMerchantTitular property.
   *
   * @param string $titular
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setTitular($titular) {
    if (strlen($titular) > 60) {
      throw new ErrorException('The specified Ds_Merchant_Titular: ' . $titular . ' is too long.');
    }
    return $this->set('DsMerchantTitular', $titular);
  }

  /**
   * Getter for Sermepa::DsMerchantTitular property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getTitular() {
    return $this->DsMerchantTitular;
  }

  /**
   * Setter for Sermepa::DsMerchantMerchantCode property.
   *
   * @param integer $merchant_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setMerchantCode($merchant_code) {
    if (strlen($merchant_code) != 9) {
      throw new ErrorException('The specified Ds_Merchant_MerchantCode: ' . $merchant_code . ' is not valid.');
    }
    return $this->set('DsMerchantMerchantCode', $merchant_code);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantCode property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getMerchantCode() {
    return $this->DsMerchantMerchantCode;
  }

  /**
   * Setter for Sermepa::DsMerchantMerchantURL property.
   *
   * @param string $merchant_url
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setMerchantURL($merchant_url) {
    if (!filter_var($merchant_url, FILTER_VALIDATE_URL)) {
      throw new ErrorException('The specified Ds_Merchant_MerchantURL: ' . $merchant_url . ' is not valid.');
    }
    return $this->set('DsMerchantMerchantURL', $merchant_url);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantURL property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantURL() {
    return $this->DsMerchantMerchantURL;
  }

  /**
   * Setter for Sermepa::DsMerchantUrlOK property.
   *
   * @param string $url_ok
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setUrlOK($url_ok) {
    if (!filter_var($url_ok, FILTER_VALIDATE_URL)) {
      throw new ErrorException('The specified Ds_Merchant_UrlOK: ' . $url_ok . ' is not valid.');
    }
    return $this->set('DsMerchantUrlOK', $url_ok);
  }

  /**
   * Getter for Sermepa::DsMerchantUrlOK property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getUrlOK() {
    return $this->DsMerchantUrlOK;
  }

  /**
   * Setter for Sermepa::DsMerchantUrlKO property.
   *
   * @param string $url_ko
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setUrlKO($url_ko) {
    if (!filter_var($url_ko, FILTER_VALIDATE_URL)) {
      throw new ErrorException('The specified Ds_Merchant_UrlKO: ' . $url_ko . ' is not valid.');
    }
    return $this->set('DsMerchantUrlKO', $url_ko);
  }

  /**
   * Getter for Sermepa::DsMerchantUrlKO property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getUrlKO() {
    return $this->DsMerchantUrlKO;
  }

  /**
   * Setter for Sermepa::DsMerchantMerchantName property.
   *
   * @param string $merchant_name
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setMerchantName($merchant_name) {
    if (strlen($merchant_name) > 25) {
      throw new ErrorException('The specified Ds_Merchant_MerchantName: ' . $merchant_name . ' is too long.');
    }
    return $this->set('DsMerchantMerchantName', $merchant_name);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantName property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantName() {
    return $this->DsMerchantMerchantName;
  }

  /**
   * Setter for Sermepa::DsMerchantConsumerLanguage property.
   *
   * @param integer $consumer_language
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setConsumerLanguage($consumer_language) {
    if (strlen($consumer_language) != 3 &&
        !preg_match('/^([0-9]+)$/i', $consumer_language) &&
        !array_key_exists($consumer_language, $this->getAvailableConsumerLanguages())) {
      throw new ErrorException('The specified Ds_Merchant_ConsumerLanguage: ' . $consumer_language . ' is not valid/available.');
    }
    return $this->set('DsMerchantConsumerLanguage', $consumer_language);
  }

  /**
   * Getter for Sermepa::DsMerchantConsumerLanguage property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getConsumerLanguage() {
    return $this->DsMerchantConsumerLanguage;
  }

  /**
   * Setter for Sermepa::DsMerchantMerchantSignature property.
   *
   * @param string $merchant_signature
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setMerchantSignature($merchant_signature) {
    if (empty($merchant_signature)) {
      throw new ErrorException('The specified Ds_Merchant_MerchantSignature is not valid.');
    }
    return $this->set('DsMerchantMerchantSignature', $merchant_signature);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantSignature property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantSignature() {
    return $this->DsMerchantMerchantSignature;
  }

  /**
   * Setter for Sermepa::DsMerchantTerminal property.
   *
   * @param integer $terminal
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setTerminal($terminal) {
    if (strlen($terminal) != 3 && !preg_match('/^([0-9]+)$/i', $terminal)) {
      throw new ErrorException('The specified Ds_Merchant_Terminal: ' . $terminal . ' is not valid.');
    }
    return $this->set('DsMerchantTerminal', $terminal);
  }

  /**
   * Getter for Sermepa::DsMerchantTerminal property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getTerminal() {
    return $this->DsMerchantTerminal;
  }

  /**
   * Setter for Sermepa::DsMerchantSumTotal property.
   *
   * @param integer $sum_total
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setSumTotal($sum_total) {
    if (!preg_match('/^([0-9]+)$/i', $sum_total)) {
      throw new ErrorException('The specified Ds_Merchant_SumTotal: ' . $sum_total . ' is not valid.');
    }
    return $this->set('DsMerchantSumTotal', $sum_total);
  }

  /**
   * Getter for Sermepa::DsMerchantSumTotal property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getSumTotal() {
    return $this->DsMerchantSumTotal;
  }

  /**
   * Setter for Sermepa::DsMerchantTransactionType property.
   *
   * @param mixed $transaction_type
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setTransactionType($transaction_type) {
    if (!array_key_exists($transaction_type, $this->getAvailableTransactionTypes())) {
      throw new ErrorException('The specified Ds_Merchant_TransactionType: ' . $transaction_type . ' is not valid/available.');
    }
    return $this->set('DsMerchantTransactionType', $transaction_type);
  }

  /**
   * Getter for Sermepa::DsMerchantCurrency property.
   *
   * @return mixed
   *   Return the requested property.
   */
  public function getTransactionType() {
    return $this->DsMerchantTransactionType;
  }

  /**
   * Setter for Sermepa::DsMerchantMerchantData property.
   *
   * @param string $merchant_data
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setMerchantData($merchant_data) {
    if (strlen($merchant_data) > 1024) {
      throw new ErrorException('The specified Ds_Merchant_MerchantData: ' . $merchant_data . ' is too long.');
    }
    return $this->set('DsMerchantMerchantData', $merchant_data);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantData property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantData() {
    return $this->DsMerchantMerchantData;
  }

  /**
   * Setter for Sermepa::DsMerchantDateFrecuency property.
   *
   * @param string $date_frecuency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setDateFrecuency($date_frecuency) {
    if (!preg_match('/^([0-9]+)$/i', $date_frecuency) && strlen($date_frecuency) > 5) {
      throw new ErrorException('The specified Ds_Merchant_DateFrecuency: ' . $date_frecuency . ' is too long.');
    }
    return $this->set('DsMerchantDateFrecuency', $date_frecuency);
  }

  /**
   * Getter for Sermepa::DsMerchantDateFrecuency property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getDateFrecuency() {
    return $this->DsMerchantDateFrecuency;
  }

  /**
   * Setter for Sermepa::DsMerchantChargeExpiryDate property.
   *
   * @param string $charge_expiry_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setChargeExpiryDate($charge_expiry_date) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/i', $charge_expiry_date) &&
        strtotime(date("Y-m-d", strtotime($charge_expiry_date))) <= time()) {
      throw new ErrorException('The specified Ds_Merchant_ChargeExpiryDate: ' . $charge_expiry_date . ' is not valid.');
    }
    return $this->set('DsMerchantChargeExpiryDate', $charge_expiry_date);
  }

  /**
   * Getter for Sermepa::DsMerchantChargeExpiryDate property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getChargeExpiryDate() {
    return $this->DsMerchantChargeExpiryDate;
  }

  /**
   * Setter for Sermepa::DsMerchantAuthorisationCode property.
   *
   * @param integer $authorisation_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setAuthorisationCode($authorisation_code) {
    if (!preg_match('/^([0-9]{6})$/i', $authorisation_code)) {
      throw new ErrorException('The specified Ds_Merchant_AuthorisationCode: ' . $authorisation_code . ' is not valid.');
    }
    return $this->set('DsMerchantAuthorisationCode', $authorisation_code);
  }

  /**
   * Getter for Sermepa::DsMerchantAuthorisationCode property.
   *
   * @return integer
   *   Return the requested property.
   */
  public function getAuthorisationCode() {
    return $this->DsMerchantAuthorisationCode;
  }

  /**
   * Setter for Sermepa::DsMerchantTransactionDate property.
   *
   * @param string $transaction_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setTransactionDate($transaction_date) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/i', $transaction_date)) {
      throw new ErrorException('The specified Ds_Merchant_TransactionDate: ' . $transaction_date . ' is not valid.');
    }
    return $this->set('DsMerchantTransactionDate', $transaction_date);
  }

  /**
   * Getter for Sermepa::DsMerchantTransactionDate property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getTransactionDate() {
    return $this->DsMerchantTransactionDate;
  }

  /**
   * Setter for Sermepa::encryptionMethod property.
   *
   * @param string $encryption_method
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setEncryptionMethod($encryption_method) {
    if ($encryption_method != 'sha1' && $encryption_method != 'sha1-enhanced') {
      throw new ErrorException('The specified encryption method: ' . $encryption_method . ' is not valid.');
    }
    return $this->set('encryptionMethod', $encryption_method);
  }

  /**
   * Getter for Sermepa::encryptionMethod property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getEncryptionMethod() {
    return $this->encryptionMethod;
  }

  /**
   * Setter for Sermepa::environment property.
   *
   * @param string $environment
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws ErrorException
   */
  public function setEnvironment($environment) {
    if ($environment != 'live' && $environment != 'test' &&
        !filter_var($environment, FILTER_VALIDATE_URL)) {
      throw new ErrorException('The specified environment: ' . $environment . ' is not valid.');
    }
    elseif ($environment == 'live' || $environment == 'test') {
      $environment = constant('SERMEPA::SERMEPA_URL_' . strtoupper($environment));
    }
    return $this->set('environment', $environment);
  }

  /**
   * Getter for Sermepa::environment property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getEnvironment() {
    return $this->environment;
  }
}
