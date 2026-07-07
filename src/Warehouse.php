<?php

class Warehouse
{
    private array $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function countAvailable(): int {
        $counter = 0;
        foreach ($this->products as $product) {
            if ($product->isAvailable()) {
                $counter++;
            }
        }
        return $counter;
    }

    /**
     * @return Product[]
     */
    public function availableProducts(): array {
        $products = [];
        foreach ($this->products as $product) {
            if ($product->isAvailable()) {
                $products[] = $product;
            }
        }
        return $products;
    }
}