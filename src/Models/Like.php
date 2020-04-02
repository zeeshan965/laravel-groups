<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Like extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [ 'user_id', 'unique_id', 'likeable_id', 'likeable_type' ];
}
