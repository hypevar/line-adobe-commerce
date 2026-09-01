<?php
/**
 * Copyright © 2023 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Allows external modules to add custom fields into the request
 *
 * External modules must implement their custom builders through
 * dependency injection against the `builders` argument.
 *
 * This class won't allow core builders data manipulation.
 * In that case, a plugin for the specific builder must be created.
 *
 * @see \Magento\Payment\Gateway\Response\HandlerChain
 */
class IntegrationPool implements HandlerInterface
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
    public function handle(array $handlingSubject, array $response)
    {
        foreach ($this->builders as $external) {
            $external->handle($handlingSubject, $response);
        }
    }
}
