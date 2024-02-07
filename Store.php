<?php
require "BaseClass.php";
require "CashRegister.php";
class Store extends BaseClass
{
    const NUMBER_OF_CASH_REGISTERS = 5;
    const WAITING_TIME_FOR_NEW_CUSTOMERS = 1; // Время, после которого касса закрывается, если нет покупателей
    public array $cashiers = [];
    public int $totalHours = 0;
    const MAX_CLIENTS_ON_CASHIER = 5;
    public int $workingHours = 8;

    /**
     * Находим кассу с наименьшей очередью и добавляем покупателя в нее, а также сбрасываем счетчик часов без покупателей для этой кассы.
     * @param $customer
     * @return void
     */
    function addCustomer($customer): void
    {
        $minQueueIndex = 0;
        $minQueueLength = PHP_INT_MAX; // текущее минимальное значение очереди
        // Если нет ни одной открытой кассы - нужно ее создать
        if (empty($this->cashiers)) {
            $this->cashiers[] = new CashRegister(); // Создаем новую кассу
        }
        // цикл проходится по всем кассам в магазине
        foreach ($this->cashiers as $index => $cashier) {
            // Проверяем, что касса существует и у нее длина очереди меньше, чем текущее минимальное значение очереди ($minQueueLength).
            if (!empty($cashier) && count($cashier->queueOfCustomers) < $minQueueLength) {
                // Если это условие выполняется, то мы обновляем значения $minQueueIndex и $minQueueLength.
                $minQueueIndex = $index;
                $minQueueLength = count($cashier->queueOfCustomers);
            }
        }
        // Проверяем, что найденная касса с минимальной очередью существует.
        if (!empty($this->cashiers[$minQueueIndex]) && $minQueueLength < self::MAX_CLIENTS_ON_CASHIER) {
            // добавляем покупателя $customer в очередь найденной кассы с минимальной длиной.
            $this->cashiers[$minQueueIndex]->queueOfCustomers[] = $customer;
            // Обнуляем счетчик часов без покупателей у найденной кассы, так как к ней добавлен новый покупатель.
            $this->cashiers[$minQueueIndex]->hoursSinceLastCustomer = 0;
        } elseif (count($this->cashiers) < self::NUMBER_OF_CASH_REGISTERS) {
            // Если еще можно создать кассу, то создаем
            $freeIndex = count($this->cashiers);
            $this->cashiers[$freeIndex] = new CashRegister(); // Создаем новую кассу
            // Добавляем покупателя $customer в очередь созданной кассы
            $this->cashiers[$freeIndex]->queueOfCustomers[] = $customer;
        } else { // Иначе добавляем в первую попавшшуюся кассу
            foreach ($this->cashiers as $index => $cashier) {
                $this->cashiers[$index]->queueOfCustomers[] = $customer;
                break;
            }
        }
    }

    private function arrivalOfCustomers(): int
    {
        if ($this->totalHours > 3) {
            $numberOfCustomers = rand(self::MAX_CLIENTS_ON_CASHIER, self::NUMBER_OF_CASH_REGISTERS * self::MAX_CLIENTS_ON_CASHIER + 2);
        } else {
            $numberOfCustomers = rand(0, self::MAX_CLIENTS_ON_CASHIER);
        }
        return $numberOfCustomers;
    }

    function processHour(): float|int
    {
        // Моделируем приход новых покупателей
        $numberOfCustomers = $this->arrivalOfCustomers();
        // Добавление инфы о покупателях и распределение по кассам
        if ($this->debug) {
            $totalCustomers = 0;
            /** @var CashRegister $cashier */
            foreach ($this->cashiers as $cashier) {
                $totalCustomers += count($cashier->queueOfCustomers);
            }
            $this->echoDebugMessage("Пришло $numberOfCustomers покупателя(ей), всего их ".($numberOfCustomers+$totalCustomers));
        }
        for ($i = 0; $i < $numberOfCustomers; $i++) {
            $this->addCustomer(['products' => ['product1', 'product2']]); // Просто для примера 2 товара
        }
        $this->echoDebugMessage("Касс ".count($this->cashiers));
        // Обработка покупателей
        $totalTimeProcessing = 0;
        /** @var CashRegister $cashier */
        foreach ($this->cashiers as $index => $cashier) {
            if (empty($cashier->queueOfCustomers)) {
                $cashier->hoursSinceLastCustomer++; // Увеличиваем количество часов без покупателя
            } else {
                $processingResult = $cashier->processCustomers();
                $totalTimeProcessing += $processingResult['totalTime'];
                echo "На кассе ".($index+1)." осталось {$processingResult['remainingCustomers']} покупателей, отработано {$processingResult['totalTime']} часов".PHP_EOL;
                $cashier->hoursSinceLastCustomer = 0;
            }
        }

        // Проверяем кассы на закрытие
        foreach ($this->cashiers as $index => $cashier) {
            if (empty($cashier->queueOfCustomers) && $cashier->hoursSinceLastCustomer > self::WAITING_TIME_FOR_NEW_CUSTOMERS) {
                // Закрываем кассу, если нет покупателей и прошло достаточно времени
                unset($this->cashiers[$index]);
                echo "Кассир ушел с кассы $index спустя час".PHP_EOL;
            }
        }

        // Выводим информацию о текущем состоянии
        $this->totalHours += 1;
        echo "Час: $this->totalHours".PHP_EOL;
        echo "Количество работающих касс после часа: " . count($this->cashiers) . "\n";
        foreach ($this->cashiers as $index => $cashier) {
            echo "Размер очереди на кассе " . ($index + 1) . " по итогам дня: " . count($cashier->queueOfCustomers) . "\n";
        }
        echo "Общее время обработки покупателей на ".count($this->cashiers)." кассах(е): $totalTimeProcessing часов".PHP_EOL;
        echo "\n";

        // Возвращаем оставшееся время обработки
        return $totalTimeProcessing;
    }

    public function workDay(): void
    {
        $totalProcessingTime = 0;
        // Моделирование рабочего дня
        for ($hour = 1; $hour <= $this->workingHours; $hour++) {
            $totalProcessingTime += $this->processHour();
        }
        echo "Общее время обработки покупателей за день: $totalProcessingTime часов".PHP_EOL;
        if (!empty($this->cashiers)) {
            /** @var CashRegister $cashier */
            $hasCustomers = 0;
            $cashiersWithCustomers = 0;
            foreach ($this->cashiers as $cashier) {
                if (!empty($cashier->queueOfCustomers)) {
                    $hasCustomers += count($cashier->queueOfCustomers);
                    $cashiersWithCustomers++;
                }
            }
            if ($hasCustomers > 0) {
                echo "Осталось $hasCustomers покупателей на $cashiersWithCustomers кассах(е) на момент окончания рабочего дня".PHP_EOL;
            }
        }
    }
}