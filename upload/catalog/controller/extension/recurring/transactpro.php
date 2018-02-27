<?php

class ControllerExtensionRecurringTransactpro extends Controller
{

    public function index()
    {
        $this->load->language('extension/recurring/transactpro');

        $this->load->model('account/recurring');
        $this->load->model('extension/payment/transactpro');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);

        if ($recurring_info) {
            $data['cancel_url'] = html_entity_decode($this->url->link('extension/recurring/transactpro/cancel', 'order_recurring_id=' . $order_recurring_id, 'SSL'));

            $data['continue'] = $this->url->link('account/recurring', '', true);

            if ($recurring_info['status'] == ModelExtensionPaymentTransactpro::RECURRING_ACTIVE) {
                $data['order_recurring_id'] = $order_recurring_id;
            } else {
                $data['order_recurring_id'] = '';
            }

            return $this->load->view('extension/recurring/transactpro', $data);
        }
    }

    public function cancel()
    {
        $this->load->language('extension/recurring/transactpro');

        $this->load->model('account/recurring');
        $this->load->model('extension/payment/transactpro');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $json = array();

        $recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);

        if ($recurring_info) {
            $this->model_account_recurring->editOrderRecurringStatus($order_recurring_id, ModelExtensionPaymentTransactpro::RECURRING_CANCELLED);

            $this->load->model('checkout/order');

            $order_info = $this->model_checkout_order->getOrder($recurring_info['order_id']);

            $this->model_checkout_order->addOrderHistory($recurring_info['order_id'], $order_info['order_status_id'], $this->language->get('text_order_history_cancel'), true);

            $json['success'] = $this->language->get('text_canceled');
        } else {
            $json['error'] = $this->language->get('error_not_found');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function recurring()
    {
        $this->load->language('extension/payment/transactpro');

        $this->load->model('extension/payment/transactpro');

        if (! $this->model_extension_payment_transactpro->validateCRON()) {
            return;
        }
        $this->load->library('transactpro');

        $result = array(
            'transaction_success' => array(),
            'transaction_error' => array(),
            'transaction_fail' => array()
        );

        $this->load->model('checkout/order');

        foreach ($this->model_extension_payment_transactpro->nextRecurringPayments() as $payment) {
            $payment_method = $this->transactpro->getRecurrentMethod($payment['payment_method']);
            $amount = $payment['amount'];
            $currency = $payment['currency'];
            $transaction_guid = $payment['transaction_guid'];

            try {
                if (TRUE == $payment['is_free']) {
                    $amount = 0;
                    $transaction_guid = '';
                } else {
                    try {
                        $json = $this->transactpro->createRecurrentTransaction($payment_method, $transaction_guid, $amount);

                        $transaction_guid = $json['gw']['gateway-transaction-id'];
                        $this->session->data['transaction_guid'] = $transaction_guid;
                        $transaction_status = $json['gw']['status-code'];

                        $this->model_extension_payment_transactpro->addTransaction($transaction_guid, $transaction_status, $payment_method, $amount, $currency, $payment['order_id'], '127.0.0.1');
                    } catch (Exception $e) {
                        $transaction_status = Transactpro::STATUS_GATEWAY_ERROR;
                        $result['transaction_error'][] = '[ID: ' . $payment['order_recurring_id'] . '] - ' . $e->getMessage();
                    }
                }

                if ($this->transactpro->isSuccessTransaction($payment_method, $transaction_status)) {
                    $type = ModelExtensionPaymentTransactpro::RECURRING_TRANSACTION_TYPE_PAYMENT;
                } else {
                    $type = ModelExtensionPaymentTransactpro::RECURRING_TRANSACTION_TYPE_FAILED;
                }
                $this->model_extension_payment_transactpro->addRecurringTransaction($payment['order_recurring_id'], $transaction_guid, $amount, $currency, $type);

                $trial_expired = FALSE;
                $recurring_expired = FALSE;
                $profile_suspended = FALSE;

                $amount = $this->transactpro->standardDenomination($amount, $currency);

                if ($this->transactpro->isSuccessTransaction($payment_method, $transaction_status)) {
                    $trial_expired = $this->model_extension_payment_transactpro->updateRecurringTrial($payment['order_recurring_id']);
                    $recurring_expired = $this->model_extension_payment_transactpro->updateRecurringExpired($payment['order_recurring_id']);
                    $result['transaction_success'][$payment['order_recurring_id']] = $this->currency->format($amount, $currency);
                } else {
                    // Transaction was not successful. Suspend the recurring profile.
                    $profile_suspended = $this->model_extension_payment_transactpro->suspendRecurringProfile($payment['order_recurring_id']);
                    $result['transaction_fail'][$payment['order_recurring_id']] = $this->currency->format($amount, $currency);
                }

                $transaction_status_name = $this->transactpro->getTransactionStatusName($transaction_status);
                $order_status_id = $this->config->get('payment_transactpro_transaction_status_' . strtolower($transaction_status_name));

                if ($order_status_id) {
                    if (! $payment['is_free']) {
                        $order_status_comment = $this->language->get('transaction_status_' . strtolower($transaction_status_name) . '_comment');
                    } else {
                        $order_status_comment = '';
                    }

                    if ($profile_suspended) {
                        $order_status_comment .= $this->language->get('text_recurring_profile_suspended');
                    }

                    if ($trial_expired) {
                        $order_status_comment .= $this->language->get('text_recurring_trial_expired');
                    }

                    if ($recurring_expired) {
                        $order_status_comment .= $this->language->get('text_recurring_expired');
                    }

                    if ($this->transactpro->isSuccessTransaction($payment_method, $transaction_status)) {
                        $notify = (bool) $this->config->get('payment_transactpro_notify_recurring_success');
                    } else {
                        $notify = (bool) $this->config->get('payment_transactpro_notify_recurring_fail');
                    }

                    $this->model_checkout_order->addOrderHistory($payment['order_id'], $order_status_id, $order_status_comment, $notify);
                }
            } catch (Exception $e) {
                $result['transaction_error'][] = '[ID: ' . $payment['order_recurring_id'] . '] - ' . $e->getMessage();
            }
        }
        ;
        if ($this->config->get('payment_transactpro_cron_email_status')) {
            $this->model_extension_payment_transactpro->cronEmail($result);
        }
    }
}