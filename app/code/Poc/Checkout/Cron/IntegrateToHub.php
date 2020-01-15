<?php

namespace Poc\Checkout\Cron;

class IntegrateToHub
{

    /** @var \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection */
    protected $_orderCollection;

    /** @var \Poc\Checkout\Helper\Integration\Order $_helperOrderIntegration */
    protected $_helperOrderIntegration;

    /** @var \Magento\Sales\Model\ResourceModel\Order $orderResource */
    protected $_orderResource;

    public function __construct
    (
        \Magento\Sales\Model\ResourceModel\Order\Collection $orderCollection,
        \Poc\Checkout\Helper\Integration\Order $helperOrderIntegration,
        \Magento\Sales\Model\ResourceModel\Order $orderResource
    )
    {
        $this->_orderCollection         = $orderCollection;
        $this->_helperOrderIntegration  = $helperOrderIntegration;
        $this->_orderResource           = $orderResource;
    }


    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Json_Exception
     */
    public function execute()
    {

        $this->_orderCollection->addFieldToFilter('is_integrated', ['neq' => 1]);

        /** @var \Magento\Sales\Model\Order $order */
        foreach ($this->_orderCollection as $order) {

            /** @var \Zend_Http_Response $result */
            $result = $this->_helperOrderIntegration->create($order);

            $isIntegrated = 0;
            if ($result->getStatus() == 200) {
                $isIntegrated = 1;
            }
            $order->setData('is_integrated', $isIntegrated);
            $order->setData('integrated_response', $result->getBody());
            $this->_orderResource->save($order);

        }


        return $this;

    }

}

