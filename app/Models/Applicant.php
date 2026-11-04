<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Applicant extends Model
{
    protected $guarded = [];
    public function jobOpening() { return $this->belongsTo(JobOpening::class); }
}
