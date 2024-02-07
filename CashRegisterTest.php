<?php

use PHPUnit\Framework\TestCase;

class CashRegisterTest extends TestCase
{
    public function testProcessCustomersWithEmptyQueue(): void
    {
        $cashRegister = new CashRegister();
        $result = $cashRegister->processCustomers();
        $this->assertEquals(['totalTime' => 0, 'remainingCustomers' => 0], $result);
    }

    public function testProcessCustomersWithOneCustomer(): void
    {
        $cashRegister = new CashRegister();
        $cashRegister->queueOfCustomers[] = ['products' => ['product1', 'product2']];
        $result = $cashRegister->processCustomers();
        $this->assertGreaterThanOrEqual(0.3, $result['totalTime']); // Assuming default processing times
        $this->assertLessThanOrEqual(1, $result['totalTime']);
        $this->assertEquals(0, $result['remainingCustomers']);
    }

    public function testProcessCustomersWithMultipleCustomers(): void
    {
        $cashRegister = new CashRegister();
        $cashRegister->queueOfCustomers[] = ['products' => ['product1', 'product2']];
        $cashRegister->queueOfCustomers[] = ['products' => ['product3', 'product4']];
        $result = $cashRegister->processCustomers();
        $this->assertGreaterThanOrEqual(0.6, $result['totalTime']); // Assuming default processing times
        $this->assertLessThanOrEqual(2, $result['totalTime']);
        $this->assertEquals(0, $result['remainingCustomers']);
    }

    public function testProcessCustomersWithCustomerExceedingOneHour(): void
    {
        $cashRegister = new CashRegister();
        $cashRegister->queueOfCustomers[] = ['products' => array_fill(0, 20, 'product')]; // 20 products
        $result = $cashRegister->processCustomers();
        $this->assertEquals(1, $result['totalTime']); // Only one hour for processing
        $this->assertGreaterThan(0, $result['remainingCustomers']); // Some customers left
    }
}
