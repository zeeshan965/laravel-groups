<?php

namespace Psycho\Groups\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Psycho\Groups\Groups;
use Psycho\Groups\Traits\Likes;
use Psycho\Groups\Traits\Reporting;

class Post extends Model
{
    use Likes, Reporting, SoftDeletes;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName ()
    {
        return 'id';
    }

    /**
     * @var array
     */
    protected $fillable = [ 'title', 'user_id', 'body', 'type', 'extra_info', 'unique_id' ];

    /**
     * Boot method for Post
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
    public function comments ()
    {
        return $this -> hasMany ( Comment::class, 'post_id' ) -> with ( 'commentator' ) -> where ( 'parent_id', null );
    }

    /**
     * @return mixed
     */
    public function allComments ()
    {
        return $this -> hasMany ( Comment::class, 'post_id' ) -> with ( 'commentator' );
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
     * @return mixed
     */
    public function owner ()
    {
        return $this -> belongsTo ( User::class, 'user_id' );
    }

    /**
     * Get the post's media.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function media ()
    {
        return $this -> morphOne ( GroupAttachment::class, 'attachment' );
    }

    /**
     * Adds a comment.
     *
     * @param $data
     * @return array
     */
    public static function add_post ( $data )
    {
        try {
            $self = self ::create ( self ::prepare_data ( $data, false ) );
            if ( isset( $data[ 'postMedia' ] ) ) self ::attach_media ( $data[ 'postMedia' ], $self );
            $group = Group ::find ( $data[ 'group_id' ] );
            $group -> attachPost ( $self -> id );
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
    public static function update_post ( $data, $id )
    {
        try {
            $self = self ::find ( $id );
            $self -> update ( self ::prepare_data ( $data ) );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'success', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $data
     * @param bool $update
     * @return mixed
     */
    private static function prepare_data ( $data, $update = true )
    {
        $array[ 'title' ] = $data[ 'postTitle' ];
        $array[ 'body' ] = $data[ 'postBody' ];
        $array[ 'type' ] = $data[ 'postStatus' ] === 'on' ? 1 : 0;
        if ( $update === false ) $array[ 'user_id' ] = Auth ::user () -> id;

        return $array;
    }

    /**
     * @param $file
     * @param $post
     */
    private static function attach_media ( $file, $post )
    {
        $url = Groups ::save_to_s3 ( $file, 'getCompanyUniqueId' );
        $attachment = new GroupAttachment( [ 'attachment_url' => $url ] );
        $post -> media () -> save ( $attachment );
    }

}
