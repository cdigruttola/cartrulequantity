<?php
/**
 * Copyright since 2007 Carmine Di Gruttola
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    cdigruttola <c.digruttola@hotmail.it>
 * @copyright Copyright since 2007 Carmine Di Gruttola
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

declare(strict_types=1);

namespace cdigruttola\CartRuleQuantity\Form;

use cdigruttola\CartRuleQuantity\Translations\TranslationDomains;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShop\PrestaShop\Core\ConstraintValidator\Constraints\PositiveOrZero;
use PrestaShopBundle\Form\Admin\Type\CategoryChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\ShopChoiceTreeType;
use PrestaShopBundle\Form\Admin\Type\SwitchType;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class CartRuleQuantityType extends TranslatorAwareType
{
    /**
     * @var bool
     */
    private $isMultistoreUsed;
    /**
     * @var LegacyContext
     */
    private $context;

    /**
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param bool $isMultistoreUsed
     * @param LegacyContext $context
     */
    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        bool $isMultistoreUsed,
        LegacyContext $context
    ) {
        parent::__construct($translator, $locales);

        $this->isMultistoreUsed = $isMultistoreUsed;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => $this->trans('Name', TranslationDomains::ADMIN_GLOBAL),
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('quantity', TextType::class, [
                'label' => $this->trans('Quantity', TranslationDomains::ADMIN_GLOBAL),
                'required' => true,
                'constraints' => [
                    new PositiveOrZero(),
                ],
            ])
            ->add('active', SwitchType::class, [
                'label' => $this->trans('Active', TranslationDomains::TRANSLATION_DOMAIN_ADMIN),
                'required' => true,
            ])
            ->add('categories_id', CategoryChoiceTreeType::class, [
                'label' => $this->trans('Categories', 'Admin.Global'),
                'required' => false,
                'multiple' => true,
            ]);

        if ($this->isMultistoreUsed) {
            $builder->add(
                'shop_association',
                ShopChoiceTreeType::class,
                [
                    'label' => $this->trans('Shop associations', TranslationDomains::TRANSLATION_DOMAIN_ADMIN),
                    'constraints' => [
                        new NotBlank([
                            'message' => $this->trans(
                                'You have to select at least one shop to associate this item with',
                                'Admin.Notifications.Error'
                            ),
                        ]),
                    ],
                ]
            );
        }
    }
}
