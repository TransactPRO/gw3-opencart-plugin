<?php

class Transactpro
{
    const CAPTURE_API = 1;
    const CAPTURE_GW = 2;

    const METHOD_SMS = 1;
    const METHOD_DMS = 2;
    const METHOD_CREDIT = 3;
    const METHOD_P2P = 4;
    const METHOD_INIT_RECURRENT_SMS = 5;
    const METHOD_INIT_RECURRENT_DMS = 6;
    const METHOD_RECURRENT_SMS = 7;
    const METHOD_RECURRENT_DMS = 8;

    const STATUS_INIT = 1;
    const STATUS_SENT_TO_BANK = 2;
    const STATUS_HOLD_OK = 3;
    const STATUS_DMS_HOLD_FAILED = 4;
    const STATUS_SMS_FAILED= 5;
    const STATUS_DMS_CHARGE_FAILED = 6;
    const STATUS_SUCCESS = 7;
    const STATUS_EXPIRED = 8;
    const STATUS_HOLD_EXPIRED = 9;
    const STATUS_REFUND_FAILED = 11;
    const STATUS_REFUND_PENDING = 12;
    const STATUS_REFUND_SUCCESS = 13;
    const STATUS_DMS_CANCEL_OK = 15;
    const STATUS_DMS_CANCEL_FAILED = 16;
    const STATUS_REVERSED = 17;
    const STATUS_INPUT_VALIDATION_FAILED = 18;
    const STATUS_BR_VALIDATION_FAILED = 19;
    const STATUS_TERMINAL_GROUP_SELECT_FAILED = 20;
    const STATUS_TERMINAL_SELECT_FAILED = 21;
    const STATUS_DECLINED_BY_BR_ACTION = 23;
    const STATUS_WAITING_CARD_FORM_FILL = 25;
    const STATUS_MPI_URL_GENERATED = 26;
    const STATUS_WAITING_MPI = 27;
    const STATUS_MPI_FAILED = 28;
    const STATUS_MPI_NOT_REACHABLE = 29;
    const STATUS_INSIDE_FORM_URL_SENT = 30;
    const STATUS_MPI_AUTH_FAILED = 31;
    const STATUS_ACQUIRER_NOT_REACHABLE = 32;
    const STATUS_REVERSAL_FAILED = 33;
    const STATUS_CREDIT_FAILED = 34;
    const STATUS_P2P_FAILED = 35;
    const STATUS_GATEWAY_ERROR = 99;

    public function __construct($registry)
    {
        spl_autoload_register(function ($class) {
            if (strpos($class, 'TransactPro\\') === 0) {
                $class_file = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, 12)) . '.php';
                include_once join(DIRECTORY_SEPARATOR, array(
                    __DIR__,
                    'transactpro',
                    'gw3-client',
                    'src',
                    $class_file
                ));
            }
        });

        $this->session = $registry->get('session');
        $this->url = $registry->get('url');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->customer = $registry->get('customer');
        $this->currency = $registry->get('currency');
        $this->registry = $registry;

        $this->gateway = new \TransactPro\Gateway\Gateway($this->config->get('payment_transactpro_gateway_uri'));
        $this->gateway->auth()
            ->setAccountId((int) $this->config->get('payment_transactpro_account_id'))
            ->setSecretKey((string) $this->config->get('payment_transactpro_secret_key'));
    }

    public function getTransactionStatusName($transaction_status)
    {
        $status_names = array(
            self::STATUS_INIT => 'INIT',
            self::STATUS_SENT_TO_BANK => 'SENT_TO_BANK',
            self::STATUS_HOLD_OK => 'HOLD_OK',
            self::STATUS_DMS_HOLD_FAILED => 'DMS_HOLD_FAILED',
            self::STATUS_SMS_FAILED => 'SMS_FAILED',
            self::STATUS_DMS_CHARGE_FAILED => 'DMS_CHARGE_FAILED',
            self::STATUS_SUCCESS => 'SUCCESS',
            self::STATUS_EXPIRED => 'EXPIRED',
            self::STATUS_HOLD_EXPIRED => 'HOLD_EXPIRED',
            self::STATUS_REFUND_FAILED => 'REFUND_FAILED',
            self::STATUS_REFUND_PENDING => 'REFUND_PENDING',
            self::STATUS_REFUND_SUCCESS => 'REFUND_SUCCESS',
            self::STATUS_DMS_CANCEL_OK => 'DMS_CANCEL_OK',
            self::STATUS_DMS_CANCEL_FAILED => 'DMS_CANCEL_FAILED',
            self::STATUS_REVERSED => 'REVERSED',
            self::STATUS_INPUT_VALIDATION_FAILED => 'INPUT_VALIDATION_FAILED',
            self::STATUS_BR_VALIDATION_FAILED => 'BR_VALIDATION_FAILED',
            self::STATUS_TERMINAL_GROUP_SELECT_FAILED => 'TERMINAL_GROUP_SELECT_FAILED',
            self::STATUS_TERMINAL_SELECT_FAILED => 'TERMINAL_SELECT_FAILED',
            self::STATUS_DECLINED_BY_BR_ACTION => 'DECLINED_BY_BR_ACTION',
            self::STATUS_WAITING_CARD_FORM_FILL => 'WAITING_CARD_FORM_FILL',
            self::STATUS_MPI_URL_GENERATED => 'MPI_URL_GENERATED',
            self::STATUS_WAITING_MPI => 'WAITING_MPI',
            self::STATUS_MPI_FAILED => 'MPI_FAILED',
            self::STATUS_MPI_NOT_REACHABLE => 'MPI_NOT_REACHABLE',
            self::STATUS_INSIDE_FORM_URL_SENT => 'INSIDE_FORM_URL_SENT',
            self::STATUS_MPI_AUTH_FAILED => 'MPI_AUTH_FAILED',
            self::STATUS_ACQUIRER_NOT_REACHABLE => 'ACQUIRER_NOT_REACHABLE',
            self::STATUS_REVERSAL_FAILED => 'REVERSAL_FAILED',
            self::STATUS_CREDIT_FAILED => 'CREDIT_FAILED',
            self::STATUS_P2P_FAILED => 'P2P_FAILED',
            self::STATUS_GATEWAY_ERROR => 'GATEWAY_ERROR'
        );

        if (array_key_exists($transaction_status, $status_names)) {
            return $status_names[$transaction_status];
        } else {
            return 'UNKNOWN';
        }
    }

    public function getPaymentMethodName($payment_method)
    {
        $method_names = array(
            self::METHOD_SMS => 'Sms',
            self::METHOD_DMS => 'DmsHold',
            self::METHOD_CREDIT => 'Credit',
            self::METHOD_P2P => 'P2P',
            self::METHOD_INIT_RECURRENT_SMS => 'InitRecurrentSms',
            self::METHOD_INIT_RECURRENT_DMS => 'InitRecurrentDms',
            self::METHOD_RECURRENT_SMS => 'RecurrentSms',
            self::METHOD_RECURRENT_DMS => 'RecurrentDms'
        );
        
        if (array_key_exists($payment_method, $method_names)) {
            return $method_names[$payment_method];
        } else {
            return FALSE;
        }
    }

    public function canRefundTransaction($payment_method, $transaction_status)
    {
        return in_array($payment_method, array(
            self::METHOD_SMS,
            self::METHOD_INIT_RECURRENT_SMS,
            self::METHOD_RECURRENT_SMS
        )) && in_array($transaction_status, array(
            self::STATUS_SUCCESS
        ));
    }

    public function canChargeTransaction($payment_method, $transaction_status)
    {
        return in_array($payment_method, array(
            self::METHOD_DMS,
            self::METHOD_INIT_RECURRENT_DMS,
            self::METHOD_RECURRENT_DMS
        )) && in_array($transaction_status, array(
            self::STATUS_HOLD_OK
        ));
    }

    public function canCancelTransaction($payment_method, $transaction_status)
    {
        return in_array($payment_method, array(
            self::METHOD_DMS,
            self::METHOD_INIT_RECURRENT_DMS,
            self::METHOD_RECURRENT_DMS
        )) && in_array($transaction_status, array(
            self::STATUS_HOLD_OK
        ));
    }

    public function canReverseTransaction($payment_method, $transaction_status)
    {
        return in_array($payment_method, array(
            self::METHOD_SMS,
            self::METHOD_INIT_RECURRENT_SMS,
            self::METHOD_RECURRENT_SMS
        )) && in_array($transaction_status, array(
            self::STATUS_SUCCESS
        ));
    }

    public function getInitRecurrentMethod($payment_method)
    {
        switch ($payment_method) {
            case self::METHOD_SMS:
                return self::METHOD_INIT_RECURRENT_SMS;
            case self::METHOD_DMS:
                return self::METHOD_INIT_RECURRENT_DMS;
            default:
                return FALSE;
        }
    }

    public function getRecurrentMethod($payment_method)
    {
        switch ($payment_method) {
            case self::METHOD_INIT_RECURRENT_SMS:
                return self::METHOD_RECURRENT_SMS;
            case self::METHOD_INIT_RECURRENT_DMS:
                return self::METHOD_RECURRENT_DMS;
            default:
                return FALSE;
        }
    }

    public function isRecurringMethod($payment_method)
    {
        return in_array($payment_method, array(
            self::METHOD_INIT_RECURRENT_SMS,
            self::METHOD_INIT_RECURRENT_DMS,
            self::METHOD_RECURRENT_SMS,
            self::METHOD_RECURRENT_DMS
        ));
    }

    public function isSuccessTransaction($payment_method, $transaction_status)
    {
        if (in_array($payment_method, array(
            self::METHOD_DMS,
            self::METHOD_INIT_RECURRENT_DMS,
            self::METHOD_RECURRENT_DMS
        ))) {
            return in_array($transaction_status, array(
                self::STATUS_HOLD_OK,
                self::STATUS_SUCCESS
            ));
        } else {
            return in_array($transaction_status, array(
                self::STATUS_SUCCESS
            ));
        }
    }

    public function lowestDenomination($value, $currency)
    {
        $power = $this->currency->getDecimalPlace($currency);
        $value = (float) $value;
        return (int) ($value * pow(10, $power));
    }

    public function standardDenomination($value, $currency)
    {
        $power = $this->currency->getDecimalPlace($currency);
        $value = (int) $value;
        return (float) ($value / pow(10, $power));
    }

    public function createTransaction($payment_method, $customer_email, $customer_phone, $billing_address_country, $billing_address_state, $billing_address_city, $billing_address_street, $billing_address_house, $billing_address_flat, $billing_address_zip, $shipping_address_country, $shipping_address_state, $shipping_address_city, $shipping_address_street, $shipping_address_house, $shipping_address_flat, $shipping_address_zip, $amount, $currency, $order_description, $merchant_url, $user_ip, $pan = null, $card_exp = null, $cvv = null, $cardholder_name = null, $customer_birth_date = 'N/A')
    {
        $endpoint_name = $this->getPaymentMethodName((int) $payment_method);
        if (FALSE === $endpoint_name) {
            throw new Exception('Invalid payment method selected.');
        }

        $endpoint = $this->gateway->{'create' . $endpoint_name}();
        
        $endpoint->customer()
            ->setEmail((string) $customer_email)
            ->setPhone((string) $customer_phone)
            ->setBirthDate((string) $customer_birth_date)
            ->setBillingAddressCountry($this->sanitize($billing_address_country))
            ->setBillingAddressState($this->sanitize($billing_address_state))
            ->setBillingAddressCity($this->sanitize($billing_address_city))
            ->setBillingAddressStreet($this->sanitize($billing_address_street))
            ->setBillingAddressHouse($this->sanitize($billing_address_house))
            ->setBillingAddressFlat($this->sanitize($billing_address_flat))
            ->setBillingAddressZIP($this->sanitize($billing_address_zip))
            ->setShippingAddressCountry($this->sanitize($shipping_address_country))
            ->setShippingAddressState($this->sanitize($shipping_address_state))
            ->setShippingAddressCity($this->sanitize($shipping_address_city))
            ->setShippingAddressStreet($this->sanitize($shipping_address_street))
            ->setShippingAddressHouse($this->sanitize($shipping_address_house))
            ->setShippingAddressFlat($this->sanitize($shipping_address_flat))
            ->setShippingAddressZIP($this->sanitize($shipping_address_zip));

        $endpoint->order()
            ->setRecipientName((string) $this->config->get('config_owner'))
            ->setDescription((string) $order_description)
            ->setMerchantSideUrl((string) $merchant_url)
            ->setMerchantTransactionId((string) uniqid(time() . '.', true));
        
        $endpoint->system()->setUserIP((string) $user_ip);
        
        $endpoint->money()
            ->setAmount((int) $amount)
            ->setCurrency((string) $currency);
        
        $endpoint->paymentMethod()
            ->setPAN((string) $pan)
            ->setExpire((string) $card_exp)
            ->setCVV((string) $cvv)
            ->setCardHolderName((string) $cardholder_name);
        
        return $this->processEndpoint($endpoint);
    }

    public function createRecurrentTransaction($payment_method, $transaction_id, $amount)
    {
        $endpoint_name = $this->getPaymentMethodName((int) $payment_method);
        if (FALSE === $endpoint_name) {
            throw new Exception('Invalid payment method selected.');
        }
        
        $endpoint = $this->gateway->{'create' . $endpoint_name}();
        $endpoint->command()->setGatewayTransactionID((string) $transaction_id);
        $endpoint->order()->setMerchantTransactionId((string) uniqid(time() . '.', true));
        $endpoint->money()->setAmount((int) $amount);
        
        return $this->processEndpoint($endpoint);
    }

    public function refundTransaction($transaction_id, $amount)
    {
        $refund = $this->gateway->createRefund();
        $refund->command()->setGatewayTransactionID((string) $transaction_id);
        $refund->order()->setMerchantTransactionId((string) uniqid(time() . '.', true));
        $refund->money()->setAmount((int) $amount);
        
        return $this->processEndpoint($refund);
    }

    public function chargeDmsTransaction($transaction_id, $amount)
    {
        $charge = $this->gateway->createDmsCharge();
        $charge->command()->setGatewayTransactionID((string) $transaction_id);
        $charge->order()->setMerchantTransactionId((string) uniqid(time() . '.', true));
        $charge->money()->setAmount((int) $amount);
        
        return $this->processEndpoint($charge);
    }

    public function cancelDmsTransaction($transaction_id)
    {
        $cancel = $this->gateway->createCancel();
        $cancel->command()->setGatewayTransactionID((string) $transaction_id);
        $cancel->order()->setMerchantTransactionId((string) uniqid(time() . '.', true));
        
        return $this->processEndpoint($cancel);
    }

    public function reverseSmsTransaction($transaction_id)
    {
        $reverse = $this->gateway->createReversal();
        $reverse->command()->setGatewayTransactionID((string) $transaction_id);
        $reverse->order()->setMerchantTransactionId((string) uniqid(time() . '.', true));
        
        return $this->processEndpoint($reverse);
    }

    public function auth($account_id, $secret_key)
    {
        $this->gateway->auth()
            ->setAccountId((int) $account_id)
            ->setSecretKey((string) $secret_key);
        return $this;
    }

    protected function processEndpoint($endpoint)
    {
        try {
            $request = $this->gateway->generateRequest($endpoint);
            $response = $this->gateway->process($request);
            $this->logOutcome($request, $response);
        } catch (Exception $error) {
            $this->logOutcome($request, $error);
            throw $error;
        }

        if (200 !== $response->getStatusCode()) {
            throw new Exception($response->getBody(), $response->getStatusCode());
        }
        
        $json = json_decode($response->getBody(), true);
        $json_status = json_last_error();
        
        if (JSON_ERROR_NONE !== $json_status) {
            throw new Exception('JSON ' . json_last_error_msg(), $json_status);
        }
        
        if (! isset($json['gw']) || empty($json['gw'])) {
            if (isset($json['error']) && ! empty($json['error'])) {
                throw new Exception($json['error']['message'], $json['error']['code']);
            } else {
                throw new Exception('Unexpected payment gateway response.');
            }
        } elseif (isset($json['error']['message'])) {
            $json['error'] = $json['error']['message'];
        }
        
        if (empty($json['error'])) {
            unset($json['error']);
        }
        
        return $json;
    }

    protected function sanitize($value)
    {
        return preg_replace('/[^\w\d\s]/', ' ', (string) $value);
    }

    public function logOutcome($request, $response) {
        if (1 != $this->config->get('payment_transactpro_logging')) {
            return;
        }

        $data = date('Y-m-d H:i:s') . "\r\n";

        if ($request)  {
            $data = $data . "---> Request ({$this->config->get('payment_transactpro_gateway_uri')}{$request->getPath()}):\r\n" . var_export($request->getData(), true) . "\r\n";
        }

        if ($response) {
            if ($response instanceof Exception) {
                $data = $data . "<--- Error ({$response->getMessage()}):\r\n{$response->getTraceAsString()}\r\n";
            } else {
                $data = $data . "<--- Response ({$response->getStatusCode()}):\r\n{$response->getBody()}\r\n";
            }
        }

        $data = $data . "---------------------------------------\r\n\r\n";

        file_put_contents(DIR_LOGS . 'transactpro.log', $data, FILE_APPEND);
    }

    public function logIncome($request) {
        if (1 != $this->config->get('payment_transactpro_logging')) {
            return;
        }

        $data = date('Y-m-d H:i:s') . "\r\n";

        if ($request)  {
            $data = $data . "<--- Request ({$request->get['route']}):\r\n" . html_entity_decode($request->post['json']) . "\r\n";
        }

        $data = $data . "---------------------------------------\r\n\r\n";

        file_put_contents(DIR_LOGS . 'transactpro.log', $data, FILE_APPEND);
    }

}