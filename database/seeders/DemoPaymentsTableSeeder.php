<?php

namespace Database\Seeders;

use App\Helpers\Qs;
use App\Models\MyClass;
use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\StudentRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * DEMO DATA ONLY - populates fees & payments so the dashboard has data to show.
 * Re-running replaces previous demo records.
 */
class DemoPaymentsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('payment_records')->delete();
        DB::table('payments')->delete();

        $session = Qs::getCurrentSession();
        $term = 1;

        // ---- Backfill gender for students (used by dashboard chart) ----
        StudentRecord::with('user')->get()->each(function ($sr) {
            if ($sr->user && empty($sr->user->gender)) {
                $sr->user->update(['gender' => rand(0, 1) ? 'male' : 'female']);
            }
        });

        // ---- Fee structure per class ----
        $fees = [];
        foreach (MyClass::all() as $i => $mc) {
            $tuition = Payment::create([
                'title' => 'Tuition Fees',
                'amount' => 300000 + ($i * 50000),
                'my_class_id' => $mc->id,
                'description' => 'Term tuition for '.$mc->name,
                'year' => $session,
                'ref_no' => 'TUITION-'.$mc->id
            ]);
            $levies = Payment::create([
                'title' => 'Lunch & Levies',
                'amount' => 120000,
                'my_class_id' => $mc->id,
                'description' => 'Meals and school levies',
                'year' => $session,
                'ref_no' => 'LEVY-'.$mc->id
            ]);
            $fees[] = [$tuition, $levies];
        }

        // ---- Payment records spread across last 6 months ----
        $students = StudentRecord::with('user')->get();
        $counter = 0;

        foreach ($students as $student) {
            foreach ($fees as $pair) {
                foreach ($pair as $fee) {
                    if ((int)$fee->my_class_id !== (int)$student->my_class_id) {
                        continue;
                    }

                    $counter++;
                    $full = rand(1, 100) <= 70; // 70% fully paid
                    $amt = $full ? $fee->amount : (int)($fee->amount * rand(30, 70) / 100);
                    $date = now()->subDays(rand(0, 170));

                    PaymentRecord::create([
                        'student_id' => $student->user_id,
                        'payment_id' => $fee->id,
                        'amt_paid' => $amt,
                        'balance' => max(0, $fee->amount - $amt),
                        'paid' => $full ? 1 : 0,
                        'year' => $session,
                        'ref_no' => 'DEMO-'.str_pad($counter, 5, '0', STR_PAD_LEFT),
                        'term' => $term,
                        'payment_date' => $date->format('Y-m-d'),
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
            }
        }
    }
}
