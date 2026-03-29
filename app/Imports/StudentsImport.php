<?php

namespace App\Imports;

use App\Helpers\AdminNoHelper;
use App\Helpers\Qs;
use App\Models\StudentRecord;
use App\Models\MyClass;
use App\Models\Section;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class StudentsImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    protected MyClass $myClass;
    protected string $classTypeCode;

    public int $imported  = 0;
    public int $skipped   = 0;
    public int $duplicates = 0;

    protected string $cacheKey;

    public function __construct(MyClass $myClass, string $classTypeCode)
    {
        $this->myClass = $myClass;
        $this->classTypeCode = $classTypeCode;
        $this->cacheKey = 'student_import:' . $myClass->id;
    }

public function collection(Collection $rows)
{
    $this->initProgress($rows->count());

    foreach ($rows as $row) {
        try {
            $this->processRow($row);
        } catch (\Throwable $e) {
            $this->skipped++;
            Log::error('Student import row failed', [
                'row'   => $row->toArray(),
                'error' => $e->getMessage(),
            ]);
        } finally {
            Cache::increment("{$this->cacheKey}:processed"); // ✅ Always runs
        }
    }

    Cache::put("{$this->cacheKey}:status", 'done', 600);
}

protected function initProgress(int $count): void
{
    if (!Cache::has("{$this->cacheKey}:total")) {
        Cache::put("{$this->cacheKey}:total",     $count, 600);
        Cache::put("{$this->cacheKey}:processed", 0,      600);
        Cache::put("{$this->cacheKey}:status",    'running', 600);
    }
}

protected function processRow($row): void
{
    if (empty($row['name']) || empty($row['stream'])) {
        $this->skipped++;
        return;
    }

    $oldRegNo = trim($row['admission_number'] ?? '');

    if ($oldRegNo !== '' && StudentRecord::where('old_reg_no', $oldRegNo)->exists()) {
        $this->duplicates++;
        $this->skipped++;
        Log::warning("Duplicate skipped: old_reg_no={$oldRegNo}");
        return;
    }

    $section = $this->resolveSection($row);

    if (!$section) {
        $this->skipped++;
        return;
    }

    $this->insertWithRetry($row, $oldRegNo, $section);
    $this->imported++;
}

protected function resolveSection($row): ?Section
{
    $stream = strtoupper(trim($row['stream']));

    if (preg_match('/^[A-Z]$/', $stream)) {
        $sectionLetter = $stream;
    } else {
        preg_match('/([A-Z])$/', $stream, $m);
        if (!isset($m[1])) return null;
        $sectionLetter = $m[1];
    }

    preg_match('/(\d+)/', $this->myClass->name, $classMatch);
    $sectionName = 'S' . ($classMatch[1] ?? '') . $sectionLetter;

    return Section::where('name', $sectionName)
        ->where('my_class_id', $this->myClass->id)
        ->first();
}

protected function insertWithRetry($row, string $oldRegNo, Section $section): void
{
    $attempts   = 0;
    $maxRetries = 5;

    do {
        try {
            DB::transaction(function () use ($row, $oldRegNo, $section) {
                $admNo = AdminNoHelper::generateAdmissionNo($this->classTypeCode, now()->year);

                $user = User::create([
                    'name'      => ucwords(strtolower($row['name'])),
                    'username'  => $admNo,
                    'user_type' => 'student',
                    'code'      => strtoupper(Str::random(10)),
                    'password'  => Hash::make('student'),
                    'photo'     => Qs::getDefaultUserImage(),
                    'gender'    => $row['gender'] ?? null,
                ]);

                StudentRecord::create([
                    'user_id'       => $user->id,
                    'my_class_id'   => $this->myClass->id,
                    'section_id'    => $section->id,
                    'adm_no'        => $admNo,
                    'old_reg_no'    => $oldRegNo ?: null,
                    'session'       => Qs::getSetting('current_session'),
                    'fees'          => 0,
                    'grad'          => 0,
                    'year_admitted' => now()->year,
                ]);
            });

            return; // ✅ success

        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062 && ++$attempts < $maxRetries) {
                usleep(100000 * $attempts);
                continue;
            }
            throw $e;
        }

    } while ($attempts < $maxRetries);
}

public function chunkSize(): int { return 200; }
  
}
