# OpenCart 3.x payment module for Transact Pro

This module is using to connect OpenCart to Transact Pro Gateway payment system.

### Requirements

- This module uses [Transact Pro Gateway v3 PHP client library](https://github.com/TransactPRO/gw3-php-client) (included in distribution)
- PHP 7.* (required by Transact Pro Gateway v3 PHP client library)
- [OpenCart 3.x](https://www.opencart.com/index.php?route=cms/download/history)

## Installation

- Download this repository as ZIP archive and save it as `gw3-opencart-plugin.ocmod.zip` (make sure the archive's extension is `.ocmod.zip`)
- Install module using standard OpenCart module installer (`Extensions > Installer > Upload File`)

### Configuration and usage

Enable and configure module in OpenCart admin (`Extensions > Extensions > Paymens > Transact Pro`)

#### Settings
- `Extension status`: Enabled
- `Payment method name`: Credit / Debit Card
- `Gateway URL`: Leave empty (`https://api.transactpro.lv/v3.0` is used by default) or specify the URL received from the Transact Pro support team 
- `Account ID`: Specify your numeric Account ID received from the Transact Pro support team 
- `Secret Key`: Specify your Secret Key received from the Transact Pro support team 
- `Payment Method`: Choose payment method you want to use (`SMS` is most common used)
- `Payment Infomation Capture`: Choose `Merchant Side` (credit card details will be entered on `Checkout` page) or `Payment gateway side` (client will be redirected to payment gateway page to enter credit card details). 
**Note:** `Payment Infomation Capture` depends from `Account ID`, you need to set corresponded `Account ID` value for correct work
- `Transaction Statuses`: Set relation between transaction statuses (left) and order statuses (right)

#### Transactions
- Transactions list shown here, you can `Cancel`, `Charge` or `Refund` particular transaction (depends from the transaction status)

#### CRON
- Use `Method # 1` or `Method # 2` for setting up the CRON job (used in recurring transactions)
- `Setup confirmation`: Need to be checked
- `Send e-mail summary`: Send email to the administrator every time CRON job run
- `Send task summary to this e-mail`: Speify email address to receive recurrent task summary emails

#### Recurring Payments
- `Status of recurring payments`: Enable if you plan to use recurring payments
- `Recurring Transaction Successful`: Send email to the customer in case successful recurrent transaction
- `Recurring Transaction Failed`: Send email to the customer in case failed recurrent transaction

**Note: At the current time gateway accepts only Euro. Please, configure your site to deal with Euro currency by default!**


### Submit bugs and feature requests
 
Bugs and feature request are tracked on [GitHub](https://github.com/TransactPRO/gw3-opencart-plugin/issues)

### License

This library is licensed under the MIT License - see the `LICENSE` file for details.