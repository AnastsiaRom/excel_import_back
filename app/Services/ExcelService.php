<?php

namespace App\Services;

use App\Models\Row;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Shared\Date,
    Spreadsheet,
};

use Illuminate\Support\Facades\Validator;

class ExcelService
{
    public function processExcelFile(string $filePath)
    {
        try {
            $spreadsheet = $this->loadSpreadsheet($filePath);
            $rows        = $this->getRowsFromSpreadsheet($spreadsheet);
            $errors      = $this->validateAndInsertRows($rows);

            if (!empty($errors)) {
                $this->saveErrorsToFile($errors);
            }
        } catch (\Exception $e) {
            Log::error('Error processing Excel file: ' . $e->getMessage());
            throw $e; // Повторно бросаем исключение, чтобы Job был повторно добавлен в очередь
        }
    }

    protected function loadSpreadsheet($filePath): Spreadsheet
    {
        return IOFactory::load($filePath);
    }

    protected function getRowsFromSpreadsheet(Spreadsheet $spreadsheet): array
    {
        $worksheet = $spreadsheet->getActiveSheet();
        return $worksheet->toArray();
    }

    protected function validateAndInsertRows(array $rows): array
    {
        $errors = [];
        $batch  = [];
        $batchSize = 1000;

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Пропускаем шапку

            $validator = $this->validateRow($row);

            if ($validator->fails()) {
                $errors[] = $index + 1 . ' - ' . implode(', ', $validator->errors()->all());
                continue;
            }

            $data = $this->prepareRowData($row);

            if (!isset($data['excel_id'])) {
                $errors[] = $index + 1 . ' - Invalid data';
                continue;
            }

            if ($this->isDuplicateId($data['excel_id'])) {
                $errors[] = $index + 1 . ' - Duplicate ID';
                continue;
            }

            $batch[] = $data;

            if (count($batch) >= $batchSize) {
                $this->insertBatch($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->insertBatch($batch);
        }

        return $errors;
    }

    protected function validateRow(array $row): \Illuminate\Validation\Validator
    {
        return Validator::make($row, [
            0 => 'required|numeric|min:0',        // excel_id
            1 => 'required|regex:/^[a-zA-Z ]+$/', // name
            2 => 'required|date_format:d.m.Y',    // date
        ]);
    }

    protected function prepareRowData(array $row): array
    {
        $date = $row[2];

        $timestamp = is_string($date) ? strtotime($date) : $date;

        if ($timestamp === false) {
            return [];
        }

        $date = Date::PHPToExcel($timestamp);

        return [
            'excel_id' => $row[0],
            'name'     => $row[1],
            'date'     => Date::excelToDateTimeObject($date)->format('Y-m-d'),
        ];
    }

    protected function isDuplicateId(int $id): bool
    {
        return Row::query()->where('excel_id', $id)->exists();
    }

    protected function insertBatch(array $batch): void
    {
        Row::query()->insert($batch);
    }

    protected function saveErrorsToFile(array $errors): void
    {
        file_put_contents('result.txt', implode("\n", $errors));
    }
}
