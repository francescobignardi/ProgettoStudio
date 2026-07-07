<?php

require 'src/Product.php';
require 'src/Warehouse.php';

$name1 = "Vite";
$name2 = "Rondella";
$name3 = "Cacciavite";
$name4 = "Martello";
$product1 = 5;
$product2 = 0;
$product3 = 2;
$product4 = 1;

$instance1 = new Product($name1,$product1);
$instance2 = new Product($name2,$product2);
$instance3 = new Product($name3,$product3);
$instance4 = new Product($name4,$product4);

$array = [$instance1,$instance2,$instance3,$instance4];

$warehouse = new Warehouse($array);

echo "Prodotti disponibili: " . $warehouse->countAvailable();

$available = $warehouse->availableProducts();

echo "\nElenco disponibili:";
foreach($available as $product) {
    echo "\n- " . $product->getName();
}