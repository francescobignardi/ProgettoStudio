<?php

namespace App;

class Product
{
    private string $name;
    private int $quantity;

    public function __construct(string $name, int $quantity)
    {
        $this->name = $name;
        $this->quantity = $quantity;
    }

    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    public function getName(): string
    {
        return $this->name;
    }
}