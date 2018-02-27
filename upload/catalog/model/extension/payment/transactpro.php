<?php

class ModelExtensionPaymentTransactpro extends Model
{
    const RECURRING_ACTIVE = 1;
    const RECURRING_INACTIVE = 2;
    const RECURRING_CANCELLED = 3;
    const RECURRING_SUSPENDED = 4;
    const RECURRING_EXPIRED = 5;
    const RECURRING_PENDING = 6;

    const RECURRING_TRANSACTION_TYPE_DATE_ADDED = 0;
    const RECURRING_TRANSACTION_TYPE_PAYMENT = 1;
    const RECURRING_TRANSACTION_TYPE_OUTSTANDING_PAYMENT = 2;
    const RECURRING_TRANSACTION_TYPE_SKIPPED = 3;
    const RECURRING_TRANSACTION_TYPE_FAILED = 4;
    const RECURRING_TRANSACTION_TYPE_CANCELLED = 5;
    const RECURRING_TRANSACTION_TYPE_SUSPENDED = 6;
    const RECURRING_TRANSACTION_TYPE_SUSPENDED_FAILED = 7;
    const RECURRING_TRANSACTION_TYPE_OUTSTANDING_FAILED = 8;
    const RECURRING_TRANSACTION_TYPE_EXPIRED = 9;

    public function getMethod($address, $total)
    {
        $geo_zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int) $this->config->get('payment_transactpro_geo_zone_id') . "' AND country_id = '" . (int) $address['country_id'] . "' AND (zone_id = '" . (int) $address['zone_id'] . "' OR zone_id = '0')");

        $transactpro_display_name = $this->config->get('payment_transactpro_display_name');

        $this->load->language('extension/payment/transactpro');

        if (! empty($transactpro_display_name[$this->config->get('config_language_id')])) {
            $title = $transactpro_display_name[$this->config->get('config_language_id')];
        } else {
            $title = $this->language->get('text_default_transactpro_name');
        }

        $status = true;

        $minimum_total = (float) $this->config->get('payment_transactpro_total');

        $transactpro_geo_zone_id = $this->config->get('payment_transactpro_geo_zone_id');

        if ($minimum_total > 0 && $minimum_total > $total) {
            $status = false;
        } else if (empty($transactpro_geo_zone_id)) {
            $status = true;
        } else if ($geo_zone_query->num_rows == 0) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code' => 'transactpro',
                'title' => $title,
                'terms' => '',
                'sort_order' => (int) $this->config->get('payment_transactpro_sort_order')
            );
        }

        return $method_data;
    }

    public function isCaptureOnGatewaySide()
    {
        $this->load->library('transactpro');

        return Transactpro::CAPTURE_GW == $this->config->get('payment_transactpro_capture_method');
    }

    public function addTransaction($transaction_id, $transaction_status, $payment_method, $amount, $currency, $order_id, $user_ip)
    {
        $this->load->library('transactpro');

        $amount = $this->transactpro->standardDenomination($amount, $currency);
        $this->db->query("INSERT INTO `" . DB_PREFIX . "transactpro_transaction` SET transaction_guid='" . $this->db->escape($transaction_id) . "', order_id='" . (int) $order_id . "', transaction_status='" . (int) $transaction_status . "', payment_method='" . (int) $payment_method . "', transaction_amount='" . (float) $amount . "', transaction_currency='" . $this->db->escape($currency) . "', device_ip='" . $this->db->escape($user_ip) . "', created_at=NOW(), is_refunded='0', refunded_at='', refunds='" . $this->db->escape(json_encode(array())) . "'");
    }

    public function getTransactions($filters = array())
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "transactpro_transaction` WHERE 1";

        if (isset($filters['order_id'])) {
            $sql .= " AND order_id='" . (int) $filters['order_id'] . "'";
        }

        if (isset($filters['transaction_guid'])) {
            $sql .= " AND transaction_guid='" . $this->db->escape($filters['transaction_guid']) . "'";
        }

        $sql .= " ORDER BY created_at DESC";

        if (isset($filters['start']) && isset($filters['limit'])) {
            $sql .= " LIMIT " . $filters['start'] . ', ' . $filters['limit'];
        }

        return $this->db->query($sql)->rows;
    }

    public function updateTransactionStatus($transaction_id, $transaction_status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "transactpro_transaction` SET transaction_status='" . (int) $transaction_status . "' WHERE transaction_id='" . (int) $transaction_id . "'");
    }

    public function recurringPayments()
    {
        return (bool) $this->config->get('payment_transactpro_recurring_status');
    }

    public function validateCRON()
    {
        if (! $this->config->get('payment_transactpro_status') || ! $this->config->get('payment_transactpro_recurring_status')) {
            return false;
        }

        if (isset($this->request->get['cron_token']) && $this->request->get['cron_token'] == $this->config->get('payment_transactpro_cron_token')) {
            return true;
        }

        if (defined('TRANSACTPRO_ROUTE')) {
            return true;
        }

        return false;
    }

    public function nextRecurringPayments()
    {
        $payments = array();

        $this->load->model('checkout/order');

        $this->load->library('transactpro');

        $recurring_sql = "SELECT * FROM `" . DB_PREFIX . "order_recurring` `or` INNER JOIN `" . DB_PREFIX . "transactpro_transaction` st ON (st.transaction_guid = `or`.reference) WHERE `or`.status='" . self::RECURRING_ACTIVE . "'";

        foreach ($this->db->query($recurring_sql)->rows as $recurring) {
            if (! $this->paymentIsDue($recurring['order_recurring_id'])) {
                continue;
            }

            $order_info = $this->model_checkout_order->getOrder($recurring['order_id']);

            $price = (float) ($recurring['trial'] ? $recurring['trial_price'] : $recurring['recurring_price']);

            $payments[] = array(
                'is_free' => 0 == $price,
                'transaction_guid' => $recurring['transaction_guid'],
                'payment_method' => $recurring['payment_method'],
                'amount' => $this->transactpro->lowestDenomination($price * $recurring['product_quantity'], $recurring['transaction_currency']),
                'currency' => $recurring['transaction_currency'],
                'order_id' => $recurring['order_id'],
                'order_recurring_id' => $recurring['order_recurring_id']
            );
        }

        return $payments;
    }

    public function createRecurringOrder($recurring, $order_id, $description, $reference)
    {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring` SET `order_id` = '" . (int) $order_id . "', `date_added` = NOW(), `status` = '" . self::RECURRING_ACTIVE . "', `product_id` = '" . (int) $recurring['product_id'] . "', `product_name` = '" . $this->db->escape($recurring['name']) . "', `product_quantity` = '" . $this->db->escape($recurring['quantity']) . "', `recurring_id` = '" . (int) $recurring['recurring']['recurring_id'] . "', `recurring_name` = '" . $this->db->escape($recurring['recurring']['name']) . "', `recurring_description` = '" . $this->db->escape($description) . "', `recurring_frequency` = '" . $this->db->escape($recurring['recurring']['frequency']) . "', `recurring_cycle` = '" . (int) $recurring['recurring']['cycle'] . "', `recurring_duration` = '" . (int) $recurring['recurring']['duration'] . "', `recurring_price` = '" . (float) $recurring['recurring']['price'] . "', `trial` = '" . (int) $recurring['recurring']['trial'] . "', `trial_frequency` = '" . $this->db->escape($recurring['recurring']['trial_frequency']) . "', `trial_cycle` = '" . (int) $recurring['recurring']['trial_cycle'] . "', `trial_duration` = '" . (int) $recurring['recurring']['trial_duration'] . "', `trial_price` = '" . (float) $recurring['recurring']['trial_price'] . "', `reference` = '" . $this->db->escape($reference) . "'");

        return $this->db->getLastId();
    }

    public function addRecurringTransaction($order_recurring_id, $reference, $amount, $currency, $type)
    {
        $this->load->library('transactpro');

        $amount = $this->transactpro->standardDenomination($amount, $currency);

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET order_recurring_id='" . (int) $order_recurring_id . "', reference='" . $this->db->escape($reference) . "', type='" . (int) $type . "', amount='" . (float) $amount . "', date_added=NOW()");
    }

    public function updateRecurringExpired($order_recurring_id)
    {
        $recurring_info = $this->getRecurring($order_recurring_id);

        if ($recurring_info['trial']) {
            // If we are in trial, we need to check if the trial will end at some point
            $expirable = (bool) $recurring_info['trial_duration'];
        } else {
            // If we are not in trial, we need to check if the recurring will end at some point
            $expirable = (bool) $recurring_info['recurring_duration'];
        }

        // If recurring payment can expire (trial_duration > 0 AND recurring_duration > 0)
        if ($expirable) {
            $number_of_successful_payments = $this->getTotalSuccessfulPayments($order_recurring_id);

            $total_duration = (int) $recurring_info['trial_duration'] + (int) $recurring_info['recurring_duration'];

            // If successful payments exceed total_duration
            if ($number_of_successful_payments >= $total_duration) {
                $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET status='" . self::RECURRING_EXPIRED . "' WHERE order_recurring_id='" . (int) $order_recurring_id . "'");

                return true;
            }
        }

        return false;
    }

    public function updateRecurringTrial($order_recurring_id)
    {
        $recurring_info = $this->getRecurring($order_recurring_id);

        // If recurring payment is in trial and can expire (trial_duration > 0)
        if ($recurring_info['trial'] && $recurring_info['trial_duration']) {
            $number_of_successful_payments = $this->getTotalSuccessfulPayments($order_recurring_id);

            // If successful payments exceed trial_duration
            if ($number_of_successful_payments >= $recurring_info['trial_duration']) {
                $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET trial='0' WHERE order_recurring_id='" . (int) $order_recurring_id . "'");

                return true;
            }
        }

        return false;
    }

    public function suspendRecurringProfile($order_recurring_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET status='" . self::RECURRING_SUSPENDED . "' WHERE order_recurring_id='" . (int) $order_recurring_id . "'");

        return true;
    }

    private function getLastSuccessfulRecurringPaymentDate($order_recurring_id)
    {
        $this->load->library('transactpro');

        return $this->db->query("SELECT date_added FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id='" . (int) $order_recurring_id . "' AND type IN ('" . Transactpro::STATUS_SUCCESS . "', '" . Transactpro::STATUS_HOLD_OK . "') ORDER BY date_added DESC LIMIT 0,1")->row['date_added'];
    }

    private function getRecurring($order_recurring_id)
    {
        $recurring_sql = "SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE order_recurring_id='" . (int) $order_recurring_id . "'";

        return $this->db->query($recurring_sql)->row;
    }

    private function getTotalSuccessfulPayments($order_recurring_id)
    {
        $this->load->library('transactpro');

        return $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id='" . (int) $order_recurring_id . "' AND type IN ('" . Transactpro::STATUS_SUCCESS . "', '" . Transactpro::STATUS_HOLD_OK . "')")->row['total'];
    }

    private function paymentIsDue($order_recurring_id)
    {
        // We know the recurring profile is active.
        $recurring_info = $this->getRecurring($order_recurring_id);

        if ($recurring_info['trial']) {
            $frequency = $recurring_info['trial_frequency'];
            $cycle = (int) $recurring_info['trial_cycle'];
        } else {
            $frequency = $recurring_info['recurring_frequency'];
            $cycle = (int) $recurring_info['recurring_cycle'];
        }
        // Find date of last payment
        if (! $this->getTotalSuccessfulPayments($order_recurring_id)) {
            $previous_time = strtotime($recurring_info['date_added']);
        } else {
            $previous_time = strtotime($this->getLastSuccessfulRecurringPaymentDate($order_recurring_id));
        }

        switch ($frequency) {
            case 'day':
                $time_interval = 24 * 3600;
                break;
            case 'week':
                $time_interval = 7 * 24 * 3600;
                break;
            case 'semi_month':
                $time_interval = 15 * 24 * 3600;
                break;
            case 'month':
                $time_interval = 30 * 24 * 3600;
                break;
            case 'year':
                $time_interval = 365 * 24 * 3600;
                break;
        }

        $due_date = date('Y-m-d', $previous_time + ($time_interval * $cycle));

        $this_date = date('Y-m-d');

        return $this_date >= $due_date;
    }

    public function cronEmail($result)
    {
        $this->load->language('extension/payment/transactpro');

        $mail = new Mail();

        $mail->protocol = $this->config->get('config_mail_protocol');
        $mail->parameter = $this->config->get('config_mail_parameter');

        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $br = '<br />';

        $subject = $this->language->get('text_cron_subject');

        $message = $this->language->get('text_cron_message') . $br . $br;

        if (! empty($result['transaction_error'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_error_heading') . '</strong>' . $br;

            $message .= implode($br, $result['transaction_error']) . $br . $br;
        }

        if (! empty($result['transaction_fail'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_fail_heading') . '</strong>' . $br;

            foreach ($result['transaction_fail'] as $order_recurring_id => $amount) {
                $message .= sprintf($this->language->get('text_cron_fail_charge'), $order_recurring_id, $amount) . $br;
            }
        }

        if (! empty($result['transaction_success'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_success_heading') . '</strong>' . $br;

            foreach ($result['transaction_success'] as $order_recurring_id => $amount) {
                $message .= sprintf($this->language->get('text_cron_success_charge'), $order_recurring_id, $amount) . $br;
            }
        }

        $mail->setTo($this->config->get('payment_transactpro_cron_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags($message));
        $mail->setHtml($message);
        $mail->send();
    }
}
