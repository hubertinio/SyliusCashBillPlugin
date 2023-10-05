<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class TransactionRequest
{
    public ?string $sign = null;

    public ?string $description = null;

    public ?string $additionalData = null;

    public ?string $returnUrl = null;

    public ?string $negativeReturnUrl = null;

    public int $paymentChannel = 0;

    public ?string $referer = null;

    public function __construct(
        public string $title,
        public string $languageCode,
        public Amount $amount,
        public PersonalData $personalData
    ) {
    }
}