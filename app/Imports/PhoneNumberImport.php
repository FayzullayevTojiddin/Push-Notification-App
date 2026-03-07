<?php

namespace App\Imports;

use App\Models\PhoneNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PhoneNumberImport implements ToModel, WithHeadingRow
{
    public int $imported = 0;
    public int $duplicated = 0;

    public function model(array $row): ?PhoneNumber
    {
        $number = trim($row['number'] ?? $row[0] ?? '');

        if (empty($number)) {
            return null;
        }

        // Auto-add + prefix if missing
        if (!str_starts_with($number, '+')) {
            $number = '+' . $number;
        }

        if (PhoneNumber::where('number', $number)->exists()) {
            $this->duplicated++;
            return null;
        }

        $this->imported++;

        return new PhoneNumber([
            'number' => $number,
            'is_active' => true,
        ]);
    }
}
