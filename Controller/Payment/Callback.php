<?php

namespace Rocapay\RocapayPaymentGateway\Controller\Payment;

use Rocapay\RocapayPaymentGateway\Model\Payment as RocapayPayment;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;

/**
 * Rocapay Callback controller
 *
 * @package     Rocapay_RocapayPaymentGateway
 * @author      Rocapay
 */
class Callback extends Action
{

    protected $rocapayPayment;

    protected $order;

    /**
     * @param Context $context
     * @param Order $order
     * @param Payment|RocapayPayment $rocapayPayment
     */
    public function __construct(
        Context $context,
        Order $order,
        RocapayPayment $rocapayPayment
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->rocapayPayment = $rocapayPayment;

        $this->execute();
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $requestContent = json_decode($this->getRequest()->getContent(), true);
        $magentoOrderId = (int)filter_var($requestContent['description'], FILTER_SANITIZE_NUMBER_INT);

        $order = $this->order->loadByIncrementId($magentoOrderId);
        $this->rocapayPayment->handleRocapayCallback($order, $requestContent, $magentoOrderId);

        $this->getResponse()->setBody('OK');
    }
}
