<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Service;

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

class FastOrderService
{
    public function __construct(
        public FastOrderPageLoader $fastOrderPageLoader,
        public EntityRepository $productRepository,
        public EntityRepository $fastOrderRepository,
        public SystemConfigService $systemConfigService,
        public CartService $cartService,
        public ProductLineItemFactory $productLineItemFactory,
        public CurrencyFormatter $currencyFormatter,
    ) {}

    public function getCart(SalesChannelContext $context): Cart
    {
        try {
            $cart = $this->cartService->getCart($context->getToken(), $context);
        } catch (\Throwable $e) {
            $cart = $this->cartService->createNew($context->getToken());
        }

        return $cart;
    }

    public function getProducts(SalesChannelContext $context): EntityCollection
    {
        $criteria = (new Criteria())
            ->addFilter(
                new RangeFilter('stock', [RangeFilter::GTE => 1])
            );

        return $this->productRepository->search($criteria, $context->getContext())->getEntities();
    }

    public function validateProduct(string $productId, SalesChannelContext $context): void
    {
        $productsIds = $this->productRepository->searchIds(new Criteria([$productId]), $context->getContext());
        if ($productsIds->firstId() === null) {
            throw new ProductNotFoundException($productId);
        }
    }
}
