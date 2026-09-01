<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Model\Security;

use Line\Payment\Api\Data\ConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * The counting window of the throttle, in UTC.
 */
class AttemptWindow
{
    private const FORMAT = 'Y-m-d H:i:s';

    private ConfigInterface $config;
    private DateTime $date;

    /**
     * @param ConfigInterface $config
     * @param DateTime $date
     */
    public function __construct(
        ConfigInterface $config,
        DateTime $date
    ) {
        $this->config = $config;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function now(): string
    {
        return $this->date->gmtDate(self::FORMAT);
    }

    /**
     * Counters whose window started before this are stale and count as zero.
     *
     * @return string
     */
    public function cutoff(): string
    {
        return $this->date->gmtDate(
            self::FORMAT,
            $this->date->gmtTimestamp() - ($this->getMinutes() * 60)
        );
    }

    /**
     * Rows untouched for two full windows can never influence a decision again.
     *
     * @return string
     */
    public function pruneCutoff(): string
    {
        return $this->date->gmtDate(
            self::FORMAT,
            $this->date->gmtTimestamp() - ($this->getMinutes() * 120)
        );
    }

    /**
     * @return int
     */
    public function getMinutes(): int
    {
        return $this->config->getAntifraudWindow();
    }
}
