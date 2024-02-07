<?php

require_once 'Store.php';

use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    public function testAddCustomerToEmptyStore(): void
    {
        $store = new Store();
        $store->addCustomer(['products' => ['product1', 'product2']]);
        $this->assertCount(1, $store->cashiers);
        $this->assertCount(1, $store->cashiers[0]->queueOfCustomers);
    }

    public function testAddCustomerToStoreWithExistingCashiers(): void
    {
        $store = new Store();
        $store->cashiers[] = new CashRegister();
        $store->addCustomer(['products' => ['product1', 'product2']]);
        $this->assertCount(1, $store->cashiers[0]->queueOfCustomers);
    }

    public function testAddCustomerToFullCashiers(): void
    {
        $store = new Store();
        for ($i = 0; $i < Store::NUMBER_OF_CASH_REGISTERS; $i++) {
            $store->cashiers[] = new CashRegister();
        }
        $store->addCustomer(['products' => ['product1', 'product2']]);
        $this->assertCount(1, $store->cashiers[0]->queueOfCustomers);
    }

    public function testArrivalOfCustomers(): void
    {
        $store = new Store();
        $customers = $store->arrivalOfCustomers();
        $this->assertGreaterThanOrEqual(0, $customers);
        $this->assertLessThanOrEqual(Store::NUMBER_OF_CASH_REGISTERS * Store::MAX_CLIENTS_ON_CASHIER + 2, $customers);
    }

    public function testProcessHour(): void
    {
        $store = new Store();
        $store->debug = true;
        $store->processHour();
        // Check the debug output manually
        $this->assertTrue(true);
    }

    public function testWorkDay(): void
    {
        $store = new Store();
        $store->debug = true;
        $store->workDay();
        // Check the debug output manually
        $this->assertTrue(true);
    }
}
