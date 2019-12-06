<?php

namespace Rocapay\RocapayPaymentGateway\Controller\Payment;

use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Rocapay\RocapayPaymentGateway\Model\Payment as RocapayPayment;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Rocapay PlaceOrder controller
 *
 * @package     Rocapay_RocapayPaymentGateway
 * @author      Rocapay
 */
class PlaceOrder extends Action
{

    protected $orderFactory;

    protected $rocapayPayment;

    protected $checkoutSession;

    protected $scopeConfig;

    protected $_eventManager;

    protected $quoteRepository;

    /**
     * @param ManagerInterface $eventManager
     * @param CartRepositoryInterface $quoteRepository
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param Session $checkoutSession
     * @param RocapayPayment $rocapayPayment
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ManagerInterface $eventManager,
        CartRepositoryInterface $quoteRepository,
        Context $context,
        OrderFactory $orderFactory,
        Session $checkoutSession,
        RocapayPayment $rocapayPayment,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->_eventManager = $eventManager;
        $this->orderFactory = $orderFactory;
        $this->rocapayPayment = $rocapayPayment;
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    public function execute()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderFactory->create()->load($orderId);

        if (!$order->getIncrementId()) {
            $this->getResponse()->setBody(json_encode([
                'status' => false,
                'reason' => 'Order Not Found',
            ]));

            return;
        }

        // Restores the cart
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $quote->setIsActive(true);
        $this->quoteRepository->save($quote);

        $this->getResponse()->setBody(json_encode($this->rocapayPayment->getRocapayRequest($order)));
    }

}
