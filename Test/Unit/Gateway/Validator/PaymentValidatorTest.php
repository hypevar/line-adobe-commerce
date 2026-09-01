<?php
/**
 * Copyright © 2025 Line. All rights reserved.
 */

declare(strict_types=1);

namespace Line\Payment\Test\Unit\Gateway\Validator;

use Line\Payment\Api\Response\AttributeDetailInterface;
use Line\Payment\Api\Response\AttributeInterface;
use Line\Payment\Api\Response\StatusCodeInterface;
use Line\Payment\Api\Response\StatusInterface;
use Line\Payment\Gateway\Validator\PaymentValidator;
use Magento\Framework\DataObject;
use Magento\Payment\Gateway\Validator\Result;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentValidatorTest extends TestCase
{
    /**
     * @var ResultInterfaceFactory|MockObject
     */
    private $resultFactory;

    private PaymentValidator $validator;

    protected function setUp(): void
    {
        $this->resultFactory = $this->getMockBuilder(ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->resultFactory->method('create')->willReturnCallback(
            static function (array $arguments) {
                return new Result(
                    $arguments['isValid'],
                    $arguments['failsDescription'] ?? [],
                    $arguments['errorCodes'] ?? []
                );
            }
        );

        $this->validator = new PaymentValidator($this->resultFactory);
    }

    public function testAcceptsAnAuthorizedResponse(): void
    {
        $this->assertTrue($this->validator->validate($this->authorized())->isValid());
    }

    public function testRejectsWhenTheStatusIsNotAuthorized(): void
    {
        $subject = $this->authorized();
        $subject[AttributeInterface::FIELD_STATUS] = StatusInterface::STATUS_NOT_AUTHORIZED;

        $result = $this->validator->validate($subject);

        $this->assertFalse($result->isValid());
        $this->assertContains(StatusInterface::STATUS_NOT_AUTHORIZED, $result->getFailsDescription());
    }

    public function testRejectsWhenTheStatusCodeIsRejected(): void
    {
        $subject = $this->authorized();
        $subject[AttributeInterface::FIELD_STATUS_CODE] = StatusCodeInterface::STATUS_CODE_REJECTED;

        $this->assertFalse($this->validator->validate($subject)->isValid());
    }

    public function testRejectsWhenTheDetailCarriesAnErrorCode(): void
    {
        $subject = $this->authorized();
        $subject[AttributeInterface::FIELD_DETAIL] = new DataObject([
            AttributeDetailInterface::FIELD_ERROR_CODE => 51
        ]);

        $result = $this->validator->validate($subject);

        $this->assertFalse($result->isValid());
        $this->assertSame([51], $result->getErrorCodes());
    }

    /**
     * Previously fatal: the formatted message was read without a guard.
     */
    public function testDoesNotFailWhenTheFormattedMessageIsAbsent(): void
    {
        $subject = [
            AttributeInterface::FIELD_ERROR_CODE => 51,
            AttributeInterface::FIELD_STATUS => StatusInterface::STATUS_NOT_AUTHORIZED,
            AttributeInterface::FIELD_STATUS_CODE => StatusCodeInterface::STATUS_CODE_REJECTED
        ];

        $result = $this->validator->validate($subject);

        $this->assertFalse($result->isValid());
        $this->assertSame([StatusInterface::STATUS_NOT_AUTHORIZED], $result->getFailsDescription());
    }

    public function testRejectsAnEmptySubject(): void
    {
        $this->assertFalse($this->validator->validate([])->isValid());
    }

    /**
     * Previously a false decline: "0" is not identical to 0.
     */
    public function testTreatsAStringZeroErrorCodeAsSuccess(): void
    {
        $subject = $this->authorized();
        $subject[AttributeInterface::FIELD_ERROR_CODE] = '0';

        $this->assertTrue($this->validator->validate($subject)->isValid());
    }

    public function testTreatsAStringZeroDetailErrorCodeAsSuccess(): void
    {
        $subject = $this->authorized();
        $subject[AttributeInterface::FIELD_DETAIL] = new DataObject([
            AttributeDetailInterface::FIELD_ERROR_CODE => '0'
        ]);

        $this->assertTrue($this->validator->validate($subject)->isValid());
    }

    /**
     * @return array
     */
    private function authorized(): array
    {
        return [
            AttributeInterface::FIELD_ERROR_CODE => 0,
            AttributeInterface::FIELD_STATUS => StatusInterface::STATUS_AUTHORIZED,
            AttributeInterface::FIELD_STATUS_CODE => StatusCodeInterface::STATUS_CODE_APPROVED,
            AttributeInterface::FIELD_FORMATED_MESSAGE => 'Transaccion autorizada'
        ];
    }
}
