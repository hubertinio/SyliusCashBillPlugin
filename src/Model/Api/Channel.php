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

    public static function createFromArray(array $item): self
    {
        $channel = new self;

        $channel->id = (int) $item['id'];
        $channel->name = $item['name'];
        $channel->description = $item['description'];
        $channel->logoUrl = $item['logoUrl'];
        $channel->availableCurrencies = (array) $item['availableCurrencies'];

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