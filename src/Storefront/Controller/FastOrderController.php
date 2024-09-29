<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Storefront\Controller;

use NicoJust\ProductMulti\Service\FastOrderService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public function __construct(
        private FastOrderService $fastOrderService
    ) {}

    #[Route(path: '/fast-order', name: 'frontend.fast-order.index', methods: ['GET'])]
    public function fastOrderIndex(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->fastOrderService->fastOrderPageLoader->load($request, $context);
        $products = $this->fastOrderService->getProducts($context)->getElements();
        $formFieldNumber = $this->fastOrderService->systemConfigService->get('ProductMulti.config.formFieldNumber', $context->getSalesChannelId());
        $cart = $this->fastOrderService->getCart($context);

        return $this->renderStorefront('@ProductMulti/storefront/page/fast-order/index.html.twig', [
            'page' => $page,
            'products' => $products,
            'formFieldNumber' => $formFieldNumber >= 1 ? $formFieldNumber : 5, // provide default of 5
            'cartTotalPrice' => $this->fastOrderService->currencyFormatter->formatCurrencyByLanguage(
                $cart->getPrice()->getTotalPrice(),
                $context->getCurrency()->getName(),
                $context->getLanguageId(),
                $context->getContext()
            ),
        ]);
    }

    #[Route(path: '/fast-order-post', name: 'frontend.fast-order.post', methods: ['POST'])]
    public function fastOrderPost(Request $request, SalesChannelContext $context): Response
    {
        $rawfastOrderData = $request->request->all('fastOrder');
        $fastOrderData = array_filter(array_combine($rawfastOrderData['product'], $rawfastOrderData['qty']));

        $cart = $this->fastOrderService->getCart($context);
        foreach ($fastOrderData as $productId => $qty) {
            try {
                $this->fastOrderService->validateProduct($productId, $context);
            } catch (\Throwable $e) {
                $this->addFlash(StorefrontController::DANGER, sprintf('Product with ID "%s" not found!', $productId));

                return $this->redirectToRoute('frontend.fast-order.index');
            }

            $data = [
                'sessionId' => $cart->getToken(),
                'productId' => $productId,
                'qty' => (int)$qty
            ];
            $this->fastOrderService->fastOrderRepository->create([$data], $context->getContext());

            $lineItem = $this->fastOrderService->productLineItemFactory->create(
                ['id' => $productId, 'quantity' => (int)$qty],
                $context
            );
            $cart->add($lineItem);
        }
        $this->fastOrderService->cartService->recalculate($cart, $context);
        $this->addFlash(StorefrontController::SUCCESS, 'Added multiple Products to cart!');

        return $this->redirectToRoute('frontend.checkout.cart.page');
    }
}
