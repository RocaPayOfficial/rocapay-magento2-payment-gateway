/**
 * Rocapay payment method model
 *
 * @package     Rocapay_RocapayPaymentGateway
 * @author      Rocapay
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'rocapay_gateway',
                component: 'Rocapay_RocapayPaymentGateway/js/view/payment/method-renderer/rocapay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
