<?php

class Product
{
    private $name;
    private $quantity;

    public function __construct($name, $quantity)
    {
        $this->name = $name;
        $this->quantity = $quantity;
    }

    public function isAvailable(){
        if($this->quantity > 0){
            return true;
        } else {
            return false;
        }
    }
}