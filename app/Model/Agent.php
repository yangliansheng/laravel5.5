<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $table = 'c_agent'; // 默认 flights
    protected $primaryKey = 'ag_id'; // 默认 id
    protected $hidden = [];
}
