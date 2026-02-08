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
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        // Initialize progress (first chunk only)
        if (!Cache::has("{$this->cacheKey}:total")) {
            Cache::put("{$this->cacheKey}:total", $rows->count(), 600);
            Cache::put("{$this->cacheKey}:processed", 0, 600);
            Cache::put("{$this->cacheKey}:status", 'running', 600);
        }

        foreach ($rows as $row) {

            try {

                /* -------------------------------------------------
                 |  Basic validation
                 -------------------------------------------------*/
                if (empty($row['name']) || empty($row['stream'])) {
                    $this->skipped++;
                    Cache::increment("{$this->cacheKey}:processed");
                    continue;
                }

                /* -------------------------------------------------
                 |  Duplicate check for OLD REG NUMBER only
                 -------------------------------------------------*/
                $oldRegNo = trim($row['admission_number'] ?? '');

                if ($oldRegNo !== '' &&
                    StudentRecord::where('old_reg_no', $oldRegNo)->exists()
                ) {
                    $this->duplicates++;
                    $this->skipped++;
                    Cache::increment("{$this->cacheKey}:processed");
                    continue;
                }

                /* -------------------------------------------------
                 |  Resolve section (P, Q, S4P, Senior 4 P)
                 -------------------------------------------------*/
                $stream = strtoupper(trim($row['stream']));

                if (preg_match('/^[A-Z]$/', $stream)) {
                    $sectionLetter = $stream;
                } else {
                    preg_match('/([A-Z])$/', $stream, $m);
                    if (!isset($m[1])) {
                        $this->skipped++;
                        Cache::increment("{$this->cacheKey}:processed");
                        continue;
                    }
                    $sectionLetter = $m[1];
                }

                preg_match('/(\d+)/', $this->myClass->name, $classMatch);
                $sectionName = 'S' . ($classMatch[1] ?? '') . $sectionLetter;

                $section = Section::where('name', $sectionName)
                    ->where('my_class_id', $this->myClass->id)
                    ->first();

                if (!$section) {
                    $this->skipped++;
                    Cache::increment("{$this->cacheKey}:processed");
                    continue;
                }

                /* -------------------------------------------------
                 |  RETRY-ON-DUPLICATE INSERT (SAFE)
                 -------------------------------------------------*/
                $maxRetries = 5;
                $attempts   = 0;

                do {
                    try {

                        DB::transaction(function () use ($row, $oldRegNo, $section) {

                            // 🔁 regenerate every retry
                            $admNo = AdminNoHelper::generateAdmissionNo(
                                $this->classTypeCode,
                                now()->year
                            );

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

                        // ✅ success
                        $this->imported++;
                        break;

                    } catch (QueryException $e) {

                        // MySQL duplicate key
                        if ($e->errorInfo[1] === 1062 && ++$attempts < $maxRetries) {
                            usleep(100000 * $attempts); // backoff
                            continue;
                        }

                        throw $e;
                    }

                } while ($attempts < $maxRetries);

            } catch (\Throwable $e) {

                $this->skipped++;

                Log::error('Student import row failed', [
                    'row'   => $row,
                    'error' => $e->getMessage(),
                ]);
            }

            Cache::increment("{$this->cacheKey}:processed");
        }

        Cache::put("{$this->cacheKey}:status", 'done', 600);
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
