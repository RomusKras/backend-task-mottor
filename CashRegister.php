<?php

class CashRegister
{
    public array $queueOfCustomers = [];
    public float $productsProcessingTime;
    public float $paymentProcessingTime;
    public int $hoursSinceLastCustomer = 0;

    public function __construct($productsProcessingTime = 0.1, $paymentProcessingTime = 0.2)
    {
        $this->productsProcessingTime = $productsProcessingTime;
        $this->paymentProcessingTime = $paymentProcessingTime;
    }

    public function processCustomers(): array
    {
        $totalTime = 0;

        if (empty($this->queueOfCustomers)) {
            return ['totalTime' => $totalTime, 'remainingCustomers' => 0];
        }

        while (!empty($this->queueOfCustomers) && $totalTime < 1) {
            $customer = array_shift($this->queueOfCustomers);
            $remainingTime = $this->calculateRemainingTime($customer);

            if ($totalTime > 0) {
                $totalTime += $this->handleRemainingTime($remainingTime, 1 - $totalTime, $customer);
            } else {
                $totalTime += $this->handleInitialTime($remainingTime, $customer);
            }
        }

        return ['totalTime' => $totalTime, 'remainingCustomers' => count($this->queueOfCustomers)];
    }

    private function calculateRemainingTime(array $customer): float
    {
        return $customer['remainingTime'] ?? count($customer['products']) * $this->productsProcessingTime + $this->paymentProcessingTime;
    }

    private function handleRemainingTime(float $remainingTime, float $emptyTime, array &$customer): float
    {
        if ($emptyTime < $remainingTime) {
            return round($remainingTime, 2);
        } else {
            $customer['remainingTime'] = round($remainingTime - $emptyTime, 2);
            array_unshift($this->queueOfCustomers, $customer);
            return round($emptyTime, 2);
        }
    }

    private function handleInitialTime(float $remainingTime, array &$customer): float
    {
        if ($remainingTime <= 1.0) {
            return round($remainingTime, 2);
        } else {
            $customer['remainingTime'] = round($remainingTime - 1, 2);
            array_unshift($this->queueOfCustomers, $customer);
            return 1;
        }
    }
}
