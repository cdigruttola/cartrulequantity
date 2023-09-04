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

namespace cdigruttola\CartRuleQuantity\Controller;

use cdigruttola\CartRuleQuantity\Entity\CartRuleQuantity;
use cdigruttola\CartRuleQuantity\Filter\CartRuleQuantityFilters;
use cdigruttola\CartRuleQuantity\Translations\TranslationDomains;
use Exception;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Entity\Shop;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CartRuleQuantityController extends FrameworkBundleAdminController
{
    public function index(CartRuleQuantityFilters $filters): Response
    {
        $gridFactory = $this->get('cdigruttola.cartrulequantity.grid.cart_rule_quantity_grid_factory');
        $grid = $gridFactory->getGrid($filters);

        $configurationForm = $this->get('cdigruttola.cartrulequantity.cart_rule_quantity_configuration.form_handler')->getForm();

        return $this->render('@Modules/cartrulequantity/views/templates/admin/index.html.twig', [
            'translationDomain' => TranslationDomains::TRANSLATION_DOMAIN_ADMIN,
            'grid' => $this->presentGrid($grid),
            'configurationForm' => $configurationForm->createView(),
            'help_link' => false,
        ]);
    }

    public function create(Request $request): Response
    {
        $formDataHandler = $this->get('cdigruttola.cartrulequantity.form.identifiable_object.builder.cart_rule_quantity_form_builder');
        $form = $formDataHandler->getForm();
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.cartrulequantity.form.identifiable_object.handler.cart_rule_quantity_form_handler');

        try {
            $result = $formHandler->handle($form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash(
                    'success',
                    $this->trans('Successful creation.', 'Admin.Notifications.Success')
                );

                return $this->redirectToRoute('cartrulequantity_controller');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/cartrulequantity/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('Slider', TranslationDomains::TRANSLATION_DOMAIN_ADMIN),
            'help_link' => false,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $formBuilder = $this->get('cdigruttola.cartrulequantity.form.identifiable_object.builder.cart_rule_quantity_form_builder');
        $form = $formBuilder->getFormFor((int) $id);
        $form->handleRequest($request);

        $formHandler = $this->get('cdigruttola.cartrulequantity.form.identifiable_object.handler.cart_rule_quantity_form_handler');

        try {
            $result = $formHandler->handleFor($id, $form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash(
                    'success',
                    $this->trans('Successful edition.', 'Admin.Notifications.Success')
                );

                return $this->redirectToRoute('cartrulequantity_controller');
            }
        } catch (Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/cartrulequantity/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('Slider edit', TranslationDomains::TRANSLATION_DOMAIN_ADMIN),
            'help_link' => false,
        ]);
    }

    public function delete(Request $request, int $id): Response
    {
        $entity = $this->getDoctrine()
            ->getRepository(CartRuleQuantity::class)
            ->find($id);

        if (!empty($entity)) {
            $multistoreContext = $this->get('prestashop.adapter.shop.context');
            $entityManager = $this->get('doctrine.orm.entity_manager');

            if ($multistoreContext->isAllShopContext()) {
                $entity->clearShops();

                $entityManager->remove($entity);
            } else {
                $shopList = $this->getDoctrine()
                    ->getRepository(Shop::class)
                    ->findBy(['id' => $multistoreContext->getContextListShopID()]);

                foreach ($shopList as $shop) {
                    $entity->removeShop($shop);
                    $entityManager->flush();
                }

                if (count($entity->getShops()) === 0) {
                    $entityManager->remove($entity);
                }
            }

            $entityManager->flush();
            $this->addFlash(
                'success',
                $this->trans('Successful deletion.', 'Admin.Notifications.Success')
            );

            return $this->redirectToRoute('cartrulequantity_controller');
        }

        $this->addFlash(
            'error',
            $this->trans('Cannot find entity %d', TranslationDomains::TRANSLATION_DOMAIN_ADMIN, ['%d' => $id])
        );

        return $this->redirectToRoute('cartrulequantity_controller');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveConfiguration(Request $request): Response
    {
        $redirectResponse = $this->redirectToRoute('cartrulequantity_controller');

        $form = $this->get('cdigruttola.cartrulequantity.cart_rule_quantity_configuration.form_handler')->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $this->get('cdigruttola.cartrulequantity.cart_rule_quantity_configuration.form_handler')->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->flashErrors($formErrors);

        return $redirectResponse;
    }

    /**
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function toggleStatus(Request $request, int $id): Response
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        $entity = $entityManager
            ->getRepository(CartRuleQuantity::class)
            ->findOneBy(['id' => $id]);

        if (empty($entity)) {
            $response = [
                'status' => false,
                'message' => sprintf('Entity %d doesn\'t exist', $id),
            ];
            $errors = [$response];
            $this->flashErrors($errors);

            return $this->redirectToRoute('cartrulequantity_controller');
        }

        try {
            $entity->setActive(!$entity->getActive());
            $entityManager->flush();

            $this->addFlash('success', $this->trans('The status has been successfully updated.', 'Admin.Notifications.Success'));
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => sprintf(
                    'There was an error while updating the status of slide %d: %s',
                    $id,
                    $e->getMessage()
                ),
            ];
            $errors = [$response];
            $this->flashErrors($errors);
        }

        return $this->redirectToRoute('cartrulequantity_controller');
    }

    /**
     * Provides translated error messages for exceptions
     *
     * @return array
     */
    private function getErrorMessages(Exception $e): array
    {
        return [
            Exception::class => [
                $this->trans(
                    'Generic Exception',
                    TranslationDomains::TRANSLATION_DOMAIN_EXCEPTION
                ),
            ],
        ];
    }
}
