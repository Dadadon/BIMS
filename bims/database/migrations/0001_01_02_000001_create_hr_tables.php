<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('commission_model', ['sale_type_rate', 'company_percentage'])->default('sale_type_rate');
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('job_titles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('leave_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('annual_days', 5, 1)->default(0);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leave_group_id')->nullable()->constrained('leave_groups')->nullOnDelete();
            $table->string('employee_code', 50)->unique();
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('email')->nullable();
            $table->string('company_email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other', 'Unspecified'])->nullable();
            $table->string('civil_status', 50)->nullable();
            $table->date('birthday')->nullable();
            $table->string('birthplace')->nullable();
            $table->text('home_address')->nullable();
            $table->string('national_id', 100)->nullable();
            $table->enum('employment_type', ['Regular', 'Trainee', 'Contract', 'Part-time'])->default('Regular');
            $table->enum('employment_status', ['Active', 'Archived', 'Terminated'])->default('Active');
            $table->date('start_date')->nullable();
            $table->date('regularization_date')->nullable();
            $table->boolean('is_salaried')->default(false);
            $table->decimal('base_rate', 12, 2)->default(0.00)->comment('hourly or daily rate');
            $table->string('avatar')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employment_status', 'company_id']);
        });

        // Deferred FK: users.employee_id → employees.id (users table created before employees)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });

        // Base reference data — inserted here so it's available immediately after migration
        $now = now();

        DB::table('companies')->insertOrIgnore([
            ['name' => 'My Company', 'commission_model' => 'sale_type_rate', 'commission_rate' => 0.00, 'is_primary' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('departments')->insertOrIgnore([
            ['name' => 'Operations', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sales',      'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance',    'created_at' => $now, 'updated_at' => $now],
            ['name' => 'IT',         'created_at' => $now, 'updated_at' => $now],
            ['name' => 'HR',         'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('job_titles')->insertOrIgnore([
            ['title' => 'Sales Agent',        'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Senior Sales Agent', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Team Leader',        'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Operations Manager', 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'HR Coordinator',     'created_at' => $now, 'updated_at' => $now],
            ['title' => 'Finance Officer',    'created_at' => $now, 'updated_at' => $now],
            ['title' => 'IT Specialist',      'created_at' => $now, 'updated_at' => $now],
        ]);

        DB::table('leave_groups')->insertOrIgnore([
            ['name' => 'Standard (15 days)',    'annual_days' => 15.0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Probationary (5 days)', 'annual_days' => 5.0,  'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Senior (20 days)',       'annual_days' => 20.0, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::dropIfExists('employees');
        Schema::dropIfExists('leave_groups');
        Schema::dropIfExists('job_titles');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('companies');
    }
};
