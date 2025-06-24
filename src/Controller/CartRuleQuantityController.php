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

use cdigruttola\CartRuleQuantity\Filter\CartRuleQuantityFilters;
use cdigruttola\CartRuleQuantity\Repository\CartRuleQuantityRepository;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Core\Context\ShopContext;
use PrestaShop\PrestaShop\Core\Form\Handler;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Builder\FormBuilderInterface;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\Handler\FormHandler;
use PrestaShop\PrestaShop\Core\Grid\GridFactory;
use PrestaShopBundle\Controller\Admin\PrestaShopAdminController;
use PrestaShopBundle\Entity\Repository\ShopRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

if (!defined('_PS_VERSION_')) {
    exit;
}

class CartRuleQuantityController extends PrestaShopAdminController
{
    public function __construct(private readonly ShopRepository $shopRepository)
    {
    }

    public function index(
        CartRuleQuantityFilters $filters,
        #[Autowire(service: 'cdigruttola.cartrulequantity.cart_rule_quantity_configuration.form_handler')]
        Handler $configurationFormHandler,
        #[Autowire(service: 'cdigruttola.cartrulequantity.grid.cart_rule_quantity_grid_factory')]
        GridFactory $gridFactory,
    ): Response {
        $grid = $gridFactory->getGrid($filters);

        $configurationForm = $configurationFormHandler->getForm();

        return $this->render('@Modules/cartrulequantity/views/templates/admin/index.html.twig', [
            'translationDomain' => 'Modules.Cartrulequantity.Admin',
            'grid' => $this->presentGrid($grid),
            'configurationForm' => $configurationForm->createView(),
            'help_link' => false,
        ]);
    }

    public function create(
        Request $request,
        #[Autowire(service: 'cdigruttola.cartrulequantity.form.identifiable_object.builder.cart_rule_quantity_form_builder')]
        FormBuilderInterface $formDataHandler,
        #[Autowire(service: 'cdigruttola.cartrulequantity.form.identifiable_object.handler.cart_rule_quantity_form_handler')]
        FormHandler $formHandler,
    ): Response {
        $form = $formDataHandler->getForm();
        $form->handleRequest($request);

        try {
            $result = $formHandler->handle($form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash(
                    'success',
                    $this->trans('Successful creation.', [], 'Admin.Notifications.Success')
                );

                return $this->redirectToRoute('cartrulequantity_controller');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/cartrulequantity/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('Rules', [], 'Modules.Cartrulequantity.Admin'),
            'help_link' => false,
        ]);
    }

    public function edit(
        Request $request,
        int $id,
        #[Autowire(service: 'cdigruttola.cartrulequantity.form.identifiable_object.builder.cart_rule_quantity_form_builder')]
        FormBuilderInterface $formDataHandler,
        #[Autowire(service: 'cdigruttola.cartrulequantity.form.identifiable_object.handler.cart_rule_quantity_form_handler')]
        FormHandler $formHandler,
    ): Response {
        $form = $formDataHandler->getFormFor($id);
        $form->handleRequest($request);

        try {
            $result = $formHandler->handleFor($id, $form);

            if (null !== $result->getIdentifiableObjectId()) {
                $this->addFlash(
                    'success',
                    $this->trans('Successful edition.', [], 'Admin.Notifications.Success')
                );

                return $this->redirectToRoute('cartrulequantity_controller');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', $this->getErrorMessageForException($e, $this->getErrorMessages($e)));
        }

        return $this->render('@Modules/cartrulequantity/views/templates/admin/form.html.twig', [
            'form' => $form->createView(),
            'title' => $this->trans('Slider edit', [], 'Modules.Cartrulequantity.Admin'),
            'help_link' => false,
        ]);
    }

    public function delete(Request $request, int $id): Response
    {
        $repository = $this->container->get(CartRuleQuantityRepository::class);
        $entity = $repository->findOneBy(['id' => $id]);

        if (!empty($entity)) {
            $multistoreContext = $this->container->get(ShopContext::class);
            $entityManager = $this->container->get(EntityManagerInterface::class);

            if ($multistoreContext->isAllShopContext()) {
                $entity->clearShops();

                $entityManager->remove($entity);
            } else {
                $shopList = $this->shopRepository->findBy(['id' => \Shop::getContextListShopID()]);

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
                $this->trans('Successful deletion.', [], 'Admin.Notifications.Success')
            );

            return $this->redirectToRoute('cartrulequantity_controller');
        }

        $this->addFlash(
            'error',
            $this->trans('Cannot find entity %d', ['%d' => $id], 'Modules.Cartrulequantity.Admin')
        );

        return $this->redirectToRoute('cartrulequantity_controller');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveConfiguration(
        Request $request,
        #[Autowire(service: 'cdigruttola.cartrulequantity.cart_rule_quantity_configuration.form_handler')]
        Handler $configurationFormHandler,
    ): Response {
        $redirectResponse = $this->redirectToRoute('cartrulequantity_controller');

        $form = $configurationFormHandler->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $redirectResponse;
        }

        if ($form->isValid()) {
            $data = $form->getData();
            $saveErrors = $configurationFormHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', [], 'Admin.Notifications.Success'));

                return $redirectResponse;
            }
        }

        $formErrors = [];

        foreach ($form->getErrors(true) as $error) {
            $formErrors[] = $error->getMessage();
        }

        $this->addFlashErrors($formErrors);

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
        $repository = $this->container->get(CartRuleQuantityRepository::class);
        $entity = $repository->findOneBy(['id' => $id]);

        if (empty($entity)) {
            $errors = [$this->trans('Entity %d doesn\'t exist', [$id], 'Modules.Cartrulequantity.Admin')];
            $this->addFlashErrors($errors);

            return $this->redirectToRoute('cartrulequantity_controller');
        }

        try {
            $entity->setActive(!$entity->getActive());
            $em = $this->container->get(EntityManagerInterface::class);
            $em->flush();

            $this->addFlash('success', $this->trans('The status has been successfully updated.', [], 'Admin.Notifications.Success'));
        } catch (\Exception $e) {
            $errors = [$this->trans('There was an error while updating the status of %d: %s', [$id, $e->getMessage()], 'Modules.Cartrulequantity.Admin')];
            $this->addFlashErrors($errors);
        }

        return $this->redirectToRoute('cartrulequantity_controller');
    }

    /**
     * Provides translated error messages for exceptions
     *
     * @return array
     */
    private function getErrorMessages(\Exception $e): array
    {
        return [
            \Exception::class => [
                $this->trans(
                    'Generic Exception',
                    [],
                    'Modules.Cartrulequantity.Exceptions'
                ),
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
                CartRuleQuantityRepository::class => CartRuleQuantityRepository::class,
                EntityManagerInterface::class => EntityManagerInterface::class,
                ShopContext::class => ShopContext::class,
            ];
    }
}
