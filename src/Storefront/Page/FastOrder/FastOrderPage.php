<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Storefront\Page\FastOrder;

use Shopware\Storefront\Page\Page;
use NicoJust\ProductMulti\Core\Content\FastOrder\FastOrderEntity;

class FastOrderPage extends Page
{
    protected FastOrderEntity $fastOrderData;

    public function getFastOrderData(): FastOrderEntity
    {
        return $this->fastOrderData;
    }

    public function setFastOrderData(FastOrderEntity $fastOrderData): void
    {
        $this->fastOrderData = $fastOrderData;
    }
}
