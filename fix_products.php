<?php
require "vendor/autoload.php";
use MongoDB\Client;

$client = new Client("mongodb+srv://kiranoli421_db_user:keFt9XKE7oIdaAY2@cluster0.dxc9xkf.mongodb.net/");
$db = $client->selectDatabase("khairawang_dairy");
$products = $db->selectCollection("products");

$result = $products->updateMany(
    [],
    ['$set' => ['status' => 'published', 'featured' => true]]
);

echo "Updated " . $result->getModifiedCount() . " products\n";
echo "Total: " . $products->countDocuments() . "\n";
