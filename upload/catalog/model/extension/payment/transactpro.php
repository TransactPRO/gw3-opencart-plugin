<?php

class ModelExtensionPaymentTransactpro extends Model
{
    const CAPTURE_API = 1;
    const CAPTURE_GW = 2;

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
        return self::CAPTURE_GW == $this->config->get('payment_transactpro_capture_method');
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
}
