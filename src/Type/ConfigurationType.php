<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\DashActivity\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Plain AbstractType: labels/help are translated via translation_domain, no need for
 * TranslatorAwareType's translator/locales injection.
 */
class ConfigurationType extends AbstractType
{
    private const DELAY_CHOICES = [15, 30, 45, 60, 90, 120];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $delayChoices = array_combine(self::DELAY_CHOICES, self::DELAY_CHOICES);

        $builder
            ->add('DASHACTIVITY_CART_ACTIVE', ChoiceType::class, [
                'label' => 'Active cart',
                'help' => 'How long (in minutes) a cart is to be considered as active after the last recorded change.',
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_VISITOR_ONLINE', ChoiceType::class, [
                'label' => 'Online visitor',
                'help' => 'How long (in minutes) a visitor is to be considered as online after their last action.',
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MIN', IntegerType::class, [
                'label' => 'Abandoned cart (min)',
                'help' => 'How long (in hours) after the last action a cart is to be considered as abandoned.',
                'constraints' => [new NotBlank(), new GreaterThan(0)],
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MAX', IntegerType::class, [
                'label' => 'Abandoned cart (max)',
                'help' => 'How long (in hours) after the last action a cart is no longer to be considered as abandoned.',
                'constraints' => [new NotBlank(), new GreaterThan(0)],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required' => true,
            'translation_domain' => 'Modules.Dashactivity.Admin',
        ]);
    }
}
