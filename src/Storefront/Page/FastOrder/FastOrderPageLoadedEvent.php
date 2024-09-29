<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Storefront\Page\FastOrder;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

class FastOrderPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(protected FastOrderPage $page, SalesChannelContext $salesChannelContext, Request $request)
    {
        $this->page = $page;
        parent::__construct($salesChannelContext, $request);
    }

    public function getPage(): FastOrderPage
    {
        return $this->page;
    }
}
