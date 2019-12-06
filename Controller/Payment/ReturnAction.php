<?php

namespace Rocapay\RocapayPaymentGateway\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

class ReturnAction extends Action
{

    protected $request;

    public function __construct(Context $context, RequestInterface $request)
    {
        parent::__construct($context);
        $this->request = $request;
    }

    /**
     * @return Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Redirect to successful/cancelled 3checkout
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute()
    {
        $order = $this->_getCheckout()->getLastRealOrder();

        if (!$order) {
            return;
        }

        // Payment failed TODO: Implement Rocapay backend solution for this
        /*if (isset($requestContent['status']) && $requestContent['status'] !== 'success') {
            $this->handleCancellation($order);
            $this->_redirect('checkout/cart');

            return;
        }*/

        // Payment succeeded
        $this->_redirect('checkout/onepage/success');
    }

    /**
     * Handle a cancelled order
     *
     * @param Order $order
     * @return void
     * @throws LocalizedException
     */
    private function handleCancellation(Order $order)
    {
        if ($order->getId() && !$order->isCanceled()) {
            $order->registerCancellation('Canceled by Customer')->save();
        }

        $this->_getCheckout()->restoreQuote();
    }
}
