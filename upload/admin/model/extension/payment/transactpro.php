<?php

class ModelExtensionPaymentTransactpro extends Model
{

    const RECURRING_ACTIVE = 1;
    const RECURRING_INACTIVE = 2;
    const RECURRING_CANCELLED = 3;
    const RECURRING_SUSPENDED = 4;
    const RECURRING_EXPIRED = 5;
    const RECURRING_PENDING = 6;

    public function getTransaction($transaction_id)
    {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "transactpro_transaction` WHERE transaction_id='" . (int) $transaction_id . "'")->row;
    }

    public function getTransactions($filters)
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "transactpro_transaction` WHERE 1";

        if (isset($filters['transaction_guid'])) {
            $sql .= " AND transaction_guid='" . (int) $filters['order_id'] . "'";
        }

        if (isset($filters['order_id'])) {
            $sql .= " AND order_id='" . (int) $filters['order_id'] . "'";
        }

        $sql .= " ORDER BY created_at DESC";

        if (isset($filters['start']) && isset($filters['limit'])) {
            $sql .= " LIMIT " . $filters['start'] . ', ' . $filters['limit'];
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotalTransactions($filters)
    {
        $sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "transactpro_transaction` WHERE 1";

        if (isset($filters['transaction_guid'])) {
            $sql .= " AND transaction_guid='" . (int) $filters['order_id'] . "'";
        }

        if (isset($filters['order_id'])) {
            $sql .= " AND order_id='" . (int) $filters['order_id'] . "'";
        }

        return $this->db->query($sql)->row['total'];
    }

    public function updateTransactionRefunds($transaction_id, $refunds)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "transactpro_transaction` SET is_refunded='1', refunded_at=NOW(), refunds='" . $this->db->escape(json_encode($refunds)) . "' WHERE transaction_id='" . (int) $transaction_id . "'");
    }

    public function updateTransactionStatus($transaction_id, $transaction_status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "transactpro_transaction` SET transaction_status='" . (int) $transaction_status . "' WHERE transaction_id='" . (int) $transaction_id . "'");
    }

    public function getOrderStatusId($order_id, $transaction_status = null)
    {
        if ($transaction_status) {
            $this->load->library('transactpro');

            $status_name = $this->transactpro->getTransactionStatusName($transaction_status);
            return $this->config->get('payment_transactpro_transaction_status_' . strtolower($status_name));
        } else {
            $this->load->model('sale/order');

            $order_info = $this->model_sale_order->getOrder($order_id);
            return $order_info['order_status_id'];
        }
    }

    public function editOrderRecurringStatus($order_recurring_id, $status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = '" . (int) $status . "' WHERE `order_recurring_id` = '" . (int) $order_recurring_id . "'");
    }

    public function createTables()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "transactpro_transaction` (
          `transaction_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `transaction_guid` char(40) NOT NULL,
          `order_id` int(11) UNSIGNED NOT NULL,
          `transaction_status` int(11) UNSIGNED NOT NULL,
          `payment_method` int(11) UNSIGNED NOT NULL,
          `transaction_amount` decimal(15,2) NOT NULL,
          `transaction_currency` char(3) NOT NULL,
          `device_ip` char(15) NOT NULL,
          `created_at` datetime NOT NULL,
          `is_refunded` tinyint(1) NOT NULL,
          `refunded_at` datetime NULL,
          `refunds` text NOT NULL,
          PRIMARY KEY (`transaction_id`),
          KEY `transaction_guid` (`transaction_guid`),
          KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    public function dropTables()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "transactpro_transaction`");
    }
}
