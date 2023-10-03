<?php

declare(strict_types=1);

namespace Hubertinio\SyliusCashBillPlugin\Model\Api;

final class Channel
{
    public int $id;

    public string $name;

    public string $description;

    public string $logoUrl;

    public array $availableCurrencies;

    public static function createFromStdClass(\stdClass $std): self
    {
        $channel = new self;

        $channel->id = (int) $std->id;
        $channel->name = $std->name;
        $channel->description = $std->description;
        $channel->logoUrl = $std->logoUrl;
        $channel->availableCurrencies = $std->availableCurrencies;

        return $channel;
    }

    public function toArray(): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logoUrl' => $this->logoUrl,
            'availableCurrencies' => $this->availableCurrencies,
        ];
    }
}