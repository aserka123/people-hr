<?php
namespace App\Http\Controllers;
use App\Models\{LeaveRequest, LeaveType};
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveRequestController extends CrudController
{
    protected string $model = LeaveRequest::class;
    protected array $with = ['employee', 'leaveType'];
    protected array $search = ['status'];
    protected bool $ownedByEmployee = true;

    protected function rules(?int $id = null): array
    {
        return [
            'employee_id' => 'required|exists:employees,id', 'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date', 'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string', 'status' => 'required|in:pending,approved,rejected',
        ];
    }

    /** Any signed-in employee can file for themselves; the yearly balance is enforced. */
    public function store(Request $r)
    {
        $emp = $r->user()->employee;
        $d = $r->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id', 'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date', 'reason' => 'nullable|string',
        ]);
        $employeeId = $r->user()->isHr() ? ($d['employee_id'] ?? $emp?->id) : $emp?->id;
        abort_unless($employeeId, 422, 'Your account is not linked to an employee profile.');
        $days = Carbon::parse($d['start_date'])->diffInDays(Carbon::parse($d['end_date'])) + 1;
        $type = LeaveType::findOrFail($d['leave_type_id']);
        $used = LeaveRequest::where(['employee_id' => $employeeId, 'leave_type_id' => $type->id, 'status' => 'approved'])
            ->whereYear('start_date', now()->year)->sum('days');
        abort_if($used + $days > $type->days_per_year, 422,
            "Not enough {$type->name} balance (" . ($type->days_per_year - $used) . ' days left).');
        return LeaveRequest::create([
            'employee_id' => $employeeId, 'leave_type_id' => $type->id, 'start_date' => $d['start_date'],
            'end_date' => $d['end_date'], 'days' => $days, 'reason' => $d['reason'] ?? null,
        ])->load($this->with);
    }

    public function decide(Request $r, $id)
    {
        $r->validate(['status' => 'required|in:approved,rejected']);
        $l = LeaveRequest::findOrFail($id);
        $l->update(['status' => $r->status, 'decided_by' => $r->user()->id]);
        return $l->load($this->with);
    }

    public function update(Request $r, $id)
    {
        $l = LeaveRequest::findOrFail($id);
        $d = $r->validate($this->rules($l->id));
        $d['days'] = Carbon::parse($d['start_date'])->diffInDays(Carbon::parse($d['end_date'])) + 1;
        $l->update($d);
        return $l->load($this->with);
    }
}
