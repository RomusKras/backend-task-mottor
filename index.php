<?php
require 'Store.php';

// Инициализация магазина
$store = new Store();
$store->debug = true;
$store->workDay();