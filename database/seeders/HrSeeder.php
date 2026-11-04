<?php
namespace Database\Seeders;
use App\Models\{Department, Employee, LeaveType, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@example.com'],
            ['name' => 'HR Admin', 'password' => Hash::make('password'), 'role' => 'admin']);
        foreach (['Engineering', 'Human Resources', 'Finance', 'Sales'] as $d) Department::firstOrCreate(['name' => $d]);
        LeaveType::firstOrCreate(['name' => 'Vacation'], ['days_per_year' => 15, 'paid' => true]);
        LeaveType::firstOrCreate(['name' => 'Sick'], ['days_per_year' => 10, 'paid' => true]);
        LeaveType::firstOrCreate(['name' => 'Unpaid'], ['days_per_year' => 30, 'paid' => false]);
        Employee::firstOrCreate(['email' => 'admin@example.com'], [
            'user_id' => $admin->id, 'department_id' => Department::where('name', 'Human Resources')->value('id'),
            'employee_no' => 'EMP-0001', 'first_name' => 'HR', 'last_name' => 'Admin',
            'position' => 'HR Manager', 'hire_date' => now()->subYear(), 'basic_salary' => 50000, 'allowances' => 2000,
        ]);
    }
}
