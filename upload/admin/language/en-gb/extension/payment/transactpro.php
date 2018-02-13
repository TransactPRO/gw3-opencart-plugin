<?php

// Heading
$_['heading_title']                                     = 'Transact Pro';
$_['heading_settings']                                  = 'Settings';
$_['heading_transaction_statuses']                      = 'Transaction Statuses';
$_['heading_title_transaction']                         = 'View Transaction #%s';
$_['heading_confirm_action']                            = 'Are you sure?';
$_['heading_refund_details']                            = 'Refund details';
$_['heading_refunds']                                   = 'Refunds (%s)';



// Tab
$_['tab_setting']                                       = 'Settings';
$_['tab_transaction']                                   = 'Transactions';
$_['tab_cron']                                          = 'CRON';
$_['tab_recurring']                                     = 'Recurring Payments';

// Entry
$_['extension_status_label']                            = 'Extension status';
$_['extension_status_entry_enabled']                    = 'Enabled';
$_['extension_status_entry_disabled']                   = 'Disabled';
$_['extension_status_help']                             = 'Enable or disable the payment method.';

$_['payment_method_name_label']                         = 'Payment method name';
$_['payment_method_name_entry']                         = 'Credit / Debit Card';
$_['payment_method_name_help']                          = 'Checkout payment method name.';

$_['callback_uri_label']                                = 'Callback URL';
$_['callback_uri_help']                                 = 'Provide this link into the Transact Pro support team.';

$_['redirect_uri_label']                                = 'Redirect URL';
$_['redirect_uri_help']                                 = 'Provide this link into the Transact Pro support team.';

$_['gw_uri_label']                                      = 'Gateway URL';
$_['gw_uri_entry']                                      = 'https://api.transactpro.lv/v3.0';
$_['gw_uri_help']                                       = 'The url of Transact Pro payment gateway. Leave blank to use default value.';

$_['account_id_label']                                  = 'Account ID';
$_['account_id_entry']                                  = 'Account ID';
$_['account_id_help']                                   = 'Get this from the Information/Accounts section on Transact Pro dashboard page.';
$_['account_id_error']                                  = 'The Account ID is a required field.';

$_['secret_key_label']                                  = 'Secret Key';
$_['secret_key_entry']                                  = 'Secret Key';
$_['secret_key_help']                                   = 'Get this from the Information/Accounts section on Transact Pro dashboard page.';
$_['secret_key_error']                                  = 'The Secret Key is a required field';

$_['payment_method_label']                              = 'Payment Method';
$_['payment_method_entry_sms']                          = 'SMS';
$_['payment_method_entry_dms_charge']                   = 'DMS Charge';
$_['payment_method_entry_dms_hold']                     = 'DMS Hold';
$_['payment_method_entry_credit']                       = 'Credit';
$_['payment_method_entry_p2p']                          = 'P2P';

$_['capture_method_label']                              = 'Paymen Infomation Capture';
$_['capture_method_entry_merchant']                     = 'Merchant side';
$_['capture_method_entry_gw']                           = 'Payment gateway side';

$_['total_label']                                       = 'Total';
$_['total_entry']                                       = 'Total';
$_['total_help']                                        = 'The checkout total the order must reach before this payment method becomes active.';

$_['geo_zone_label']                                    = 'Geo Zone';

$_['sort_order_label']                                  = 'Sort Order';
$_['sort_order_entry']                                  = 'Sort Order';

$_['transaction_status_init_label'] =                         'INIT';
$_['transaction_status_sent_to_bank_label'] =                 'SENT_TO_BANK';
$_['transaction_status_hold_ok_label'] =                      'HOLD_OK';
$_['transaction_status_dms_hold_failed_label'] =              'DMS_HOLD_FAILED';
$_['transaction_status_sms_failed_sms_label'] =               'SMS_FAILED_SMS';
$_['transaction_status_dms_charge_failed_label'] =            'DMS_CHARGE_FAILED';
$_['transaction_status_success_label'] =                      'SUCCESS';
$_['transaction_status_expired_label'] =                      'EXPIRED';
$_['transaction_status_hold_expired_label'] =                 'HOLD_EXPIRED';
$_['transaction_status_refund_failed_label'] =                'REFUND_FAILED';
$_['transaction_status_refund_pending_label'] =               'REFUND_PENDING';
$_['transaction_status_refund_success_label'] =               'REFUND_SUCCESS';
$_['transaction_status_dms_cancel_ok_label'] =                'DMS_CANCEL_OK';
$_['transaction_status_dms_cancel_failed_label'] =            'DMS_CANCEL_FAILED';
$_['transaction_status_reversed_label'] =                     'REVERSED';
$_['transaction_status_input_validation_failed_label'] =      'INPUT_VALIDATION_FAILED';
$_['transaction_status_br_validation_failed_label'] =         'BR_VALIDATION_FAILED';
$_['transaction_status_terminal_group_select_failed_label'] = 'TERMINAL_GROUP_SELECT_FAILED';
$_['transaction_status_terminal_select_failed_label'] =       'TERMINAL_SELECT_FAILED';
$_['transaction_status_declined_by_br_action_label'] =        'DECLINED_BY_BR_ACTION';
$_['transaction_status_waiting_card_form_fill_label'] =       'WAITING_CARD_FORM_FILL';
$_['transaction_status_mpi_url_generated_label'] =            'MPI_URL_GENERATED';
$_['transaction_status_waiting_mpi_label'] =                  'WAITING_MPI';
$_['transaction_status_mpi_failed_label'] =                   'MPI_FAILED_3D';
$_['transaction_status_mpi_not_reachable_label'] =            'MPI_NOT_REACHABLE_3D';
$_['transaction_status_inside_form_url_sent_label'] =         'INSIDE_FORM_URL_SENT';
$_['transaction_status_mpi_auth_failed_label'] =              'MPI_AUTH_FAILED_3D';
$_['transaction_status_acquirer_not_reachable_label'] =       'ACQUIRER_NOT_REACHABLE';
$_['transaction_status_reversal_failed_label'] =              'REVERSAL_FAILED';
$_['transaction_status_credit_failed_label'] =                'CREDIT_FAILED';
$_['transaction_status_p2p_failed_label'] =                   'P2P_FAILED';

$_['transaction_status_init_help'] =                          'Successful transaction start.';
$_['transaction_status_sent_to_bank_help'] =                  'Awaiting response from acquirer.';
$_['transaction_status_hold_ok_help'] =                       'Funds successfully reserved.';
$_['transaction_status_dms_hold_failed_help'] =               'Fund reservation failed.';
$_['transaction_status_sms_failed_sms_help'] =                'SMS transaction failed.';
$_['transaction_status_dms_charge_failed_help'] =             'Reserved fund charge failed.';
$_['transaction_status_success_help'] =                       'Funds successfully transferred.';
$_['transaction_status_expired_help'] =                       'Time given to perform current action is expired.';
$_['transaction_status_hold_expired_help'] =                  'Fund reservation is expired.';
$_['transaction_status_refund_failed_help'] =                 'Failed to perform REFUND transaction.';
$_['transaction_status_refund_pending_help'] =                'Refund request is in process.';
$_['transaction_status_refund_success_help'] =                'Successful refund operation.';
$_['transaction_status_dms_cancel_ok_help'] =                 'Reservation successfully canceled.';
$_['transaction_status_dms_cancel_failed_help'] =             'Failed to cancel reserved funds.';
$_['transaction_status_reversed_help'] =                      'Operation successfully reversed.';
$_['transaction_status_input_validation_failed_help'] =       'Invalid payload data provided.';
$_['transaction_status_br_validation_failed_help'] =          'Business rules declined current action.';
$_['transaction_status_terminal_group_select_failed_help'] =  'Failed to select terminal group.';
$_['transaction_status_terminal_select_failed_help'] =        'Failed to select terminal.';
$_['transaction_status_declined_by_br_action_help'] =         'Business rules declined current action.';
$_['transaction_status_waiting_card_form_fill_help'] =        'Transaction is waiting till cardholder enters card data.';
$_['transaction_status_mpi_url_generated_help'] =             'Gateway provided URL to proceed with 3D authentication.';
$_['transaction_status_waiting_mpi_help'] =                   'Transaction is waiting for 3D authentication.';
$_['transaction_status_mpi_failed_help'] =                    '3D authentication failed.';
$_['transaction_status_mpi_not_reachable_help'] =             '3D authentication service is unavailable.';
$_['transaction_status_inside_form_url_sent_help'] =          'Gateway provided URL where inside form resides.';
$_['transaction_status_mpi_auth_failed_help'] =               '3D service declined transaction.';
$_['transaction_status_acquirer_not_reachable_help'] =        'Acquirer service is unavailable.';
$_['transaction_status_reversal_failed_help'] =               'Failed to reverse given transaction.';
$_['transaction_status_credit_failed_help'] =                 'Failed to process credit transaction.';
$_['transaction_status_p2p_failed_help'] =                    'Failed to process P2P transaction.';

// Error
$_['error_permission']                                  = '<strong>Warning:</strong> You do not have permission to modify payment Transact Pro!';
$_['error_permission_recurring']                        = '<strong>Warning:</strong> You do not have permission to modify recurring payments!';
$_['error_form']                                        = 'Please check the form for errors and try to save agian.';
$_['error_no_reason_provided']                          = 'Reason not provided.';
$_['error_no_refund']                                   = 'Refund failed.';

// Text
$_['text_transactpro']                                  = '<a target="_BLANK" href="https://transactpro.lv"><img src="view/image/payment/transactpro.png" alt="Transact Pro" title="Transact Pro" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_success']                                      = 'Success: You have modified Transact Pro payment module!';
$_['text_extension']                                    = 'Extensions';
$_['text_loading_short']                                = 'Please wait...';
$_['text_success_refund']                               = 'Transaction successfully refunded!';
$_['text_success_cancel']                               = 'Transaction successfully canceled / reversed!';
$_['text_refunded_amount']                              = 'Refunded: %s. Status of the refund: %s. Reason for the refund: %s';
$_['text_charged_amount']                               = 'Charged: %s. Status of the charge %s.';
$_['text_no_transactions']                              = 'No transactions have been logged yet.';

// Column
$_['column_transaction_id']                             = 'Transaction ID';
$_['column_order_id']                                   = 'Order ID';
$_['column_customer']                                   = 'Customer';
$_['column_status']                                     = 'Status';
$_['column_type']                                       = 'Type';
$_['column_amount']                                     = 'Amount';
$_['column_ip']                                         = 'IP';
$_['column_date_created']                               = 'Date Created';
$_['column_action']                                     = 'Action';
$_['column_refunds']                                    = 'Refunds';
$_['column_reason']                                     = 'Reason';
$_['column_fee']                                        = 'Processing Fee';

// Button
$_['button_cancel']                                     = 'Cancel';
$_['button_refund']                                     = 'Refund';
$_['button_charge']                                     = 'Charge';
$_['button_info']                                       = 'Info';
$_['button_ok']                                         = 'OK';
$_['button_close']                                      = 'Close';


// Label
$_['label_transaction_id']                              = 'Transaction ID';
$_['label_order_id']                                    = 'Order ID';
$_['label_type']                                        = 'Transaction Type';
$_['label_status']                                      = 'Status';
$_['label_currency']                                    = 'Currency';
$_['label_amount']                                      = 'Amount';
$_['label_browser']                                     = 'Customer User Agent';
$_['label_ip']                                          = 'Customer IP';
$_['label_date_created']                                = 'Date Created';

$_['label_confirm_charge']                              = 'You are about to charge the following amount: <strong>%s</strong>. Click OK to proceed.';
$_['label_confirm_refund']                              = 'Please provide a reason for the refund:';
$_['label_confirm_cancel']                              = 'You are about to cancel / revrese the following amount: <strong>%s</strong>. Click OK to proceed.';
$_['label_insert_amount']                               = 'Please insert the refund amount. Maximum: %s in %s:';



