<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table = 'c_team'; // 默认 flights
    protected $primaryKey = 't_id'; // 默认 id
}
