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

namespace PrestaShop\Module\DashActivity\Controller;

use Configuration;
use PrestaShop\Module\DashActivity\Type\ConfigurationType;
// FrameworkBundleAdminController is deprecated since 9.0 in favor of PrestaShopAdminController,
// which doesn't exist on PS 8.2 — kept for the module's declared 8.2+ compatibility.
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Security\Annotation\AdminSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfigurationController extends FrameworkBundleAdminController
{
    private const FIELDS = [
        'DASHACTIVITY_CART_ACTIVE',
        'DASHACTIVITY_VISITOR_ONLINE',
        'DASHACTIVITY_CART_ABANDONED_MIN',
        'DASHACTIVITY_CART_ABANDONED_MAX',
    ];

    // Every install() before 3.0.0 set these two exact values, meant as hours (the free-text
    // field's own former label) though always read back as minutes — never anything else, since
    // no UI let a merchant change them before this settings page existed.
    private const LEGACY_ABANDONED_CART_MIN_DEFAULT = 24;
    private const LEGACY_ABANDONED_CART_MAX_DEFAULT = 48;

    /**
     * @AdminSecurity("is_granted('read', request.get('_legacy_controller'))")
     */
    public function indexAction(Request $request): Response
    {
        $data = [];
        foreach (self::FIELDS as $field) {
            $data[$field] = (int) Configuration::get($field);
        }

        // Correct the known pre-3.0.0 default to what it always meant (1440 min = 24h, 2880 min
        // = 48h) for display only: nothing is written back unless the merchant saves the form.
        if (self::LEGACY_ABANDONED_CART_MIN_DEFAULT === $data['DASHACTIVITY_CART_ABANDONED_MIN']) {
            $data['DASHACTIVITY_CART_ABANDONED_MIN'] = 1440;
        }
        if (self::LEGACY_ABANDONED_CART_MAX_DEFAULT === $data['DASHACTIVITY_CART_ABANDONED_MAX']) {
            $data['DASHACTIVITY_CART_ABANDONED_MAX'] = 2880;
        }

        $form = $this->createForm(ConfigurationType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('update', $request->attributes->get('_legacy_controller'));

            // No @DemoRestricted attribute here: its listener only recognizes the PHP 8
            // attribute form (PrestaShopBundle\Security\Attribute\DemoRestricted), which
            // doesn't exist on PS 8.2 — this module's declared minimum version.
            if ($this->isDemoModeEnabled()) {
                $this->addFlash('error', $this->trans('This functionality has been disabled.', 'Admin.Notifications.Error'));

                return $this->redirectToRoute('dashactivity_configuration');
            }

            foreach ($form->getData() as $field => $value) {
                Configuration::updateValue($field, (int) $value);
            }
            $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

            return $this->redirectToRoute('dashactivity_configuration');
        }

        return $this->render('@Modules/dashactivity/views/templates/admin/configuration.html.twig', [
            'configurationForm' => $form->createView(),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink('AdminDashactivityConfiguration'),
        ]);
    }
}
