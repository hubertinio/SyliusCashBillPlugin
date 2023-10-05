<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class PersonalData
{
    // First name
    public ?string $firstName = null;

    // Surname
    public ?string $surname = null;

    // E-mail
    public ?string $email = null;

    // Country
    public ?string $country = null;

    // City
    public ?string $city = null;

    // Post code
    public ?string $postcode = null;

    // Street
    public ?string $street = null;

    // Building no
    public ?string $house = null;

    // Flat no
    public ?string $flat = null;

    // IP in IPv4 format
    public ?string $ip = null;
}