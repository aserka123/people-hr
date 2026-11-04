<?php
namespace App\Http\Controllers;
use App\Models\{Attendance, Employee, LeaveRequest, Payroll};
use Illuminate\Http\Request;

class PayrollController extends CrudController
{
    // Adjust to your country's rules (tax tables, statutory contributions, working days).
    private const TAX_RATE = 0.10;
    private const WORK_DAYS = 22;
    private const OVERTIME_MULTIPLIER = 1.25;

    protected string $model = Payroll::class;
    protected array $with = ['employee'];
    protected array $search = ['period'];
    protected bool $ownedByEmployee = true;

    protected function rules(?int $id = null): array
    {
        return [
            'employee_id' => 'required|exists:employees,id', 'period' => 'required|date_format:Y-m',
            'basic' => 'required|numeric|min:0', 'allowances' => 'required|numeric|min:0',
            'overtime_pay' => 'required|numeric|min:0', 'deductions' => 'required|numeric|min:0',
            'net_pay' => 'required|numeric', 'status' => 'required|in:draft,paid',
        ];
    }

    /** Builds a draft payslip for every active employee for the month (skips ones already paid). */
    public function generate(Request $r)
    {
        $period = $r->validate(['period' => 'required|date_format:Y-m'])['period'];
        [$y, $m] = explode('-', $period);
        $count = 0;
        foreach (Employee::where('status', 'active')->get() as $e) {
            if (Payroll::where(['employee_id' => $e->id, 'period' => $period, 'status' => 'paid'])->exists()) continue;
            $daily = $e->basic_salary / self::WORK_DAYS;
            $otHours = (float) Attendance::where('employee_id', $e->id)->whereYear('date', $y)->whereMonth('date', $m)
                ->where('hours', '>', 8)->selectRaw('COALESCE(SUM(hours - 8), 0) as h')->value('h');
            $overtime = round($otHours * ($daily / 8) * self::OVERTIME_MULTIPLIER, 2);
            $unpaidDays = LeaveRequest::where('employee_id', $e->id)->where('status', 'approved')
                ->whereYear('start_date', $y)->whereMonth('start_date', $m)
                ->whereHas('leaveType', fn ($q) => $q->where('paid', false))->sum('days');
            $gross = $e->basic_salary + $e->allowances + $overtime;
            $deductions = round($unpaidDays * $daily + $e->basic_salary * self::TAX_RATE, 2);
            Payroll::updateOrCreate(['employee_id' => $e->id, 'period' => $period], [
                'basic' => $e->basic_salary, 'allowances' => $e->allowances, 'overtime_pay' => $overtime,
                'deductions' => $deductions, 'net_pay' => round($gross - $deductions, 2), 'status' => 'draft',
            ]);
            $count++;
        }
        return ['generated' => $count, 'period' => $period];
    }

    public function pay($id)
    {
        $p = Payroll::findOrFail($id);
        $p->update(['status' => 'paid']);
        return $p->load('employee');
    }
}
