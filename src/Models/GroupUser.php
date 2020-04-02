<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupUser extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'group_user';

    /**
     * @return mixed
     */
    public function group ()
    {
        return $this -> belongsTo ( Group::class, 'group_id' );
    }
}
