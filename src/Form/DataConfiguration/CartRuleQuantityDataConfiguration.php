<?php

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Form\DataConfiguration;

use cdigruttola\CartRuleQuantity\Configuration\CartRuleQuantityConfiguration;
use PrestaShop\PrestaShop\Core\Configuration\AbstractMultistoreConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CartRuleQuantityDataConfiguration extends AbstractMultistoreConfiguration
{
    private const CONFIGURATION_FIELDS = [
        'speed',
        'pause',
        'wrap',
        'max_product',
    ];

    /**
     * @return OptionsResolver
     */
    protected function buildResolver(): OptionsResolver
    {
        return (new OptionsResolver())
            ->setDefined(self::CONFIGURATION_FIELDS)
            ->setAllowedTypes('default_value', 'int');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): array
    {
        $return = [];
        $shopConstraint = $this->getShopConstraint();

        $return['default_value'] = $this->configuration->get(CartRuleQuantityConfiguration::CART_RULE_DEFAULT_QUANTITY, null, $shopConstraint);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateConfiguration(array $configuration): array
    {
        $shopConstraint = $this->getShopConstraint();
        $this->updateConfigurationValue(CartRuleQuantityConfiguration::CART_RULE_DEFAULT_QUANTITY, 'default_value', $configuration, $shopConstraint);

        return [];
    }
}
