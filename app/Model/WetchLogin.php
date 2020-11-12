<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class WetchLogin extends Model
{
    protected $table = 'wetch_login';

    protected  $primaryKey = 'wetch_id';

    public $timestamps = false;
}
