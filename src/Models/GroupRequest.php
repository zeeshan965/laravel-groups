<?php

namespace Psycho\Groups\Models;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupRequest extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'group_request';

    /**
     * @var array
     */
    protected $fillable = [ 'user_id', 'group_id', 'unique_id', 'is_invite' ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName ()
    {
        return 'unique_id';
    }

    /**
     * @return BelongsTo
     */
    public function group ()
    {
        return $this -> belongsTo ( Group::class, 'group_id' );
    }

    /**
     * @return BelongsTo
     */
    public function sender ()
    {
        return $this -> belongsTo ( User::class, 'user_id' );
    }

    public function accept_request ( $group, $self )
    {
        try {
            $group -> acceptRequest ( $self -> user_id );
            $user = $self -> toArray ();
            unset( $user[ 'sender' ] );
            $user[ 'role' ] = getRoleId ( $self -> sender -> role_id, $self -> sender -> status );

            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record saved successfully!', 'data' => $user ];
        } catch ( \Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'success', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

}
