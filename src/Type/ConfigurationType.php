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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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

    // DASHACTIVITY_CART_ABANDONED_MIN/MAX are consumed as minutes (strtotime('- X MIN') in
    // dashactivity.php), not hours as the field used to suggest — 30 min to 3 days covers the
    // range a store would realistically want for a cart abandonment window.
    private const ABANDONED_CART_CHOICES = [30, 60, 120, 240, 360, 720, 1440, 2880, 4320];

    private const DOMAIN = 'Modules.Dashactivity.Admin';

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $delayChoices = [];
        foreach (self::DELAY_CHOICES as $minutes) {
            $delayChoices[self::formatMinutesChoiceLabel($minutes)] = $minutes;
        }
        $abandonedCartChoices = [];
        foreach (self::ABANDONED_CART_CHOICES as $minutes) {
            $abandonedCartChoices[self::formatMinutesChoiceLabel($minutes)] = $minutes;
        }

        $builder
            ->add('DASHACTIVITY_CART_ACTIVE', ChoiceType::class, [
                'label' => $this->translator->trans('Active cart', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) a cart is to be considered as active after the last recorded change (default: 30 min).', [], self::DOMAIN),
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_VISITOR_ONLINE', ChoiceType::class, [
                'label' => $this->translator->trans('Online visitor', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) a visitor is to be considered as online after their last action (default: 30 min).', [], self::DOMAIN),
                'choices' => $delayChoices,
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MIN', ChoiceType::class, [
                'label' => $this->translator->trans('Abandoned cart (min)', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) after the last action a cart is to be considered as abandoned (default: 1440 min, 24h).', [], self::DOMAIN),
                'choices' => $abandonedCartChoices,
            ])
            ->add('DASHACTIVITY_CART_ABANDONED_MAX', ChoiceType::class, [
                'label' => $this->translator->trans('Abandoned cart (max)', [], self::DOMAIN),
                'help' => $this->translator->trans('How long (in minutes) after the last action a cart is no longer to be considered as abandoned (default: 2880 min, 48h).', [], self::DOMAIN),
                'choices' => $abandonedCartChoices,
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

    // e.g. 1440 -> "24h", 30 -> "30 min".
    private static function formatMinutesChoiceLabel(int $minutes): string
    {
        if ($minutes >= 60 && 0 === $minutes % 60) {
            return sprintf('%dh', intdiv($minutes, 60));
        }

        return sprintf('%d min', $minutes);
    }
}
