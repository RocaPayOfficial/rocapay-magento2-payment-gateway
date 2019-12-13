# Magento 2 RocaPay Payment Gateway
This payment gateway integration enables you to use [RocaPay](https://rocapay.com/) with Magento 2.
# Installation
## Composer
To get the latest version of the gateway through Composer, run the command:
```bash
composer require rocapayofficial/rocapay-magento2-payment-gateway
```
## Direct Download
You can also download the gateway as a [TAR File](https://github.com/rocapayofficial/rocapay-magento2-payment-gateway/archive/master.tar.gz). Then, run the following commands in your installation's root directory:
```bash
cd app/code
mkdir -p Rocapay/RocapayPaymentGateway
tar -zxvf rocapay-magento2-payment-gateway-master.tar.gz
```
# Configuration
First, you have to sign up for a [RocaPay](https://rocapay.com/auth/register) account.

Then, you have to create a widget and put in `YOUR_DOMAIN/rocapay/payment/callback` in the Postback URL field.

 After that, enable the gateway by running this in your installation's root directory:
```bash
php bin/magento module:enable Rocapay_RocapayPaymentGateway --clear-static-content
php bin/magento setup:upgrade
```
Finally, copy the API key, provided under the implementation tab of your newly created widget, to the payment's gateway configuration menu in the admin panel (`Stores / Configuration / Sales / Payment Methods / RocaPay`). 
