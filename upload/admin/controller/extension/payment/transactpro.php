<?php

class ControllerExtensionPaymentTransactpro extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/payment/transactpro');

        $this->load->model('setting/setting');

        if ($this->request->server['HTTPS']) {
            $server = HTTPS_SERVER;
        } else {
            $server = HTTP_SERVER;
        }

        $previous_setting = $this->model_setting_setting->getSetting('payment_transactpro');
        $previous_config = new Config();

        foreach ($previous_setting as $key => $value) {
            $previous_config->set($key, $value);
        }        

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_transactpro', array_merge($previous_setting, $this->request->post));
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $data['status_error']                       = $this->getValidationError('status');
        $data['display_name_error']                 = $this->getValidationError('display_name');
        $data['account_id_error']                   = $this->getValidationError('account_id');
        $data['secret_key_error']                   = $this->getValidationError('secret_key');
        

        $data['payment_transactpro_status']                    = $this->getSettingValue('payment_transactpro_status');
        $data['payment_transactpro_display_name']              = $this->getSettingValue('payment_transactpro_display_name');
        $data['payment_transactpro_gateway_uri']               = $this->getSettingValue('payment_transactpro_gateway_uri');
        $data['payment_transactpro_account_id']                = $this->getSettingValue('payment_transactpro_account_id');
        $data['payment_transactpro_secret_key']                = $this->getSettingValue('payment_transactpro_secret_key');
        $data['payment_transactpro_payment_method']            = $this->getSettingValue('payment_transactpro_payment_method');
        $data['payment_transactpro_capture_method']            = $this->getSettingValue('payment_transactpro_capture_method');
        $data['payment_transactpro_total']                     = $this->getSettingValue('payment_transactpro_total');
        $data['payment_transactpro_geo_zone_id']               = $this->getSettingValue('payment_transactpro_geo_zone_id');
        $data['payment_transactpro_sort_order']                = $this->getSettingValue('payment_transactpro_sort_order');

        $url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
        $data['payment_transactpro_callback_uri'] = $url->link('extension/payment/transactpro/callback', '', false);
        $data['payment_transactpro_redirect_uri'] = $url->link('extension/payment/transactpro/redirect', '', false);
        
        $data['payment_transactpro_transaction_status_init']                         = $this->getSettingValue('payment_transactpro_transaction_status_init');
        $data['payment_transactpro_transaction_status_sent_to_bank']                 = $this->getSettingValue('payment_transactpro_transaction_status_sent_to_bank');
        $data['payment_transactpro_transaction_status_hold_ok']                      = $this->getSettingValue('payment_transactpro_transaction_status_hold_ok');
        $data['payment_transactpro_transaction_status_dms_hold_failed']              = $this->getSettingValue('payment_transactpro_transaction_status_dms_hold_failed');
        $data['payment_transactpro_transaction_status_sms_failed_sms']               = $this->getSettingValue('payment_transactpro_transaction_status_sms_failed_sms');
        $data['payment_transactpro_transaction_status_dms_charge_failed']            = $this->getSettingValue('payment_transactpro_transaction_status_dms_charge_failed');
        $data['payment_transactpro_transaction_status_success']                      = $this->getSettingValue('payment_transactpro_transaction_status_success');
        $data['payment_transactpro_transaction_status_expired']                      = $this->getSettingValue('payment_transactpro_transaction_status_expired');
        $data['payment_transactpro_transaction_status_hold_expired']                 = $this->getSettingValue('payment_transactpro_transaction_status_hold_expired');
        $data['payment_transactpro_transaction_status_refund_failed']                = $this->getSettingValue('payment_transactpro_transaction_status_refund_failed');
        $data['payment_transactpro_transaction_status_refund_pending']               = $this->getSettingValue('payment_transactpro_transaction_status_refund_pending');
        $data['payment_transactpro_transaction_status_refund_success']               = $this->getSettingValue('payment_transactpro_transaction_status_refund_success');
        $data['payment_transactpro_transaction_status_dms_cancel_ok']                = $this->getSettingValue('payment_transactpro_transaction_status_dms_cancel_ok');
        $data['payment_transactpro_transaction_status_dms_cancel_failed']            = $this->getSettingValue('payment_transactpro_transaction_status_dms_cancel_failed');
        $data['payment_transactpro_transaction_status_reversed']                     = $this->getSettingValue('payment_transactpro_transaction_status_reversed');
        $data['payment_transactpro_transaction_status_input_validation_failed']      = $this->getSettingValue('payment_transactpro_transaction_status_input_validation_failed');
        $data['payment_transactpro_transaction_status_br_validation_failed']         = $this->getSettingValue('payment_transactpro_transaction_status_br_validation_failed');
        $data['payment_transactpro_transaction_status_terminal_group_select_failed'] = $this->getSettingValue('payment_transactpro_transaction_status_terminal_group_select_failed');
        $data['payment_transactpro_transaction_status_terminal_select_failed']       = $this->getSettingValue('payment_transactpro_transaction_status_terminal_select_failed');
        $data['payment_transactpro_transaction_status_declined_by_br_action']        = $this->getSettingValue('payment_transactpro_transaction_status_declined_by_br_action');
        $data['payment_transactpro_transaction_status_waiting_card_form_fill']       = $this->getSettingValue('payment_transactpro_transaction_status_waiting_card_form_fill');
        $data['payment_transactpro_transaction_status_mpi_url_generated']            = $this->getSettingValue('payment_transactpro_transaction_status_mpi_url_generated');
        $data['payment_transactpro_transaction_status_waiting_mpi']                  = $this->getSettingValue('payment_transactpro_transaction_status_waiting_mpi');
        $data['payment_transactpro_transaction_status_mpi_failed']                   = $this->getSettingValue('payment_transactpro_transaction_status_mpi_failed');
        $data['payment_transactpro_transaction_status_mpi_not_reachable']            = $this->getSettingValue('payment_transactpro_transaction_status_mpi_not_reachable');
        $data['payment_transactpro_transaction_status_inside_form_url_sent']         = $this->getSettingValue('payment_transactpro_transaction_status_inside_form_url_sent');
        $data['payment_transactpro_transaction_status_mpi_auth_failed']              = $this->getSettingValue('payment_transactpro_transaction_status_mpi_auth_failed');
        $data['payment_transactpro_transaction_status_acquirer_not_reachable']       = $this->getSettingValue('payment_transactpro_transaction_status_acquirer_not_reachable');
        $data['payment_transactpro_transaction_status_reversal_failed']              = $this->getSettingValue('payment_transactpro_transaction_status_reversal_failed');
        $data['payment_transactpro_transaction_status_credit_failed']                = $this->getSettingValue('payment_transactpro_transaction_status_credit_failed');
        $data['payment_transactpro_transaction_status_p2p_failed']                   = $this->getSettingValue('payment_transactpro_transaction_status_p2p_failed');
        

        if (isset($this->error['warning'])) {
            $this->pushAlert(array(
                'type' => 'danger',
                'icon' => 'exclamation-circle',
                'text' => $this->error['warning']
            ));
        }

        // Insert success message from the session
        if (isset($this->session->data['success'])) {
            $this->pushAlert(array(
                'type' => 'success',
                'icon' => 'exclamation-circle',
                'text' => $this->session->data['success']
            ));

            unset($this->session->data['success']);
        }

        $tabs = array(
            'tab-transaction',
            'tab-setting',
            'tab-recurring',
            'tab-cron'
        );

        if (isset($this->request->get['tab']) && in_array($this->request->get['tab'], $tabs)) {
            $data['tab'] = $this->request->get['tab'];
        } else if (isset($this->error['cron_email']) || isset($this->error['cron_acknowledge'])) {
            $data['tab'] = 'tab-cron';
        } else if ($this->error) {
            $data['tab'] = 'tab-setting';
        } else {
            $data['tab'] = $tabs[1];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/transactpro', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = html_entity_decode($this->url->link('extension/payment/transactpro', 'user_token=' . $this->session->data['user_token'], true));
        $data['cancel'] = html_entity_decode($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
        $data['url_list_transactions'] = html_entity_decode($this->url->link('extension/payment/transactpro/transactions', 'user_token=' . $this->session->data['user_token'] . '&page={PAGE}', true));

        $this->load->model('localisation/language');
        $data['languages'] = array();
        foreach ($this->model_localisation_language->getLanguages() as $language) {
            $data['languages'][] = array(
                'language_id' => $language['language_id'],
                'name' => $language['name'] . ($language['code'] == $this->config->get('config_language') ? $this->language->get('text_default') : ''),
                'image' => 'language/' . $language['code'] . '/'. $language['code'] . '.png'
            );
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $data['payment_transactpro_cron_command'] = PHP_BINDIR . '/php -d session.save_path=' . session_save_path() . ' ' . DIR_SYSTEM . 'library/transactpro/cron.php ' . parse_url($server, PHP_URL_HOST) . ' 443 > /dev/null 2> /dev/null';
        
        if (!$this->config->get('payment_transactpro_cron_token')) {
            $data['payment_transactpro_cron_token'] = md5(mt_rand());
        }

        $data['payment_transactpro_cron_uri'] = str_replace('&amp;', '&', $url->link('extension/recurring/transactpro/recurring', 'cron_token={CRON_TOKEN}', false));

        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);
            $session->start();
            $this->model_user_api->deleteApiSessionBySessonId($session->getId());
            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
            $session->data['api_id'] = $api_info['api_id'];
            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['alerts'] = $this->pullAlerts();

        $this->clearAlerts();

        $this->response->setOutput($this->load->view('extension/payment/transactpro', $data));
    }
    
    public function transactions() {
        $this->load->language('extension/payment/transactpro');

        $this->load->model('extension/payment/transactpro');
        
        $this->load->library('transactpro');

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $result = array(
            'transactions' => array(),
            'pagination' => ''
        );

        $filter_data = array(
            'start' => ($page - 1) * (int)$this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        if (isset($this->request->get['order_id'])) {
            $filter_data['order_id'] = $this->request->get['order_id'];
        }

        $transactions_total = $this->model_extension_payment_transactpro->getTotalTransactions($filter_data);
        $transactions = $this->model_extension_payment_transactpro->getTransactions($filter_data);

        $this->load->model('sale/order');

        foreach ($transactions as $transaction) {
            $amount = $this->currency->format($transaction['transaction_amount'], $transaction['transaction_currency']);

            $order_info = $this->model_sale_order->getOrder($transaction['order_id']);
            
            $result['transactions'][] = array(
                'transaction_id' => $transaction['transaction_id'],
                'transaction_guid' => $transaction['transaction_guid'],
                'url_order' => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $transaction['order_id'], true),
                'url_charge' => $this->url->link('extension/payment/transactpro/charge', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id'], true),
                'url_cancel' => $this->url->link('extension/payment/transactpro/cancel', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id'], true),
                'url_refund' => $this->url->link('extension/payment/transactpro/refund', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id'], true),
                'confirm_charge' => sprintf($this->language->get('label_confirm_charge'), $amount),
                'confirm_cancel' => sprintf($this->language->get('label_confirm_cancel'), $amount),
                'confirm_refund' => $this->language->get('label_confirm_refund'),
                'can_charge' => $this->transactpro->canCharge($transaction['transaction_status']), 
                'can_cancel' => $this->transactpro->canCancel($transaction['transaction_status']) || $this->transactpro->canReverse($transaction['transaction_status']),
                'can_refund' => $this->transactpro->canRefund($transaction['transaction_status']),
                'insert_amount' => sprintf($this->language->get('label_insert_amount'), $amount, $transaction['transaction_currency']),
                'order_id' => $transaction['order_id'],
                'type' => $this->transactpro->getPaymentMethodName($transaction['payment_method']),
                'status' => $this->transactpro->getTransactionStatusName($transaction['transaction_status']),
                'num_refunds' => count(@json_decode($transaction['refunds'], true)),
                'amount' => $amount,
                'customer' => $order_info['firstname'] . ' ' . $order_info['lastname'],
                'ip' => $transaction['device_ip'],
                'date_created' => date($this->language->get('datetime_format'), strtotime($transaction['created_at'])),
                'url_info' => $this->url->link('extension/payment/transactpro/transaction_info', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction['transaction_id'], true)
            );
        }

        $pagination = new Pagination();
        $pagination->total = $transactions_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = '{page}';

        $result['pagination'] = $pagination->render();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }

    public function transaction_info() {
        $this->load->language('extension/payment/transactpro');
        
        $this->load->model('extension/payment/transactpro');
        
        $this->load->library('transactpro');
        
        if (isset($this->request->get['transaction_id'])) {
            $transaction_id = $this->request->get['transaction_id'];
        } else {
            $transaction_id = 0;
        }
        
        $transaction_info = $this->model_extension_payment_transactpro->getTransaction($transaction_id);
        
        if (empty($transaction_info)) {
            $this->response->redirect($this->url->link('extension/payment/transactpro', 'user_token=' . $this->session->data['user_token'], true));
        }
        
        $this->document->setTitle(sprintf($this->language->get('heading_title_transaction'), $transaction_info['transaction_id']));
        
        $data['alerts'] = $this->pullAlerts();
        
        $this->clearAlerts();
        
        $data['text_edit'] = sprintf($this->language->get('heading_title_transaction'), $transaction_info['transaction_id']);
        
        $amount = $this->currency->format($transaction_info['transaction_amount'], $transaction_info['transaction_currency']);
        
        $data['confirm_charge'] = sprintf($this->language->get('label_confirm_charge'), $amount);
        $data['confirm_cancel'] = sprintf($this->language->get('label_confirm_cancel'), $amount);
        $data['confirm_refund'] = $this->language->get('label_confirm_refund');
        $data['insert_amount'] = sprintf($this->language->get('label_insert_amount'), $amount, $transaction_info['transaction_currency']);
        $data['text_loading'] = $this->language->get('text_loading_short');
        
        $data['transaction_id'] = $transaction_info['transaction_guid'];
        $data['order_id'] = $transaction_info['order_id'];
        $data['type'] = $this->transactpro->getPaymentMethodName($transaction_info['payment_method']);
        $data['amount'] = $amount;
        $data['currency'] = $transaction_info['transaction_currency'];
        $data['status'] = $this->transactpro->getTransactionStatusName($transaction_info['transaction_status']);
        $data['ip'] = $transaction_info['device_ip'];
        $data['date_created'] = date($this->language->get('datetime_format'), strtotime($transaction_info['created_at']));
        
        $data['cancel'] = $this->url->link('extension/payment/transactpro', 'user_token=' . $this->session->data['user_token'] . '&tab=tab-transaction', true);
        
        $data['url_order'] = $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $transaction_info['order_id'], true);
        $data['url_charge'] = $this->url->link('extension/payment/transactpro' . '/charge', 'user_token=' . $this->session->data['user_token'] . '&preserve_alert=true&transaction_id=' . $transaction_info['transaction_id'], true);
        $data['url_cancel'] = $this->url->link('extension/payment/transactpro' . '/cancel', 'user_token=' . $this->session->data['user_token'] . '&preserve_alert=true&transaction_id=' . $transaction_info['transaction_id'], true);
        $data['url_refund'] = $this->url->link('extension/payment/transactpro' . '/refund', 'user_token=' . $this->session->data['user_token'] . '&preserve_alert=true&transaction_id=' . $transaction_info['transaction_id'], true);
        
        $data['can_charge'] = $this->transactpro->canCharge($transaction_info['transaction_status']);
        $data['can_cancel'] = $this->transactpro->canCancel($transaction_info['transaction_status']) || $this->transactpro->canReverse($transaction_info['transaction_status']);
        $data['can_refund'] = $this->transactpro->canRefund($transaction_info['transaction_status']);
        
        $data['has_refunds'] = (bool)$transaction_info['is_refunded'];
        
        if ($data['has_refunds']) {
            $refunds = @json_decode($transaction_info['refunds'], true);
            
            $data['refunds'] = array();
            
            $data['heading_refunds'] = sprintf($this->language->get('heading_refunds'), count($refunds));
            
            foreach ($refunds as $refund) {
                $amount = $this->currency->format($refund['amount'], $transaction_info['transaction_currency']);
                
                $data['refunds'][] = array(
                    'transaction_id' => $refund['transaction_guid'],
                    'date_created' => date($this->language->get('datetime_format'), strtotime($refund['created_at'])),
                    'reason' => $refund['reason'],
                    'status' => $this->transactpro->getTransactionStatusName($refund['transaction_status']),
                    'amount' => $amount
                );
            }
        }
        
        $data['breadcrumbs'] = array();
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/transactpro', 'user_token=' . $this->session->data['user_token'], true)
        );
        
        $data['breadcrumbs'][] = array(
            'text' => sprintf($this->language->get('heading_title_transaction'), $transaction_info['transaction_id']),
            'href' => $this->url->link('extension/payment/transactpro/transaction_info', 'user_token=' . $this->session->data['user_token'] . '&transaction_id=' . $transaction_id, true)
        );
        
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;
        
        // API login
        $this->load->model('user/api');
        
        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));
        
        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);
            
            $session->start();
            
            $this->model_user_api->deleteApiSessionBySessonId($session->getId());
            
            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
            
            $session->data['api_id'] = $api_info['api_id'];
            
            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/payment/transactpro_transaction_info', $data));
    }

    public function refund()
    {
        $this->transactionAction(function ($transaction_info, &$json) {
            if (! empty($this->request->post['reason'])) {
                $reason = $this->request->post['reason'];
            } else {
                $reason = $this->language->get('error_no_reason_provided');
            }
            
            if (! empty($this->request->post['amount'])) {
                $amount = preg_replace('~[^0-9\.\,]~', '', $this->request->post['amount']);
                
                if (strpos($amount, ',') !== FALSE && strpos($amount, '.') !== FALSE) {
                    $amount = (float) str_replace(',', '', $amount);
                } else if (strpos($amount, ',') !== FALSE && strpos($amount, '.') === FALSE) {
                    $amount = (float) str_replace(',', '.', $amount);
                } else {
                    $amount = (float) $amount;
                }
            } else {
                $amount = 0;
            }
            
            $refund_amount = $this->transactpro->lowestDenomination($amount, $transaction_info['transaction_currency']);
            try {
                $json = $this->transactpro->refundTransaction($transaction_info['transaction_guid'], $refund_amount);
                
                $refund_transaction_guid = $json['gw']['gateway-transaction-id'];
                $refund_transaction_status = $json['gw']['status-code'];
                
                $refunds = array();
                
                if (! empty($transaction_info['refunds'])) {
                    $refunds = json_decode($transaction_info['refunds'], true);
                }
                
                $refunds[] = array(
                    'transaction_guid' => $refund_transaction_guid,
                    'transaction_status' => $refund_transaction_status,
                    'amount' => $amount,
                    'reason' => $reason,
                    'created_at' => date('Y-m-d H:i:s')
                );
                
                $this->model_extension_payment_transactpro->updateTransactionRefunds($transaction_info['transaction_id'], $refunds);
                
                if (Transactpro::STATUS_REFUND_SUCCESS == $refund_transaction_status) {
                    $refunded_amount = $this->currency->format($amount, $transaction_info['transaction_currency']);
                    $status_name = $this->transactpro->getTransactionStatusName($refund_transaction_status);
                    $comment = sprintf($this->language->get('text_refunded_amount'), $refunded_amount, $status_name, $reason);
                    
                    $json['order_history_data'] = array(
                        'notify' => 1,
                        'order_id' => $transaction_info['order_id'],
                        'order_status_id' => $this->model_extension_payment_transactpro->getOrderStatusId($transaction_info['order_id']),
                        'comment' => $comment
                    );
                    
                    $json['success'] = $this->language->get('text_success_refund');
                } else {
                    $json['error'] = $this->language->get('error_no_refund');
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        });
    }

    public function cancel()
    {
        $this->transactionAction(function ($transaction_info, &$json) {
            try {
                if (Transactpro::METHOD_SMS == $transaction_info['payment_method']) {
                    $json = $this->transactpro->reverseSmsTransaction($transaction_info['transaction_guid']);
                } else {
                    $json = $this->transactpro->cancelDmsHoldTransaction($transaction_info['transaction_guid']);
                }

                $transaction_status = $json['gw']['status-code'];
                $status_name = $this->transactpro->getTransactionStatusName($transaction_status);
                $comment = $this->language->get('transaction_status_' . strtolower($status_name) . '_help');
                
                if (Transactpro::STATUS_REVERSED == $transaction_status || Transactpro::STATUS_DMS_CANCEL_OK == $transaction_status) {
                    $this->model_extension_payment_transactpro->updateTransactionStatus($transaction_info['transaction_id'], $transaction_status);
                    
                    $json['order_history_data'] = array(
                        'notify' => 1,
                        'order_id' => $transaction_info['order_id'],
                        'order_status_id' => $this->model_extension_payment_transactpro->getOrderStatusId($transaction_info['order_id'], $transaction_status),
                        'comment' => $comment
                    );
                    
                    $json['success'] = $this->language->get('text_success_cancel');
                } else {
                    $json['error'] = $comment;
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        });
    }

    public function charge()
    {
        $this->transactionAction(function ($transaction_info, &$json) {
            $charge_amount = $this->transactpro->lowestDenomination($transaction_info['transaction_amount'], $transaction_info['transaction_currency']);
            try {
                $json = $this->transactpro->chargeDmsHoldTransaction($transaction_info['transaction_guid'], $charge_amount);
                
                $transaction_status = $json['gw']['status-code'];
                $status_name = $this->transactpro->getTransactionStatusName($transaction_status);
                $comment = $this->language->get('transaction_status_' . strtolower($status_name) . '_help');
                
                if (Transactpro::STATUS_SUCCESS == $transaction_status) {
                    $this->model_extension_payment_transactpro->updateTransactionStatus($transaction_info['transaction_id'], $transaction_status);
                    
                    $json['order_history_data'] = array(
                        'notify' => 1,
                        'order_id' => $transaction_info['order_id'],
                        'order_status_id' => $this->model_extension_payment_transactpro->getOrderStatusId($transaction_info['order_id'], $transaction_status),
                        'comment' => $comment
                    );
                    
                    $charged_amount = $this->currency->format($transaction_info['transaction_amount'], $transaction_info['transaction_currency']);
                    $json['success'] = sprintf($this->language->get('text_charged_amount'), $charged_amount, $status_name);
                } else {
                    $json['error'] = $comment;
                }
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        });
    }
       
    public function order() {
        $this->load->language('extension/payment/transactpro');

        $data['url_list_transactions'] = html_entity_decode($this->url->link('extension/payment/transactpro/transactions', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $this->request->get['order_id'] . '&page={PAGE}', true));
        $data['user_token'] = $this->session->data['user_token'];
        $data['order_id'] = $this->request->get['order_id'];

        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);
            
            $session->start();
                    
            $this->model_user_api->deleteApiSessionBySessonId($session->getId());
            
            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
            
            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        return $this->load->view('extension/payment/transactpro_order', $data);
    }

    public function install() {
        $this->load->model('extension/payment/transactpro');
        
        $this->model_extension_payment_transactpro->createTables();
    }

    public function uninstall() {
        $this->load->model('extension/payment/transactpro');

        $this->model_extension_payment_transactpro->dropTables();
    }

    public function recurringButtons() {
        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            return;
        }

        $this->load->model('extension/payment/transactpro');

        $this->load->language('extension/payment/transactpro');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = $this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        $recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

        $data['button_text'] = $this->language->get('button_cancel_recurring');

        if ($recurring_info['status'] == ModelExtensionPaymentTransactpro::REFUND_SUCCESS) {
            $data['order_recurring_id'] = $order_recurring_id;
        } else {
            $data['order_recurring_id'] = '';
        }

        $this->load->model('sale/order');

        $order_info = $this->model_sale_order->getOrder($recurring_info['order_id']);

        $data['order_id'] = $recurring_info['order_id'];
        $data['store_id'] = $order_info['store_id'];
        $data['order_status_id'] = $order_info['order_status_id'];
        $data['comment'] = $this->language->get('text_order_history_cancel');
        $data['notify'] = 1;

        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);
            
            $session->start();
                    
            $this->model_user_api->deleteApiSessionBySessonId($session->getId());
            
            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);
            
            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        $data['cancel'] = html_entity_decode($this->url->link('extension/payment/transactpro/cancel', 'order_recurring_id=' . $order_recurring_id . '&user_token=' . $this->session->data['user_token'], true));

        return $this->load->view('extension/payment/transactpro_recurring_buttons', $data);
    }

    public function stop() {
        $this->load->language('extension/payment/transactpro');

        $json = array();
        
        if (!$this->user->hasPermission('modify', 'sale/recurring')) {
            $json['error'] = $this->language->get('error_permission_recurring');
        } else {
            $this->load->model('sale/recurring');
            
            if (isset($this->request->get['order_recurring_id'])) {
                $order_recurring_id = $this->request->get['order_recurring_id'];
            } else {
                $order_recurring_id = 0;
            }
            
            $recurring_info = $this->model_sale_recurring->getRecurring($order_recurring_id);

            if ($recurring_info) {
                $this->load->model('extension/payment/transactpro');

                $this->model_extension_payment_transactpro->editOrderRecurringStatus($order_recurring_id, ModelExtensionPaymenttransactpro::RECURRING_CANCELLED);

                $json['success'] = $this->language->get('text_canceled_success');
                
            } else {
                $json['error'] = $this->language->get('error_not_found');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/payment/transactpro')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['payment_transactpro_account_id']) || !is_numeric($this->request->post['payment_transactpro_account_id'])) {
            $this->error['account_id'] = $this->language->get('account_id_error');
        }

        if (empty($this->request->post['payment_transactpro_secret_key'])) {
            $this->error['secret_key'] = $this->language->get('error_secret_key');
        }

        if ($this->error && empty($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_form');
        }

        return !$this->error;
    }

    protected function transactionAction($callback) {
        $this->load->language('extension/payment/transactpro');

        $this->load->model('extension/payment/transactpro');

        $this->load->library('transactpro');

        $json = array();

        if (!$this->user->hasPermission('modify', 'extension/payment/transactpro')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->get['transaction_id'])) {
            $transaction_id = $this->request->get['transaction_id'];
        } else {
            $transaction_id = 0;
        }

        $transaction_info = $this->model_extension_payment_transactpro->getTransaction($transaction_id);

        if (empty($transaction_info)) {
            $json['error'] = $this->language->get('error_transaction_missing');
        } else {
            try {
                $callback($transaction_info, $json);
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        if (isset($this->request->get['preserve_alert'])) {
            if (!empty($json['error'])) {
                $this->pushAlert(array(
                    'type' => 'danger',
                    'icon' => 'exclamation-circle',
                    'text' => $json['error']
                ));
            }

            if (!empty($json['success'])) {
                $this->pushAlert(array(
                    'type' => 'success',
                    'icon' => 'exclamation-circle',
                    'text' => $json['success']
                ));
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function pushAlert($alert) {
        $this->session->data['payment_transactpro_alerts'][] = $alert;
    }

    protected function pullAlerts() {
        if (isset($this->session->data['payment_transactpro_alerts'])) {
            return $this->session->data['payment_transactpro_alerts'];
        } else {
            return array();
        }
    }

    protected function clearAlerts() {
        unset($this->session->data['payment_transactpro_alerts']);
    }

    protected function getSettingValue($key, $default = null, $checkbox = false) {
        if ($checkbox) {
            if ($this->request->server['REQUEST_METHOD'] == 'POST' && !isset($this->request->post[$key])) {
                return $default;
            } else {
                return $this->config->get($key);
            }
        }

        if (isset($this->request->post[$key])) {
            return $this->request->post[$key]; 
        } else if ($this->config->has($key)) {
            return $this->config->get($key);
        } else {
            return $default;
        }
    }

    protected function getValidationError($key) {
        if (isset($this->error[$key])) {
            return $this->error[$key];
        } else {
            return '';
        }
    }
}