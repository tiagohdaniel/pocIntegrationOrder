<?php

namespace Poc\Checkout\Console\Integration;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Order extends Command
{

    /**
     *
     */
    protected function configure()
    {
        $this->setName('order:integration:hub');
        $this->setDescription('Integration order to HUB');
        parent::configure();
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Json_Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Poc\Customer\Cron\IntegrateToHub $IntegrateToHub */
        $IntegrateToHub = $objectManager->create('Poc\Checkout\Cron\IntegrateToHub');
        $IntegrateToHub->execute();
        $output->writeln("order integrated success");
    }


}
