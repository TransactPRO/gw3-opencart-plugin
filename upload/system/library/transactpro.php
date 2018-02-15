<?php

class Transactpro
{

    const METHOD_SMS = 1;
    const METHOD_DMS_CHARGE = 2;
    const METHOD_DMS_HOLD = 3;
    const METHOD_CREDIT = 4;
    const METHOD_P2P = 5;
    const METHOD_RECURRENT_SMS = 6;
    const METHOD_RECURRENT_DMS_HOLD = 7;

    const STATUS_INIT = 1;
    const STATUS_SENT_TO_BANK = 2;
    const STATUS_HOLD_OK = 3;
    const STATUS_DMS_HOLD_FAILED = 4;
    const STATUS_SMS_FAILED_SMS = 5;
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

    public function __construct($registry)
    {
        $this->session = $registry->get('session');
        $this->url = $registry->get('url');
        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->customer = $registry->get('customer');
        $this->currency = $registry->get('currency');
        $this->registry = $registry;

        $this->gateway = new \TransactPro\Gateway\Gateway($this->config->get('payment_transactpro_gateway_uri'));
        $this->gateway->auth()
            ->setAccountId($this->config->get('payment_transactpro_account_id'))
            ->setSecretKey($this->config->get('payment_transactpro_secret_key'));
    }

    public function getTransactionStatusName($transaction_status)
    {
        $status_names = array(
            self::STATUS_INIT =>'INIT',
            self::STATUS_SENT_TO_BANK =>'SENT_TO_BANK',
            self::STATUS_HOLD_OK =>'HOLD_OK',
            self::STATUS_DMS_HOLD_FAILED =>'DMS_HOLD_FAILED',
            self::STATUS_SMS_FAILED_SMS =>'SMS_FAILED_SMS',
            self::STATUS_DMS_CHARGE_FAILED =>'DMS_CHARGE_FAILED',
            self::STATUS_SUCCESS =>'SUCCESS',
            self::STATUS_EXPIRED =>'EXPIRED',
            self::STATUS_HOLD_EXPIRED =>'HOLD_EXPIRED',
            self::STATUS_REFUND_FAILED =>'REFUND_FAILED',
            self::STATUS_REFUND_PENDING =>'REFUND_PENDING',
            self::STATUS_REFUND_SUCCESS =>'REFUND_SUCCESS',
            self::STATUS_DMS_CANCEL_OK =>'DMS_CANCEL_OK',
            self::STATUS_DMS_CANCEL_FAILED =>'DMS_CANCEL_FAILED',
            self::STATUS_REVERSED =>'REVERSED',
            self::STATUS_INPUT_VALIDATION_FAILED =>'INPUT_VALIDATION_FAILED',
            self::STATUS_BR_VALIDATION_FAILED =>'BR_VALIDATION_FAILED',
            self::STATUS_TERMINAL_GROUP_SELECT_FAILED =>'TERMINAL_GROUP_SELECT_FAILED',
            self::STATUS_TERMINAL_SELECT_FAILED =>'TERMINAL_SELECT_FAILED',
            self::STATUS_DECLINED_BY_BR_ACTION =>'DECLINED_BY_BR_ACTION',
            self::STATUS_WAITING_CARD_FORM_FILL =>'WAITING_CARD_FORM_FILL',
            self::STATUS_MPI_URL_GENERATED =>'MPI_URL_GENERATED',
            self::STATUS_WAITING_MPI =>'WAITING_MPI',
            self::STATUS_MPI_FAILED =>'MPI_FAILED',
            self::STATUS_MPI_NOT_REACHABLE =>'MPI_NOT_REACHABLE',
            self::STATUS_INSIDE_FORM_URL_SENT =>'INSIDE_FORM_URL_SENT',
            self::STATUS_MPI_AUTH_FAILED =>'MPI_AUTH_FAILED',
            self::STATUS_ACQUIRER_NOT_REACHABLE =>'ACQUIRER_NOT_REACHABLE',
            self::STATUS_REVERSAL_FAILED =>'REVERSAL_FAILED',
            self::STATUS_CREDIT_FAILED =>'CREDIT_FAILED',
            self::STATUS_P2P_FAILED =>'P2P_FAILED'
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
            self::METHOD_DMS_CHARGE => 'DmsCharge',
            self::METHOD_DMS_HOLD => 'DmsHold',
            self::METHOD_CREDIT => 'Credit',
            self::METHOD_P2P => 'P2P',
            self::METHOD_RECURRENT_SMS => 'RecurrentSms',
            self::METHOD_RECURRENT_DMS_HOLD => 'RecurrentDms'
        );

        if (array_key_exists($payment_method, $method_names)) {
            return $method_names[$payment_method];
        } else {
            return 'Unknown';
        }
    }

    public function canRefundTransaction($payment_method, $transaction_status)
    {
        return in_array($transaction_status, array(
            self::STATUS_SUCCESS
        ));
    }

    public function canChargeTransaction($payment_method, $transaction_status)
    {
        return in_array($transaction_status, array(
            self::STATUS_HOLD_OK
        ));
    }

    public function canCancelTransaction($payment_method, $transaction_status)
    {
        return in_array($transaction_status, array(
            self::STATUS_HOLD_OK
        ));
    }

    function canReverseTransaction($payment_method, $transaction_status)
    {
        return in_array($transaction_status, array(
            self::STATUS_SUCCESS
        ));
    }

    function isRecurrentMethod($payment_method)
    {
        return in_array($payment_method, array(
            self::METHOD_RECURRENT_SMS,
            self::METHOD_RECURRENT_DMS_HOLD
        ));
    }

    public function isSuccessTransaction($payment_method, $transaction_status)
    {
        if (self::METHOD_DMS_HOLD == $payment_method || self::METHOD_RECURRENT_DMS_HOLD == $payment_method) {
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

    public function createTransaction($customer_email, $customer_phone, $billing_address_country, $billing_address_state, $billing_address_city, $billing_address_street, $billing_address_house, $billing_address_flat, $billing_address_zip, $shipping_address_country, $shipping_address_state, $shipping_address_city, $shipping_address_street, $shipping_address_house, $shipping_address_flat, $shipping_address_zip, $amount, $currency, $order_description, $merchant_url, $user_ip, $pan = null, $card_exp = null, $cvv = null, $cardholder_name = null, $payment_method = null)
    {
        if (empty($payment_method)) {
            $payment_method = $this->config->get('payment_transactpro_payment_method');
        }

        $endpoint_name = $this->getPaymentMethodName($payment_method);
        if ($this->isRecurrentMethod($payment_method)) {
            $endpoint_name = 'Init' . $endpoint_name;
        }
        $endpoint = $this->gateway->{'create' . $endpoint_name}();

        $endpoint->customer()
            ->setEmail($customer_email)
            ->setPhone($customer_phone)
            ->setBillingAddressCountry($billing_address_country)
            ->setBillingAddressState($billing_address_state)
            ->setBillingAddressCity($billing_address_city)
            ->setBillingAddressStreet($billing_address_street)
            ->setBillingAddressHouse($billing_address_house)
            ->setBillingAddressFlat($billing_address_flat)
            ->setBillingAddressZIP($billing_address_zip)
            ->setShippingAddressCountry($shipping_address_country)
            ->setShippingAddressState($shipping_address_state)
            ->setShippingAddressCity($shipping_address_city)
            ->setShippingAddressStreet($shipping_address_street)
            ->setShippingAddressHouse($shipping_address_house)
            ->setShippingAddressFlat($shipping_address_flat)
            ->setShippingAddressZIP($shipping_address_zip);

        $endpoint->order()
            ->setDescription($order_description)
            ->setMerchantSideUrl($merchant_url);

        $endpoint->system()->setUserIP($user_ip);
        //TODO: Remove fake ip address
        $endpoint->system()->setUserIP('86.57.161.11');

        $endpoint->money()
            ->setAmount($amount)
            ->setCurrency($currency);

        $endpoint->paymentMethod()
            ->setPAN($pan)
            ->setExpire($card_exp)
            ->setCVV($cvv)
            ->setCardHolderName($cardholder_name);

        return $this->processEndpoint($endpoint);
    }

    public function createRecurrentTransaction($transaction_id, $amount, $payment_method = null)
    {
        if (empty($payment_method)) {
            $payment_method = $this->config->get('payment_transactpro_payment_method');
        }

        if (self::METHOD_SMS == $payment_method) {
            $payment_method = self::METHOD_RECURRENT_SMS;
        } elseif (self::METHOD_DMS_CHARGE == $payment_method) {
            $payment_method = self::METHOD_RECURRENT_DMS_HOLD;
        }

        $endpoint_name = $this->getPaymentMethodName($payment_method);
        if ($this->canRecurrent($payment_method)) {
            $endpoint_name = 'Init' . $endpoint_name;
        }
        $endpoint = $this->gateway->{'create' . $endpoint_name}();

        $endpoint->command()->setGatewayTransactionID($transaction_id);
        $endpoint->money()->setAmount($amount);

        return $this->processEndpoint($endpoint);
    }

    public function refundTransaction($transaction_id, $amount)
    {
        $refund = $this->gateway->createRefund();
        $refund->command()->setGatewayTransactionID($transaction_id);
        $refund->money()->setAmount($amount);

        return $this->processEndpoint($refund);
    }

    public function chargeDmsHoldTransaction($transaction_id, $amount)
    {
        $charge = $this->gateway->createDmsCharge();
        $charge->command()->setGatewayTransactionID($transaction_id);
        $charge->money()->setAmount($amount);

        return $this->processEndpoint($charge);
    }

    public function cancelDmsHoldTransaction($transaction_id)
    {
        $cancel = $this->gateway->createCancel();
        $cancel->command()->setGatewayTransactionID($transaction_id);

        return $this->processEndpoint($cancel);
    }

    public function reverseSmsTransaction($transaction_id) {
        $reverse = $this->gateway->createReversal();
        $reverse->command()->setGatewayTransactionID($transaction_id);

        return $this->processEndpoint($reverse);
    }

    public function getTransactionsStatusHistory(array $transaction_ids)
    {
        $history = $this->gateway->createHistory();
        if (2 == $this->config->get('payment_transactpro_capture_method')) {
            $history->info()->setGatewayTransactionIDs($transaction_ids);
        } else {
            $history->info()->setMerchantTransactionIDs($transaction_ids);
        }
        return $this->processEndpoint($history);
    }

    public function getTransactionsStatus(array $transaction_ids)
    {
        $status = $this->gateway->createStatus();
        if (2 == $this->config->get('payment_transactpro_capture_method')) {
            $status->info()->setGatewayTransactionIDs($transaction_ids);
        } else {
            $status->info()->setMerchantTransactionIDs($transaction_ids);
        }
        return $this->processEndpoint($status);
    }

    public function auth(string $account_id, string $secret_key): self
    {
        $this->gateway->auth()
            ->setAccountId($account_id)
            ->setSecretKey($secret_key);
        return $this;
    }

    protected function processEndpoint(\TransactPro\Gateway\Operations\Operation $endpoint)
    {
        $request = $this->gateway->generateRequest($endpoint);
        $response = $this->gateway->process($request);

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
}