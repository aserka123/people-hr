<?php
namespace App\Http\Controllers;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends CrudController
{
    protected string $model = Attendance::class;
    protected array $with = ['employee'];
    protected array $search = ['status', 'date'];
    protected bool $ownedByEmployee = true;

    protected function rules(?int $id = null): array
    {
        return [
            'employee_id' => 'required|exists:employees,id', 'date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i', 'clock_out' => 'nullable|date_format:H:i',
            'hours' => 'nullable|numeric|between:0,24', 'status' => 'required|in:present,late,absent',
        ];
    }

    /** Self-service: first call clocks in, second call clocks out. */
    public function clock(Request $r)
    {
        $emp = $r->user()->employee;
        abort_unless($emp, 422, 'Your account is not linked to an employee profile.');
        $a = Attendance::firstOrNew(['employee_id' => $emp->id, 'date' => today()->toDateString()]);
        if (!$a->exists) {
            $a->clock_in = now()->format('H:i:s');
            $a->status = now()->format('H:i') > '09:15' ? 'late' : 'present'; // 09:15 grace period
        } elseif (!$a->clock_out) {
            $a->clock_out = now()->format('H:i:s');
            $a->hours = round(Carbon::parse($a->clock_in)->diffInMinutes(now()) / 60, 2);
        } else {
            abort(422, 'You have already clocked out today.');
        }
        $a->save();
        return $a->load('employee');
    }
}
