<?php

namespace App\Services;

use League\Csv\Reader;

class InventoryAnalyzerService
{
    private const LOW_STOCK_THRESHOLD = 10;
    private const TYPE_IN = 'in';
    private const TYPE_OUT = 'out';

    public function analyze(\SplFileInfo|string $file): array
    {
        $database = [];
        $balance = [];
        $anomalies = [];
        $rows_stock = [];
        $rows_low_stock = [];

        $records = Reader::from($file, 'r')->setHeaderOffset(0)->getRecords();

        foreach ($records as $rec) {
            $error = false;

            foreach ($rec as $key => $val) {
                if (!$this->validateRecord($key, $val)) {
                    $error = true;
                }
            }

            if (!$error) {
                $database[] = $rec;
            }
        }

        foreach ($database as $key => $item) {
            if (!isset($balance[$item['product_id']]['quantity'])) {
                $balance[$item['product_id']]['quantity'] = $item['type'] === 'in' ? $item['quantity'] : $item['quantity'] * -1;
            } else {
                $balance[$item['product_id']]['quantity'] = $item['type'] === 'in' ? $balance[$item['product_id']]['quantity'] + $item['quantity'] : $balance[$item['product_id']]['quantity'] - $item['quantity'];
            }
            $balance[$item['product_id']]['product_id'] = $item['product_id'];
            $balance[$item['product_id']]['product_name'] = $item['product_name'];

            if ($balance[$item['product_id']]['quantity'] < 0) {
                $anomalies[$item['product_id']] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'message' => 'Stock went negative',
                ];
            }
        }

        foreach ($balance as $item) {
            $rows_stock[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
            ];

            if ($item['quantity'] < self::LOW_STOCK_THRESHOLD) {
                $rows_low_stock[] = [
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        $rows_anomalies = array_values($anomalies);

        return [
            'stock' => $rows_stock,
            'low_stock' => $rows_low_stock,
            'anomalies' => $rows_anomalies,
        ];
    }

    private function validateRecord(string $key, mixed $val): bool
    {
        if ($key === 'timestamp' || $key === 'quantity') {
            return (!empty($val) && is_numeric($val));
        } elseif ($key === 'type') {
            return ($val === 'in' || $val === 'out');
        } else {
            return !empty($val);
        }
    }
}
