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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Plain AbstractType (no need for TranslatorAwareType's locales), with TranslatorInterface
 * constructor-injected directly so label/help strings go through an explicit trans() call —
 * required for the translation extractor to pick them up, since it only extracts ChoiceType
 * "choices", not "label"/"help" option strings.
 */
class ConfigurationType extends AbstractType
{
    private const DELAY_CHOICES = [15, 30, 45, 60, 90, 120];

    private const DOMAIN = 'Modules.Dashactivity.Admin';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $delayChoices = array_combine(self::DELAY_CHOICES, self::DELAY_CHOICES);

        $builder
            ->add('DASHACTIVITY_CART_ACTIVE', ChoiceType::class, [
                'label' => $this->translator->trans('Active cart', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) a cart is to be considered as active after the last recorded change.', [], self::DOMAIN),
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_VISITOR_ONLINE', ChoiceType::class, [
                'label' => $this->translator->trans('Online visitor', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) a visitor is to be considered as online after their last action.', [], self::DOMAIN),
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MIN', IntegerType::class, [
                'label' => $this->translator->trans('Abandoned cart (min)', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in hours) after the last action a cart is to be considered as abandoned.', [], self::DOMAIN),
                'constraints' => [new NotBlank(), new GreaterThan(0)],
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MAX', IntegerType::class, [
                'label' => $this->translator->trans('Abandoned cart (max)', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in hours) after the last action a cart is no longer to be considered as abandoned.', [], self::DOMAIN),
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
