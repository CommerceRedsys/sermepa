<?php

/**
 * @file
 * Payment gateway class for spanish banks that use Sermepa/Redsys systems.
 *
 * Full list of banks managed by sermepa:
 * http://www.redsys.es/wps/portal/redsys/publica/acercade/nuestrosSocios
 */

require_once 'SermepaException.php';

/**
 * Class implementation.
 */
class Sermepa {
  /**
   * Constant indicating error code for an undefined parameter.
   */
  const UNDEFINED_PARAM = 0;

  /**
   * Constant indicating error code for a missing parameter.
   */
  const MISSING_PARAM = 1;

  /**
   * Constant indicating error code for a bad parameter.
   */
  const BAD_PARAM = 2;

  /**
   * Constant indicating error code for a too long parameter.
   */
  const TOOLONG_PARAM = 3;

  /**
   * Constant indicating the signature algorithm version.
   */
  const SERMEPA_DS_SIGNATUREVERSION = 'HMAC_SHA256_V1';

  /**
   * Constant indicating the test environment.
   */
  const SERMEPA_URL_TEST = 'https://sis-t.redsys.es:25443/sis/realizarPago';

  /**
   * Constant indicating the live environment.
   */
  const SERMEPA_URL_LIVE = 'https://sis.redsys.es/sis/realizarPago';

  /**
   * Required. To Euros the last two positions are considered decimal.
   */
  private $DsMerchantAmount;

  /**
   * Optional. Represents the authorization code necessary to identify a
   * recurring transaction returns successively in subsequent recurring
   * transactions. Required in continuing returner operations.
   */
  private $DsMerchantAuthorisationCode;

  /**
   * Format yyyy-MM-dd date for recurring transactions (required for recurring
   * and recurring deferred).
   */
  private $DsMerchantChargeExpiryDate;

  /**
   * Optional: 3 is considered its maximum length. The value 000, indicating
   * that there isn't determined the customer's language.
   */
  private $DsMerchantConsumerLanguage;

  /**
   * Required. Numeric currency code.
   */
  private $DsMerchantCurrency;

  /**
   * Frequency in days for recurring transactions, recurring deferred (required
   * to recurring).
   */
  private $DsMerchantDateFrecuency;

  /**
   * Required. FUC Code assigned to commerce.
   */
  private $DsMerchantMerchantCode;

  /**
   * Optional field for commerce to be included in the data sent by the
   * "on-line" answer to trade if you have chosen this option.
   */
  private $DsMerchantMerchantData;

  /**
   * Optional: commerce name will appear on the ticket that the client.
   */
  private $DsMerchantMerchantName;

  /**
   * Required if commerce is "on-line" notification. URL of commerce that will
   * receive a post with transaction data.
   */
  private $DsMerchantMerchantURL;

  /**
   * Required. Order identifier. The first 4 digits must be numeric, for the
   * remaining digits only use the following characters ASCII:
   * - From 30 = 0 to 39 = 9
   * - From 65 = A to 90 = Z
   * - From 97 = a to 122 = z
   */
  private $DsMerchantOrder;

  /**
   * Required. Commerce SHa256 password.
   */
  private $DsMerchantPassword;

  /**
   * Optional. 125 is considered its maximum length. This field will display
   * to the holder on the screen confirmation of purchase.
   */
  private $DsMerchantProductDescription;

  /**
   * Required. Represents the sum of the amounts of installments.
   * The latter two are considered decimal positions.
   */
  private $DsMerchantSumTotal;

  /**
   * Required. Terminal number will be assigned your bank. 3 is considered the
   * maximum length.
   */
  private $DsMerchantTerminal;

  /**
   * Optional. 60 is considered its maximum length. This field will display
   * to the holder on the screen confirmation of purchase.
   */
  private $DsMerchantTitular;

  /**
   * Optional. Format yyyy-MM-dd. Represents the date of subsequent recurrent
   * operation is required to identify the transaction in successive returns
   * from continuing operations. Required for returns from continuing operations
   * continuing operations and deferred.
   */
  private $DsMerchantTransactionDate;

  /**
   * Required field for commerce to indicate what type of transaction it is.
   */
  private $DsMerchantTransactionType;

  /**
   * Optional: If you send will be used as ignoring the configured URLKO the
   * administration module if you have it.
   */
  private $DsMerchantUrlKO;

  /**
   * Optional: If you send will be used as ignoring the configured URLOK the
   * administration module if you have it.
   */
  private $DsMerchantUrlOK;

  /**
   * Environment: live, test or an override url.
   */
  private $environment;

  /**
   * An associative array containing the Sermepa feedback transaction
   * parameters.
   */
  private $feedbackParameters;

  /**
   * Initialize the instance.
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
   *   Commerce SHA256 password∫.
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
   * @throws SermepaException
   */
  public function __construct($titular, $merchant_code, $merchant_terminal, $merchant_password, $environment, $options = array()) {
    $this->setTitular($titular)
         ->setMerchantCode($merchant_code)
         ->setTerminal($merchant_terminal)
         ->setMerchantPassword($merchant_password)
         ->setEnvironment($environment);

    foreach ($options as $key => $value) {
      $method = $this->getSetterMethod($key);
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
      else {
        throw new SermepaException('The option ' . $key . ' is not defined.', Sermepa::UNDEFINED_PARAM);
      }
    }
  }

  /**
   * Validate all properties.
   *
   * @return boolean
   *   Boolean indicating whether or not the properties was valdiated.
   *
   * @throws SermepaException
   */
  private function check() {
    $validate = TRUE;
    if (!isset($this->DsMerchantTransactionType)) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_TransactionType.', Sermepa::MISSING_PARAM);
    }
    if (!isset($this->DsMerchantAmount)) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_Amount.', Sermepa::MISSING_PARAM);
    }
    if (!isset($this->DsMerchantCurrency)) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_Currency.', Sermepa::MISSING_PARAM);
    }
    if (!isset($this->DsMerchantOrder)) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_Order.', Sermepa::MISSING_PARAM);
    }
    if (!isset($this->DsMerchantSumTotal)) {
      $this->setSumTotal($this->DsMerchantAmount);
    }
    if (!isset($this->DsMerchantDateFrecuency) && $this->DsMerchantTransactionType == 5) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_DateFrecuency.', Sermepa::MISSING_PARAM);
    }
    if (!isset($this->DsMerchantChargeExpiryDate)
        && ($this->DsMerchantChargeExpiryDate == 5 || $this->DsMerchantChargeExpiryDate == 'O')) {
      $validate = FALSE;
      throw new SermepaException('Must enter a valid Ds_Merchant_ChargeExpiryDate.', Sermepa::MISSING_PARAM);
    }

    return $validate;
  }

  /**
   * Get encrypted password.
   *
   * @param string $$merchant_password
   *   The decoded SHA256 merchant password.
   * @param string $order_number
   *   The transaction order number.
   *
   * @return string
   *   Return encrypted order number with decoded SHA256 password.
   */
  private function getEncryptedPassword($merchant_password, $order_number) {
    // Set default IV value.
    // byte [] IV = {0, 0, 0, 0, 0, 0, 0, 0}.
    $bytes = array(0, 0, 0, 0, 0, 0, 0, 0);
    // PHP 4 >= 4.0.2.
    $iv = implode(array_map("chr", $bytes));

    // Return encrypted order number with decoded SHA256 password.
    // PHP 4 >= 4.0.2.
    return mcrypt_encrypt(MCRYPT_3DES, $merchant_password, $order_number, MCRYPT_MODE_CBC, $iv);
  }

  /**
   * Get the trasaction parameters for the sermepa form.
   *
   * @return array
   *   An associative array containing the transaction parameters.
   */
  private function getParameters() {
    if ($this->check() === FALSE) {
      return FALSE;
    }

    $parameters = array(
      'Ds_Merchant_Amount' => $this->DsMerchantAmount,
      'Ds_Merchant_AuthorisationCode' => $this->DsMerchantAuthorisationCode,
      'Ds_Merchant_ChargeExpiryDate' => $this->DsMerchantChargeExpiryDate,
      'Ds_Merchant_ConsumerLanguage' => $this->DsMerchantConsumerLanguage,
      'Ds_Merchant_Currency' => $this->DsMerchantCurrency,
      'Ds_Merchant_DateFrecuency' => $this->DsMerchantDateFrecuency,
      'Ds_Merchant_MerchantCode' => $this->DsMerchantMerchantCode,
      'Ds_Merchant_MerchantData' => $this->DsMerchantMerchantData,
      'Ds_Merchant_MerchantName' => $this->DsMerchantMerchantName,
      'Ds_Merchant_MerchantURL' => $this->DsMerchantMerchantURL,
      'Ds_Merchant_Order' => $this->DsMerchantOrder,
      'Ds_Merchant_ProductDescription' => $this->DsMerchantProductDescription,
      'Ds_Merchant_SumTotal' => $this->DsMerchantSumTotal,
      'Ds_Merchant_Terminal' => $this->DsMerchantTerminal,
      'Ds_Merchant_Titular' => $this->DsMerchantTitular,
      'Ds_Merchant_TransactionDate' => $this->DsMerchantTransactionDate,
      'Ds_Merchant_TransactionType' => $this->DsMerchantTransactionType,
      'Ds_Merchant_UrlKO' => $this->DsMerchantUrlKO,
      'Ds_Merchant_UrlOK' => $this->DsMerchantUrlOK,
    );

    return array_filter($parameters);
  }

  /**
   * Get a camel based method name based on a dash cased name.
   *
   * @param string $attribute
   *   The dash separated name.
   *
   * @return string
   *   Return camel setter method name.
   */
  private function getSetterMethod($attribute) {
    $attributes = explode('_', $attribute);

    $camel = '';
    foreach ($attributes as $attr) {
      $camel .= ucfirst($attr);
    }

    if (preg_match('/(ok|ko)$/i', $camel)) {
      $camel = substr($camel, 0, -2) . strtoupper(substr($camel, -2));
    }
    elseif (preg_match('/(url)$/i', $camel)) {
      $camel = substr($camel, 0, -3) . strtoupper(substr($camel, -3));
    }

    return 'set' . $camel;
  }

  /**
   * Store the feedback parameters.
   *
   * @param array $parameters
   *   An associative array of feedback parameters values:
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
  private function setFeedbackParameters($parameters) {
    $this->feedbackParameters = [];

    foreach ($parameters as $parameter_key => $parameter_value) {
      $this->feedbackParameters[strtoupper($parameter_key)] = $parameter_value;
    }
  }

  /**
   * Compose the merchant parameters.
   *
   * @return mixed
   *   Base64 encoded JSON parameters or FALSE.
   */
  public function composeMerchantParameters() {
    // Convert parameters array to JSON Object.

    $parameters = $this->getParameters();

    if ($parameters) {
      $json_parameters = json_encode($parameters);

      // Return encoded object parameters in base64.
      return base64_encode($json_parameters);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Compose the merchant signature.
   *
   * @return string
   *   Base64 encoded signature.
   */
  public function composeMerchantSignature() {
    // Decode SHA256 merchant password.
    $merchant_password = base64_decode($this->DsMerchantPassword);

    // Compose Ds_MerchantParameters.
    $merchant_parameters = $this->composeMerchantParameters();

    //  Encrypts merchant password with order number.
    $merchant_password = $this->getEncryptedPassword($merchant_password, $this->getOrder());

    // Generate a keyed hash signature using the HMAC method.
    // PHP 5 >= 5.1.2.
    $signature = hash_hmac('sha256', $merchant_parameters, $merchant_password, TRUE);

    // Return signature in base64.
    return base64_encode($signature);
  }

  /**
   * Compose the merchant signature from feedback values.
   *
   * @param string $encoded_parameters
   *   The feedback Ds_MerchantParameters.
   *
   * @return string
   *   Base64 encoded signature.
   */
  public function composeMerchantSignatureFromFeedback($encoded_parameters) {
    // Decode SHA256 merchant password.
    $merchant_password = base64_decode($this->DsMerchantPassword);

    // Decode Ds_MerchantParameters.
    $decoded_parameters = base64_decode(strtr($encoded_parameters, '-_', '+/'));
    // Save the feedback decoded parameters.
    $this->setFeedbackParameters(json_decode($decoded_parameters, TRUE));

    //  Encrypts merchant password with order number.
    $merchant_password = $this->getEncryptedPassword($merchant_password, $this->getFeedbackValue('Ds_Order'));

    // Generate a keyed hash signature using the HMAC method.
    // PHP 5 >= 5.1.2.
    $signature = hash_hmac('sha256', $encoded_parameters, $merchant_password, TRUE);

    // Return signature in base64.
    return strtr(base64_encode($signature), '+/', '-_');
  }

  /**
   * Get the Sermepa feedback from GET + POST parameters.
   *
   * @return array
   *   An associative array containing the Sermepa feedback taken from the
   *   $_GET and $_POST superglobals, excluding 'q'.
   *   Returns FALSE if the Ds_SignatureVersion parameter is missing
   *   (indicating missing or invalid Sermepa feedback).
   */
  public function getFeedback() {
    $feedback = FALSE;
    if (isset($_REQUEST['Ds_SignatureVersion'])) {
      // Prepare the feedback values sent by Sermepa for processing. We don't
      // use $_REQUEST since this includes the $_SESSION variables.
      $feedback = $_GET + $_POST;
      unset($feedback['q']);
    }
    return $feedback;
  }

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
   * @throws SermepaException
   */
  public function checkFeedback($feedback) {
    $status = FALSE;

    $encoded_parameters = $feedback['Ds_MerchantParameters'];
    $feedback_signature = $feedback['Ds_Signature'];

    // Compose signature from feedback merchant parameters.
    $signature_from_parameters = $this->composeMerchantSignatureFromFeedback($encoded_parameters);

    // Validate if are the same signature.
    if ($signature_from_parameters == $feedback_signature) {
      $status = TRUE;
    }

    // Return the feedback validation.
    return $status;
  }

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
  public function handleResponse($response = NULL) {
    if ((int) $response <= 99) {
      $msg = 'Transaction authorized for payments and preauthorizations';
    }
    else {
      switch ((int) $response) {
        case 900:
          $msg = 'Transaction authorized for returns and confirmations';
          break;

        case 101:
          $msg = 'Expired card';
          break;

        case 102:
          $msg = 'Temporary exception card or on suspicion of fraud';
          break;

        case 106:
          $msg = 'PIN tries exceeded';
          break;

        case 125:
          $msg = 'Not effective card';
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

        case 904:
          $msg = 'Commerce not affiliated to FUC';
          break;

        case 909:
          $msg = 'System error';
          break;

        case 913:
          $msg = 'Order duplicated';
          break;

        case 944:
          $msg = 'Wrong session';
          break;

        case 950:
          $msg = 'Return operation not allowed';
          break;

        case 9912:
        case 912:
          $msg = 'Issuer not available';
          break;

        case 9064:
          $msg = 'Wrong number of places in the card';
          break;

        case 9078:
          $msg = 'Not allowed operation type for that card';
          break;

        case 9093:
          $msg = 'Nonexistent card';
          break;

        case 9094:
          $msg = 'International servers are rejected';
          break;

        case 9104:
          $msg = 'Commerce with "owner safe" and the owner without secure shopping key';
          break;

        case 9218:
          $msg = 'Commerce does not allow safe operations per input';
          break;

        case 9253:
          $msg = 'Card does not do the check-digit';
          break;

        case 9256:
          $msg = 'The commerce can not to make pre-authorization';
          break;

        case 9257:
          $msg = 'This card does not allow preauthorization operations';
          break;

        case 9261:
          $msg = 'Operation stopped for exceeding the control of restrictions on entry to the SIS';
          break;

        case 9913:
          $msg = 'Error in commerce confirmation sent to the Virtual TPV';
          break;

        case 9914:
          $msg = '"KO" commerce confirmation';
          break;

        case 9915:
          $msg = 'Payment canceled by user';
          break;

        case 9928:
          $msg = 'Cancellation of deferred authorization by SIS';
          break;

        case 9929:
          $msg = 'Cancellation of deferred authorization by the commerce';
          break;

        case 9997:
          $msg = 'Another transaction is being processed in SIS with the same card';
          break;

        case 9998:
          $msg = 'Operation in card data request process';
          break;

        case 9999:
          $msg = 'Operation has been redirected issuer to authenticate';
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
  public static function getAvailableConsumerLanguages() {
    return array(
      '001' => 'Spanish',
      '002' => 'English',
      '003' => 'Catalan',
      '004' => 'French',
      '005' => 'German',
      '006' => 'Dutch',
      '007' => 'Italian',
      '008' => 'Swedish',
      '009' => 'Portuguese',
      '010' => 'Valencian',
      '011' => 'Polish',
      '012' => 'Galician',
      '013' => 'Euskera',
      '208' => 'Danish',
    );
  }

  /**
   * Get all available currencies.
   *
   * @return array
   *   Return an array with all available currencies.
   */
  public static function getAvailableCurrencies() {
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
   * Get all available environments.
   *
   * @return array
   *   Return an array with all available environments.
   */
  public static function getAvailableEnvironments() {
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
  public static function getAvailableTransactionTypes() {
    return array(
      '0' => 'Authorization',
      '1' => 'Pre-authorization',
      '2' => 'Confirmation of preauthorization',
      '3' => 'Automatic return',
      '5' => 'Recurring transaction',
      '6' => 'Successive transaction',
      '7' => 'Pre-authentication',
      '8' => 'Confirmation of pre-authentication',
      '9' => 'Annulment of preauthorization',
      'O' => 'Authorization delayed',
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
   * @throws SermepaException
   */
  protected function set($key, $value) {
    if (!property_exists($this, $key)) {
      throw new SermepaException('The property ' . $key . ' is not defined.', Sermepa::UNDEFINED_PARAM);
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
   * @throws SermepaException
   */
  public function setAmount($amount) {
    if (!preg_match('/^([0-9]+)$/i', $amount) || strlen($amount) > 12) {
      throw new SermepaException('The specified Ds_Merchant_Amount: ' . $amount . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantAuthorisationCode property.
   *
   * @param integer $authorisation_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setAuthorisationCode($authorisation_code) {
    if (!preg_match('/^([0-9]{6})$/i', $authorisation_code)) {
      throw new SermepaException('The specified Ds_Merchant_AuthorisationCode: ' . $authorisation_code . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantChargeExpiryDate property.
   *
   * @param string $charge_expiry_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setChargeExpiryDate($charge_expiry_date) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/i', $charge_expiry_date) &&
        strtotime(date("Y-m-d", strtotime($charge_expiry_date))) <= time()) {
      throw new SermepaException('The specified Ds_Merchant_ChargeExpiryDate: ' . $charge_expiry_date . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantConsumerLanguage property.
   *
   * @param integer $consumer_language
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setConsumerLanguage($consumer_language) {
    if (!preg_match('/^([0-9]{3})$/i', $consumer_language) &&
        !array_key_exists($consumer_language, $this->getAvailableConsumerLanguages())) {
      throw new SermepaException('The specified Ds_Merchant_ConsumerLanguage: ' . $consumer_language . ' is not valid/available.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantCurrency property.
   *
   * @param integer $currency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setCurrency($currency) {
    if (!array_key_exists($currency, $this->getAvailableCurrencies())) {
      throw new SermepaException('The specified Ds_Merchant_Currency: ' . $currency . ' is not valid/available.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantDateFrecuency property.
   *
   * @param string $date_frecuency
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setDateFrecuency($date_frecuency) {
    if (!preg_match('/^([0-9]+)$/i', $date_frecuency) && strlen($date_frecuency) > 5) {
      throw new SermepaException('The specified Ds_Merchant_DateFrecuency: ' . $date_frecuency . ' is too long.', Sermepa::TOOLONG_PARAM);
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
   * Setter for Sermepa::DsMerchantMerchantCode property.
   *
   * @param integer $merchant_code
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setMerchantCode($merchant_code) {
    if (strlen($merchant_code) != 9) {
      throw new SermepaException('The specified Ds_Merchant_MerchantCode: ' . $merchant_code . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantMerchantData property.
   *
   * @param string $merchant_data
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setMerchantData($merchant_data) {
    if (strlen($merchant_data) > 1024) {
      throw new SermepaException('The specified Ds_Merchant_MerchantData: ' . $merchant_data . ' is too long.', Sermepa::TOOLONG_PARAM);
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
   * Setter for Sermepa::DsMerchantMerchantName property.
   *
   * @param string $merchant_name
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setMerchantName($merchant_name) {
    if (strlen($merchant_name) > 25) {
      throw new SermepaException('The specified Ds_Merchant_MerchantName: ' . $merchant_name . ' is too long.', Sermepa::TOOLONG_PARAM);
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
   * Setter for Sermepa::DsMerchantPassword property.
   *
   * @param string $merchant_password
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setMerchantPassword($merchant_password) {
    if (!isset($merchant_password)) {
      throw new SermepaException('The specified Ds_Merchant_Password is not valid.', Sermepa::BAD_PARAM);
    }
    return $this->set('DsMerchantPassword', $merchant_password);
  }

  /**
   * Getter for Sermepa::DsMerchantMerchantSignature property.
   *
   * @return string
   *   Return the requested property.
   */
  public function getMerchantPassword() {
    return $this->DsMerchantPassword;
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
   * @throws SermepaException
   */
  public function setMerchantURL($merchant_url) {
    if (!filter_var($merchant_url, FILTER_VALIDATE_URL)) {
      throw new SermepaException('The specified Ds_Merchant_MerchantURL: ' . $merchant_url . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantOrder property.
   *
   * @param string $order
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setOrder($order) {
    if (strlen($order) > 12 &&
        !preg_match('/^([0-9]{4})$/i', $order) &&
        !preg_match('/^([a-zA-Z0-9]+)$/i', $order)) {
      throw new SermepaException('The specified Ds_Merchant_Order: ' . $order . ' is not valid.', Sermepa::BAD_PARAM);
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
   * @throws SermepaException
   */
  public function setProductDescription($product_description) {
    if (strlen($product_description) > 125) {
      throw new SermepaException('The specified Ds_Merchant_ProductDescription: ' . $product_description . ' is too long.', Sermepa::TOOLONG_PARAM);
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
   * Setter for Sermepa::DsMerchantSumTotal property.
   *
   * @param integer $sum_total
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setSumTotal($sum_total) {
    if (!preg_match('/^([0-9]+)$/i', $sum_total)) {
      throw new SermepaException('The specified Ds_Merchant_SumTotal: ' . $sum_total . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantTerminal property.
   *
   * @param integer $terminal
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setTerminal($terminal) {
    if (!preg_match('/^([0-9]{3})$/i', $terminal)) {
      throw new SermepaException('The specified Ds_Merchant_Terminal: ' . $terminal . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantTitular property.
   *
   * @param string $titular
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setTitular($titular) {
    if (strlen($titular) > 60) {
      throw new SermepaException('The specified Ds_Merchant_Titular: ' . $titular . ' is too long.', Sermepa::TOOLONG_PARAM);
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
   * Setter for Sermepa::DsMerchantTransactionDate property.
   *
   * @param string $transaction_date
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setTransactionDate($transaction_date) {
    if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/i', $transaction_date)) {
      throw new SermepaException('The specified Ds_Merchant_TransactionDate: ' . $transaction_date . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantTransactionType property.
   *
   * @param mixed $transaction_type
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setTransactionType($transaction_type) {
    if (!array_key_exists($transaction_type, $this->getAvailableTransactionTypes())) {
      throw new SermepaException('The specified Ds_Merchant_TransactionType: ' . $transaction_type . ' is not valid/available.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantUrlKO property.
   *
   * @param string $url_ko
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setUrlKO($url_ko) {
    if (!filter_var($url_ko, FILTER_VALIDATE_URL)) {
      throw new SermepaException('The specified Ds_Merchant_UrlKO: ' . $url_ko . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Setter for Sermepa::DsMerchantUrlOK property.
   *
   * @param string $url_ok
   *   The property to set.
   *
   * @return Sermepa
   *   Return itself.
   *
   * @throws SermepaException
   */
  public function setUrlOK($url_ok) {
    if (!filter_var($url_ok, FILTER_VALIDATE_URL)) {
      throw new SermepaException('The specified Ds_Merchant_UrlOK: ' . $url_ok . ' is not valid.', Sermepa::BAD_PARAM);
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
   * Getter for Sermepa::feedbackParameters property.
   *
   * @param string $key
   *   The array key value to get.
   *
   * @return string
   *   Return the requested array value.
   */
  public function getFeedbackValue($key) {
    $key = strtoupper($key);

    return (isset($this->feedbackParameters[$key]) ? $this->feedbackParameters[$key] : '');
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
   * @throws SermepaException
   */
  public function setEnvironment($environment) {
    if ($environment != 'live' && $environment != 'test' &&
        !filter_var($environment, FILTER_VALIDATE_URL)) {
      throw new SermepaException('The specified environment: ' . $environment . ' is not valid.', Sermepa::BAD_PARAM);
    }
    elseif ($environment == 'live' || $environment == 'test') {
      $environment = constant('Sermepa::SERMEPA_URL_' . strtoupper($environment));
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

  /**
   * Getter for Sermepa::SERMEPA_DS_SIGNATUREVERSION constant.
   *
   * @return string
   *   Return the requested constant.
   */
  public function getSignatureVersion() {
    return Sermepa::SERMEPA_DS_SIGNATUREVERSION;
  }
}
