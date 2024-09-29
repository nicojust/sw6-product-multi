<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Storefront\Page\FastOrder;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class FastOrderPageLoader
{
    private GenericPageLoaderInterface $genericPageLoader;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(GenericPageLoaderInterface $genericPageLoader, EventDispatcherInterface $eventDispatcher)
    {
        $this->genericPageLoader = $genericPageLoader;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function load(Request $request, SalesChannelContext $context): FastOrderPage
    {
        $page = $this->genericPageLoader->load($request, $context);
        $page = FastOrderPage::createFrom($page);

        $page->setFastOrderData(...);

        $this->eventDispatcher->dispatch(
            new FastOrderPageLoadedEvent($page, $context, $request)
        );

        return $page;
    }
}
