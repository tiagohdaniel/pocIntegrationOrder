<?php
namespace Poc\Checkout\Helper\Integration;

use \Magento\Framework\App\Helper\AbstractHelper;

class Order extends AbstractHelper
{

    /** @var \Psr\Log\LoggerInterface $_logger */
    protected $_logger;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_logger = $logger;
    }

    /**
     * @return bool
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Json_Exception
     */
    private function _getToken()
    {
        return $this->getConfig('sales_order/integration/key');
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return \Zend_Http_Response | bool
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Json_Exception
     */
    public function create($order)
    {
        $data = $this->prepare($order);

        $client = new \Zend_Http_Client();
        $client->setHeaders(
            [
                'Accept: application/json',
                'Authorization:Bearer '. $this->_getToken()
            ]
        );
        $client->setUri($this->getConfig('sales_order/integration/endpoint'));
        $client->setMethod('POST');
        $client->setHeaders('Content-Type', 'application/json');
        $jsonData = \Zend_Json::encode($data);
        $this->_logger->info('request: ' . $jsonData);
        $client->setRawData($jsonData, 'application/json');

        try {
            $result = $client->request();
            $this->_logger->info('response: ' . $result->getBody());
            return $result;
        } catch (\Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return false;
        }

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    private function prepare($order)
    {
        if (!$order->hasData()) {
            return [];
        }

        /** @var \Magento\Sales\Model\Order\Address $billingAddress */
        $shippingAddress = $order->getShippingAddress();

        $postData['customer'] = [
            'cnpj'         => $order->getCustomerTaxvat(),
            'razao_social' => $order->getCustomerFirstname(),
            'telephone'    => $shippingAddress->getTelephone(),
            'dob'          => $order->getCustomerDob()
        ];

        $postData['shippingAddress'] = [
            'street'        => $shippingAddress->getStreetLine(1),
            'number'        => $shippingAddress->getStreetLine(2),
            'additional'    => $shippingAddress->getStreetLine(4),
            'neighborhood'  => $shippingAddress->getStreetLine(3),
            'city'          => $shippingAddress->getCity(),
            'uf'            => $shippingAddress->getRegionCode(),
            'country'       => $shippingAddress->getCountryId(),
            'postcode'      => (string) preg_replace("/[^0-9]/", "", $shippingAddress->getPostcode()),
            'receiver_name' => $shippingAddress->getData('lastname')
        ];

        $itemsPostData = [];
        foreach ($order->getAllItems() as $item) {
            /** @var \Magento\Customer\Model\Address $addressEntity */
            $itemsPostData[] = [
                'sku'         => $item->getSku(),
                'name'        => $item->getName(),
                'price'       => $item->getPrice(),
                'qty_ordered' => $item->getQtyOrdered()
            ];
        }

        $postData['items'] = $itemsPostData;

        /** @var \Magento\Sales\Model\Order\Address $billingAddress */
        $paymentInformation = $order->getPayment()->getAdditionalInformation();

        $postData = [
            'shipping_method'      => $order->getShippingMethod(),
            'payment_method'       => $paymentInformation['method'],
            'payment_installments' => $paymentInformation['cc_installments'] ? $paymentInformation['cc_installments'] : '',
            'subtotal'             => $order->getSubtotal(),
            'shipping_amount'      => $order->getShippingAmount(),
            'discount_amount'      => $order->getDiscountAmount(),
            'grand_total'          => $order->getGrandTotal()
        ];

        return $postData;
    }


    /**
     * @param $configPath
     * @return mixed
     */
    public function getConfig($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
