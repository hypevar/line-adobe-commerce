<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Request;

use Line\Payment\Api\Request\BuilderInterface;

/**
 * Allows external modules to add custom fields into the request
 *
 * External modules must implement their custom builders through
 * dependency injection against the `builders` argument.
 *
 * This class won't allow core builders data manipulation.
 * In that case, a plugin for the specific builder must be created.
 *
 * @see \Magento\Payment\Gateway\Request\BuilderComposite
 */
class IntegrationPool implements BuilderInterface
{

    /**
     * @var array of BuilderInterface instances
     */
    private $builders;

    public function __construct(
        array $builders = []
    ) {
        $this->builders = $builders;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $incremental = [];

        foreach ($this->builders as $external) {
            /** @var BuilderInterface $external */
            $result = $external->build($buildSubject);

            $incremental = array_merge($incremental, $result);
        }

        return $incremental;
    }
}
