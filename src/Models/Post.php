<?php

namespace Psycho\Groups\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * Adds a comment.
     *
     * @param $data
     * @return array
     */
    public static function add_post ( $data )
    {
        try {
            $self = self ::create ( self ::prepare_data ( $data, true ) );
            $group = Group ::find ( $data[ 'group_id' ] );
            $attach = $group -> attachPost ( $self -> id );
            dd ( $self -> toArray (), $attach );
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
            $self -> update ( $data );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'success', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $data
     * @param bool $create
     * @return mixed
     */
    private static function prepare_data ( $data, $create = false )
    {
        //if ( isset( $data[ 'postMedia' ] ) ) $array[ 'image' ] = self ::save_to_s3 ( $data[ 'postMedia' ] );
        $array[ 'title' ] = $data[ 'postTitle' ];
        $array[ 'body' ] = $data[ 'postBody' ];
        $array[ 'type' ] = $data[ 'postStatus' ] === 'on' ? 1 : 0;
        $array[ 'user_id' ] = Auth ::user () -> id;

        return $array;
    }

    /**
     * @param $file
     * @return string
     */
    private static function save_to_s3 ( $file )
    {
        $ext = "." . $file -> getClientOriginalExtension ();
        $name = time () . generateRandomString () . $ext;
        $filePath = 'groups/posts/' . getCompanyUniqueId ( Auth ::user () ) . '/' . $name;
        Storage ::disk ( 's3' ) -> put ( $filePath, file_get_contents ( $file ) );
        return $filePath;

    }
}
