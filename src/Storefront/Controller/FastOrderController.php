<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Storefront\Controller;

use NicoJust\ProductMulti\Storefront\Page\FastOrder\FastOrderPageLoader;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\ProductLineItemFactory;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Exception\ProductNotFoundException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\Currency\CurrencyFormatter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class FastOrderController extends StorefrontController
{
    public function __construct(
        private FastOrderPageLoader $fastOrderPageLoader,
        private EntityRepository $productRepository,
        private SystemConfigService $systemConfigService,
        private CartService $cartService,
        private ProductLineItemFactory $productLineItemFactory,
        private CurrencyFormatter $currencyFormatter,
        private EntityRepository $fastOrderRepository,
    ) {}

    #[Route(path: '/fast-order', name: 'frontend.fast-order.index', methods: ['GET'])]
    public function fastOrderIndex(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->fastOrderPageLoader->load($request, $context);
        $products = $this->getProducts($context)->getElements();
        $formFieldNumber = $this->systemConfigService->get('ProductMulti.config.formFieldNumber', $context->getSalesChannelId());
        $cart = $this->getCart($context);
        $formFieldNumber = 0;

        return $this->renderStorefront('@ProductMulti/storefront/page/fast-order/index.html.twig', [
            'page' => $page,
            'products' => $products,
            'formFieldNumber' => $formFieldNumber >= 1 ? $formFieldNumber : 5, // provide default of 5
            'cartTotalPrice' => $this->currencyFormatter->formatCurrencyByLanguage($cart->getPrice()->getTotalPrice(), $context->getCurrency()->getName(), $context->getLanguageId(), $context->getContext()),
        ]);
    }

    #[Route(path: '/fast-order-post', name: 'frontend.fast-order.post', methods: ['POST'])]
    public function fastOrderPost(Request $request, SalesChannelContext $context): Response
    {
        $rawfastOrderData = $request->request->all('fastOrder');
        $fastOrderData = array_filter(array_combine($rawfastOrderData['product'], $rawfastOrderData['qty']));

        $cart = $this->getCart($context);
        foreach ($fastOrderData as $productId => $qty) {
            try {
                $this->validateProduct($productId, $context);
            } catch (\Throwable $e) {
                $this->addFlash(StorefrontController::DANGER, sprintf('Product with ID "%s" not found!', $productId));

                return $this->redirectToRoute('frontend.fast-order.index');
            }

            $data = [
                'sessionId' => $cart->getToken(),
                'productId' => $productId,
                'qty' => (int)$qty
            ];
            $this->fastOrderRepository->create([$data], $context->getContext());

            $lineItem = $this->productLineItemFactory->create(
                ['id' => $productId, 'quantity' => (int)$qty],
                $context
            );
            $cart->add($lineItem);
        }
        $this->cartService->recalculate($cart, $context);
        $this->addFlash(StorefrontController::SUCCESS, 'Added multiple Product to cart!');

        return $this->redirectToRoute('frontend.checkout.cart.page');
    }

    private function getCart(SalesChannelContext $context): Cart
    {
        try {
            $cart = $this->cartService->getCart($context->getToken(), $context);
        } catch (\Throwable $e) {
            $cart = $this->cartService->createNew($context->getToken());
        }

        return $cart;
    }

    private function getProducts(SalesChannelContext $context): EntityCollection
    {
        $criteria = (new Criteria())
            ->addFilter(
                new RangeFilter('stock', [RangeFilter::GTE => 1])
            );

        return $this->productRepository->search($criteria, $context->getContext())->getEntities();
    }

    private function validateProduct(string $productId, SalesChannelContext $context): void
    {
        $productsIds = $this->productRepository->searchIds(new Criteria([$productId]), $context->getContext());
        if ($productsIds->firstId() === null) {
            throw new ProductNotFoundException($productId);
        }
    }
}
