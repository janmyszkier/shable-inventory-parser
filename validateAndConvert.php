<?php

require 'inventoryToJson.php';

$inventoryContents = file_get_contents('./inventory');
$converter = new \Shable\Converter\InventoryToJson($inventoryContents);

echo $converter->convert();