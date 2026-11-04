<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->string('role')->default('employee')); // admin | hr | employee

        Schema::create('departments', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique(); $t->timestamps();
        });
        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->string('employee_no')->unique();
            $t->string('first_name'); $t->string('last_name');
            $t->string('email')->unique(); $t->string('phone')->nullable();
            $t->string('position'); $t->date('hire_date');
            $t->decimal('basic_salary', 12, 2)->default(0);
            $t->decimal('allowances', 12, 2)->default(0);
            $t->string('status')->default('active'); // active | inactive
            $t->timestamps();
        });
        Schema::create('attendances', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->date('date'); $t->time('clock_in')->nullable(); $t->time('clock_out')->nullable();
            $t->decimal('hours', 5, 2)->nullable();
            $t->string('status')->default('present'); // present | late | absent
            $t->timestamps();
            $t->unique(['employee_id', 'date']);
        });
        Schema::create('leave_types', function (Blueprint $t) {
            $t->id(); $t->string('name')->unique();
            $t->unsignedSmallInteger('days_per_year')->default(10);
            $t->boolean('paid')->default(true); $t->timestamps();
        });
        Schema::create('leave_requests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('leave_type_id')->constrained();
            $t->date('start_date'); $t->date('end_date');
            $t->unsignedSmallInteger('days'); $t->text('reason')->nullable();
            $t->string('status')->default('pending'); // pending | approved | rejected
            $t->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('payrolls', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->string('period', 7); // YYYY-MM
            $t->decimal('basic', 12, 2); $t->decimal('allowances', 12, 2)->default(0);
            $t->decimal('overtime_pay', 12, 2)->default(0); $t->decimal('deductions', 12, 2)->default(0);
            $t->decimal('net_pay', 12, 2); $t->string('status')->default('draft'); // draft | paid
            $t->timestamps();
            $t->unique(['employee_id', 'period']);
        });
        Schema::create('job_openings', function (Blueprint $t) {
            $t->id(); $t->string('title');
            $t->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $t->text('description')->nullable(); $t->unsignedSmallInteger('vacancies')->default(1);
            $t->string('status')->default('open'); // open | closed
            $t->timestamps();
        });
        Schema::create('applicants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('job_opening_id')->constrained()->cascadeOnDelete();
            $t->string('name'); $t->string('email'); $t->string('phone')->nullable();
            $t->string('stage')->default('applied'); // applied|screening|interview|offer|hired|rejected
            $t->text('notes')->nullable(); $t->timestamps();
        });
        Schema::create('performance_reviews', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('period'); // e.g. 2025-Q1
            $t->unsignedTinyInteger('rating'); // 1-5
            $t->text('goals')->nullable(); $t->text('comments')->nullable();
            $t->string('status')->default('draft'); // draft | submitted | acknowledged
            $t->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['performance_reviews','applicants','job_openings','payrolls','leave_requests','leave_types','attendances','employees','departments'] as $t) Schema::dropIfExists($t);
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
