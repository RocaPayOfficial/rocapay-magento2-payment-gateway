<?php

namespace Rocapay\RocapayPaymentGateway\Model;

use Exception;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Rocapay payment method model
 *
 * @package     Rocapay_RocapayPaymentGateway
 * @author      Rocapay
 */
class Payment extends AbstractMethod
{

    const ROCAPAY_MAGENTO_VERSION = '0.0.1';

    const CODE = 'rocapay_gateway';

    protected $_code = 'rocapay_gateway';

    protected $_isInitializeNeeded = true;

    protected $urlBuilder;

    protected $storeManager;

    protected $orderManagement;

    protected $_rocapaySdkFactory;

    protected $request;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param OrderManagementInterface $orderManagement
     * @param RocapayClientFactory $rocapaySdkFactory
     * @param RequestInterface $request
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @internal param ModuleListInterface $moduleList
     * @internal param TimezoneInterface $localeDate
     * @internal param CountryFactory $countryFactory
     * @internal param Http $response
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        OrderManagementInterface $orderManagement,
        RocapayClientFactory $rocapaySdkFactory,
        RequestInterface $request,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->orderManagement = $orderManagement;
        $this->_rocapaySdkFactory = $rocapaySdkFactory;
        $this->request = $request;
    }

    /**
     * @param Order $order
     * @return array
     * @throws LocalizedException
     */
    public function getRocapayRequest(Order $order)
    {
        $amount = $order->getGrandTotal();
        $fiatCurrency = $order->getOrderCurrencyCode();
        $callbackUrl = $this->urlBuilder->getUrl('rocapay/payment/returnAction');
        $description = 'Order: ' . $order->getIncrementId();

        $rocapayAuthToken = $this->getConfigData('api_auth_token');
        $rocapaySdk = $this->_rocapaySdkFactory->create($rocapayAuthToken);
        $rocapayPayment = $rocapaySdk->createPayment($amount, $fiatCurrency, $callbackUrl, $description);

        if (isset($rocapayPayment['status']) && $rocapayPayment['status'] === 'success') {
            $payment = $order->getPayment();
            $payment->setAdditionalInformation('rocapay_payment_id', $rocapayPayment['paymentId']);
            $payment->save();

            return [
                'status' => true,
                'paymentUrl' => $rocapayPayment['paymentUrl']
            ];
        }

        return [
            'status' => false
        ];
    }

    /**
     * Validate the callback request from RocaPay
     *
     * @param Order $order
     * @param array $requestContent
     * @param int $magentoOrderId
     */
    public function handleRocapayCallback(Order $order, array $requestContent, int $magentoOrderId)
    {
        try {
            if (!$order || !$order->getIncrementId()) {
                throw new Exception('Order ' . $magentoOrderId . ' does not exists');
            }

            $payment = $order->getPayment();
            $paymentIdFromRocapay = $requestContent['transaction_id'];
            $paymentIdFromMagento = $payment->getAdditionalInformation('rocapay_payment_id');

            if (empty($paymentIdFromMagento) || $paymentIdFromRocapay !== $paymentIdFromMagento) {
                throw new Exception("Tokens don't match.");
            }

            if ($requestContent['status'] !== 'success') {
                $this->orderManagement->cancel($magentoOrderId);
                return;
            }

            $order->setState(Order::STATE_PROCESSING);
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PROCESSING));
            $order->save();
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}
