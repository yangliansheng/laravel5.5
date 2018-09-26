<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{
    protected $table = 'c_job_grade'; // 默认 flights
    protected $primaryKey = 'j_g_id'; // 默认 id
}
