<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Checkout;

/**
 * Configuration provider for Payment during Checkout
 */
interface AttributeProviderInterface
{
    /**#@+
     * @access public
     * @var string
     */
    public const IS_ACTIVE = 'is_active';
    /**
     * This key will return env related info
     * url, public_key and is_sandbox
     */
    public const ENV = 'env';
    public const AVAILABLE_TYPES = 'available_types';
    public const MONTHS = 'months';
    public const YEARS = 'years';
    public const HAS_VERIFICATION = 'has_verification';
    public const CVV_IMAGE_URL = 'cvv_image_url';
    public const ICONS = 'icons';
    public const DOCUMENT_TYPES = 'document_types';
    public const PROMOTION_ACTION_URL = 'promotions_action_url';
    public const PROMOTION_BY_BIN_ACTION_URL = 'promotions_by_bin_action_url';
    public const EMITTERS = 'emitters';
    public const INSTALLMENTS = 'installments';
    public const CREDIT_CARD_METHOD = 'credit_card_method';
    /**#@-*/
}
