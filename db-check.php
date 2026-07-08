<?php

declare(strict_types=1);

$dsn = "mysql:host=mysql;port=3306;dbname=progettostudio;charset=utf8mb4";

try{
    $pdo = new PDO($dsn, "studio", "studio");
    echo "Connessione al database riuscita.\n";
} catch(PDOException $e){
    echo "errore connessione al database: " . $e->getMessage() . " \n";
}