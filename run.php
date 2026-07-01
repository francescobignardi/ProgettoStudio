<?php

require 'Product.php';

$name = "Nome";
$product1 = 5;
$product2 = 0;

$instance1 = new Product($name,$product1);
$result1 = $instance1->isAvailable();
if ($result1) {
    echo 'Product1: true';
} else {
    echo 'Product1: false';
}

$instance2 = new Product($name,$product2);
$result2 = $instance2->isAvailable();
if ($result2) {
    echo 'Product2: true';
} else {
    echo 'Product2: false';
}