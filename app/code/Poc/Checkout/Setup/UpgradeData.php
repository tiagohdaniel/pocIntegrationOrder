<?php
namespace Poc\Checkout\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        // Upgrade 1.0.0
        $this->_upgrade_1_0_0($setup, $context);
    }

    public function _upgrade_1_0_0(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.0', '>=')) {
            return false;
        }

        $eavSetup = $this->eavSetupFactory->create([ 'setup' => $setup ]);

        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'is_integrated',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Integrated',
                'input' => 'boolean',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'group' => '',
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => '',
                'attribute_set' => 'default'
            ]);

        $eavSetup->addAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            'integrated_response',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Response data',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => true,
                'user_defined' => true,
                'group' => '',
                'default' => 0,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'unique' => false,
                'apply_to' => '',
                'attribute_set' => 'default'
            ]);
    }
}
