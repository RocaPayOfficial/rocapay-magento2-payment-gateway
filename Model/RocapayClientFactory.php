<?php

namespace Rocapay\RocapayPaymentGateway\Model;

use Magento\Framework\ObjectManagerInterface;
use Rocapay\Rocapay;

class RocapayClientFactory
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create config model
     * @param string $apiAuthToken
     * @return Rocapay
     */
    public function create($apiAuthToken)
    {
        return $this->_objectManager->create(Rocapay::class, ['apiAuthToken' => $apiAuthToken]);
    }
}