<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = ['user_id'];
}
