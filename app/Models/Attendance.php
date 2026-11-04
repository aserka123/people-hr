<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Attendance extends Model
{
    protected $guarded = [];
    protected $casts = ['date' => 'date:Y-m-d'];
    public function employee() { return $this->belongsTo(Employee::class); }
}
