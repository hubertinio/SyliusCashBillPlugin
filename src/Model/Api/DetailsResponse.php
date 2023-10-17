<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class DetailsResponse
{
    public string $id;

    public string $paymentChannel;

    public string $title;

    public string $description;

    public string $status;

    public string $additionalData;

    public Amount $amount;

    public Amount $requestedAmount;

    public PersonalData $personalData;
}