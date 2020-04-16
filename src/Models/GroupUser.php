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
     * Boot method for GroupUser
     * On create add unique_id
     */
    public static function boot ()
    {
        parent ::boot ();
        self ::creating ( function ( $model ) {
            $model -> unique_id = md5 ( uniqid ( rand (), true ) );
        } );
    }
    
    /**
     * @return mixed
     */
    public function group ()
    {
        return $this -> belongsTo ( Group::class, 'group_id' );
    }
}
