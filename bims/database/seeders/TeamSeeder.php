<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Employee IDs (from earlier tinker output)
        // 1=Santos, 2=Reyes, 3=Cruz, 4=Mendoza, 5=Villanueva, 6=Lim, 7=Tan, 8=Aquino, 9=Beroni

        $teams = [
            ['name' => 'Alpha Team',  'leader_id' => 2, 'description' => 'Primary sales team',    'is_active' => true],
            ['name' => 'Beta Team',   'leader_id' => 5, 'description' => 'Secondary sales team',  'is_active' => true],
            ['name' => 'Operations',  'leader_id' => 1, 'description' => 'Operations & support',  'is_active' => true],
        ];

        $teamIds = [];
        foreach ($teams as $team) {
            $existing = DB::table('teams')->where('name', $team['name'])->first();
            if ($existing) {
                $teamIds[$team['name']] = $existing->id;
            } else {
                $teamIds[$team['name']] = DB::table('teams')->insertGetId(
                    array_merge($team, ['created_at' => now(), 'updated_at' => now()])
                );
            }
        }

        $alpha = $teamIds['Alpha Team'];
        $beta  = $teamIds['Beta Team'];
        $ops   = $teamIds['Operations'];

        // Assign employees to teams
        $assignments = [
            1 => $ops,    // Santos → Operations
            2 => $alpha,  // Reyes  → Alpha (leader)
            3 => $alpha,  // Cruz   → Alpha
            4 => $alpha,  // Mendoza → Alpha
            5 => $beta,   // Villanueva → Beta (leader)
            6 => $beta,   // Lim    → Beta
            7 => $beta,   // Tan    → Beta
            8 => $ops,    // Aquino → Operations
            9 => $ops,    // Beroni → Operations
        ];

        foreach ($assignments as $employeeId => $teamId) {
            DB::table('employees')
                ->where('id', $employeeId)
                ->update(['team_id' => $teamId, 'updated_at' => now()]);
        }

        // Backfill team_id on existing sales from the employee's team
        foreach ($assignments as $employeeId => $teamId) {
            DB::table('sales')
                ->where('employee_id', $employeeId)
                ->whereNull('team_id')
                ->update(['team_id' => $teamId, 'updated_at' => now()]);
        }

        $backfilled = DB::table('sales')->whereNotNull('team_id')->count();

        $this->command->info("Created " . count($teams) . " teams, assigned " . count($assignments) . " employees, backfilled {$backfilled} sales.");
    }
}
