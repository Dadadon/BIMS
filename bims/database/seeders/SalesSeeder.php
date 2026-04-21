<?php

namespace Database\Seeders;

use App\Models\Sales\Sale;
use App\Models\Sales\SaleType;
use App\Services\Sales\CommissionCalculator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    // employee_id => [sale_type_ids they typically sell]
    private array $reps = [
        1 => [1, 2, 4],       // Maria Santos    — Xfinity Internet, Xfinity Internet+TV, AT&T Internet
        2 => [1, 3, 6],       // James Reyes      — Xfinity Internet, Xfinity Triple, Spectrum Internet
        3 => [4, 5],          // Angela Cruz      — AT&T Internet, AT&T Mobile
        4 => [1, 2, 3],       // Carlos Mendoza   — Xfinity focus
        5 => [6, 7],          // Trisha Villanueva — Spectrum focus
        6 => [1, 4, 6],       // Kevin Lim        — all Internet
        7 => [2, 3, 7],       // Rachel Tan       — bundles
        8 => [4, 5, 6],       // Mark Aquino      — AT&T + Spectrum
        9 => [1, 2, 3, 4, 5, 6, 7], // Davian Beroni — all types
    ];

    private array $customerFirstNames = [
        'John', 'Emily', 'Michael', 'Sarah', 'David', 'Jennifer', 'Robert', 'Jessica',
        'William', 'Amanda', 'Christopher', 'Ashley', 'Matthew', 'Stephanie', 'Joshua',
        'Nicole', 'Andrew', 'Elizabeth', 'Daniel', 'Michelle', 'Anthony', 'Megan',
        'Mark', 'Lauren', 'Donald', 'Christina', 'Steven', 'Rebecca', 'Paul', 'Sharon',
        'Raymond', 'Cynthia', 'Brian', 'Angela', 'Edward', 'Deborah', 'Ronald', 'Patricia',
    ];

    private array $customerLastNames = [
        'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
        'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
        'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
        'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson',
    ];

    private array $phoneAreaCodes = [
        '213', '312', '404', '602', '702', '713', '808', '904', '702', '503',
        '619', '407', '305', '312', '214', '817', '512', '718', '347', '646',
    ];

    public function run(): void
    {
        $calculator = app(CommissionCalculator::class);

        $saleTypes = SaleType::all()->keyBy('id');

        $statuses = [
            'Approved'             => 65,
            'Submitted'            => 20,
            'Cancelled'            => 10,
            'Pending Cancellation' => 5,
        ];

        $records = $this->buildRecords($statuses, $calculator, $saleTypes);

        foreach ($records as $record) {
            Sale::create($record);
        }

        $this->command->info('Inserted ' . count($records) . ' sales records.');
    }

    private function buildRecords(array $statuses, CommissionCalculator $calc, $saleTypes): array
    {
        $records = [];
        $today   = Carbon::today();

        // Generate ~80 sales spread over the last 90 days
        for ($i = 0; $i < 80; $i++) {
            $daysAgo    = rand(0, 90);
            $saleDate   = $today->copy()->subDays($daysAgo);
            $employeeId = array_rand($this->reps);
            $saleTypeId = $this->reps[$employeeId][array_rand($this->reps[$employeeId])];
            $status     = $this->weightedRandom($statuses);
            $points     = $calc->calculate($employeeId, $saleTypeId);

            $records[] = [
                'employee_id'           => $employeeId,
                'sale_type_id'          => $saleTypeId,
                'sale_date'             => $saleDate->format('Y-m-d'),
                'customer_name'         => $this->randomName(),
                'customer_phone'        => $this->randomPhone(),
                'total_points'          => $points['total_points'],
                'agent_points'          => $points['agent_points'],
                'status'                => $status,
                'compensation_received' => $status === 'Approved' && $daysAgo > 30 && rand(0, 1),
                'metadata'              => null,
                'created_at'            => $saleDate,
                'updated_at'            => $saleDate,
            ];
        }

        return $records;
    }

    private function randomName(): string
    {
        return $this->customerFirstNames[array_rand($this->customerFirstNames)]
             . ' '
             . $this->customerLastNames[array_rand($this->customerLastNames)];
    }

    private function randomPhone(): string
    {
        $area = $this->phoneAreaCodes[array_rand($this->phoneAreaCodes)];
        return sprintf('(%s) %03d-%04d', $area, rand(200, 999), rand(1000, 9999));
    }

    private function weightedRandom(array $weights): string
    {
        $roll  = rand(1, array_sum($weights));
        $cumul = 0;
        foreach ($weights as $value => $weight) {
            $cumul += $weight;
            if ($roll <= $cumul) return $value;
        }
        return array_key_first($weights);
    }
}
