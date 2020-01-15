<?php

namespace Poc\Checkout\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class IntegrateOrderToHub implements ObserverInterface
{

    /** @var \Poc\Checkout\Helper\Integration\Order $orderIntegration */
    protected $_orderIntegration;

    /** @var \Magento\Sales\Model\ResourceModel\Order $orderResource */
    protected $_orderResource;

    public function __construct(
        \Poc\Checkout\Helper\Integration\Order $orderIntegration,
        \Magento\Sales\Model\ResourceModel\Order $orderResource
    ) {
        $this->_orderIntegration      = $orderIntegration;
        $this->_orderResource      = $orderResource;
    }


    /**
     * @param EventObserver $observer
     * @return bool|void
     */
    public function execute(EventObserver $observer)
    {
        $orders = $observer->getEvent()->getData('orders');

        try {
            /** @var \Magento\Sales\Model\Order $order */
            foreach ($orders as $order) {
                /** @var \Zend_Http_Response $result */
                $result = $this->_orderIntegration->create($order);

                $isIntegrated = 0;
                if ($result->getStatus() == 200) {
                    $isIntegrated = 1;
                }
                $order->setData('is_integrated', $isIntegrated);
                $order->setData('integrated_response', $result->getBody());

                $this->_orderResource->save($order);
            }
        } catch (\Exception $exception) {
            return;
        }
    }
}
