<?php

declare(strict_types=1);

namespace NicoJust\ProductMulti\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1727555479FastOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1727555479;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `fast_order` (
    `id` BINARY(16) NOT NULL,
    `session_id` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `product_id` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `qty` TINYINT(1) COLLATE utf8mb4_unicode_ci,
    `comment` VARCHAR(255) COLLATE utf8mb4_unicode_ci,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3),
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($sql);
    }
}
