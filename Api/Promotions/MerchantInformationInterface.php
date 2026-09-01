<?php
/**
 * Copyright © 2024 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Promotions;

/**
 * Represents merchant information schema within the Promotions response
 *
 * Data Schema:
 * "id": "4000000b-a00a-400a-a004-400000000006",
 * "brand": "VISA",
 * "cardBrand": "VISA",
 * "acquirer": "Prisma",
 * "channel": "Internet",
 * "number": "88884444",
 * "fantasyName": "Visa_Prisma_Web",
 * "status": "PENDING",
 * "activationKey": "8DUET1U5EC...842MR5P"
 */
interface MerchantInformationInterface
{
    // silence is golden
}
