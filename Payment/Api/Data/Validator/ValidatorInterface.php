<?php
/**
 *
 */

declare(strict_types=1);

namespace Line\Payment\Api\Data\Validator;

use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 *
 */
interface ValidatorInterface
{

    /**
     * @var string
     */
    const STATUS_APPROVED = 'AUTORIZADA';

    /**
     * @var string
     */
    const STATUS_NOT_AUTORIZED = 'NOAUTORIZADA';

    /**
     * @var string
     */
    const STATUS_ERROR = 'ERROR';

    /**
     * @var string
     */
    const STATUS_CONFIGURATION_ERROR = 'ERRORCONFIGURACION';

    /**
     * @var string
     */
    const STATUS_PENDING_ANNULLED = 'PENDIENTEANULACION';

    /**
     * @var string
     */
    const STATUS_ANNULLED = 'ANULADA';

    /**
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface;
}
