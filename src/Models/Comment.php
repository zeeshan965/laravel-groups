<?php

namespace Psycho\Groups\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Psycho\Groups\Traits\Likes;
use Psycho\Groups\Traits\Reporting;

class Comment extends Model
{
    use Likes, Reporting, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'groups_comments';

    /**
     * @var array
     */
    protected $fillable = [ 'post_id', 'user_id', 'body', 'unique_id', 'type', 'parent_id', 'user_ip' ];

    /**
     * @return mixed
     */
    public function commentator ()
    {
        return $this -> belongsTo ( User::class, 'user_id' );
    }

    /**
     * @return mixed
     */
    public function post ()
    {
        return $this -> belongsTo ( Post::class, 'post_id' );
    }

    /**
     * @return mixed
     */
    public function likes ()
    {
        return $this -> morphMany ( Like::class, 'likeable' );
    }

    /**
     * @return mixed
     */
    public function reports ()
    {
        return $this -> morphMany ( Report::class, 'reportable' );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies ()
    {
        return $this -> hasMany ( Comment::class, 'parent_id', 'id' );
    }

    /**
     * Adds a comment.
     *
     * @param $data
     * @return array
     */
    public static function add_comment ( $data )
    {
        try {
            $data[ 'unique_id' ] = md5 ( uniqid ( rand (), true ) );
            $data[ 'ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
            $self = self ::create ( $data );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'success', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * Update a comment.
     *
     * @param $data
     * @param $id
     * @return array
     */
    public static function update_comment ( $data, $id )
    {
        try {
            $self = self ::find ( $id );
            $self -> update ( $data );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'success', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }
}
