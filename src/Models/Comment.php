<?php

namespace Psycho\Groups\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Psycho\Groups\Groups;
use Psycho\Groups\Traits\Likes;
use Psycho\Groups\Traits\Reporting;

class Comment extends Model
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
     * @var string
     */
    protected $table = 'groups_comments';

    /**
     * @var array
     */
    protected $fillable = [ 'post_id', 'user_id', 'body', 'unique_id', 'type', 'parent_id', 'user_ip' ];

    /**
     * @var array
     */
    protected $appends = [ 'attachment_url', 'attachment_type' ];

    /**
     * Boot method for Comment
     * On create add unique_id
     */
    public static function boot ()
    {
        parent ::boot ();
        self ::creating ( function ( $model ) {
            $model -> unique_id = md5 ( uniqid ( rand (), true ) );
        } );
        self ::retrieved ( function ( $model ) {
            $model -> setRelation ( 'media', GroupAttachment ::find ( $model -> media -> id ?? null ) );
        } );
    }

    /**
     * @return |null
     */
    public function getAttachmentUrlAttribute ()
    {
        if ( $this -> media == null ) return null;
        return $this -> media -> attachment_url;
    }

    /**
     * @return mixed|null
     */
    public function getAttachmentTypeAttribute ()
    {
        if ( $this -> media == null ) return null;
        $ext = pathinfo ( $this -> media -> attachment_url, PATHINFO_EXTENSION );
        return $ext;
    }

    /**
     * @var array
     */
    protected static $ids = [];

    /**
     * @param $replies
     */
    public static function recursiveDelete ( $id )
    {
        try {
            $self = self ::find ( $id );
            $self -> detach_media ();
            array_push ( self ::$ids, $self -> id );
            if ( $self -> replies -> count () > 0 ) self ::removeChildren ( $self -> replies );

            $status = self ::destroy ( self ::$ids ) === count ( self ::$ids ) ? true : false;
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record deleted successfully!', 'data' => $status ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }

    }

    /**
     * @param $replies
     */
    private static function removeChildren ( $replies )
    {
        foreach ( $replies as $reply ) {
            array_push ( self ::$ids, $reply -> id );
            $reply -> detach_media ();
            if ( $reply -> replies -> count () > 0 ) self ::removeChildren ( $reply -> replies );
        }
    }

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
     * Get the comment's media.
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
    public static function add_comment ( $data )
    {
        try {
            $data[ 'parent_id' ] = $data[ 'parent_id' ] === 'null' || $data[ 'parent_id' ] === null ? null : $data[ 'parent_id' ];
            $data[ 'user_ip' ] = $_SERVER[ 'REMOTE_ADDR' ];
            $data[ 'user_id' ] = Auth ::user () -> id;
            $self = self ::create ( $data );
            if ( isset( $data[ 'comment_image' ] ) ) self ::attach_media ( $data[ 'comment_image' ], $self );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
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
            if ( isset( $data[ 'comment_image' ] ) ) self ::attach_media ( $data[ 'comment_image' ], $self );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $self ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $file
     * @param $comment
     */
    private static function attach_media ( $file, $comment )
    {
        $url = Groups ::save_to_s3 ( $file, 'getCompanyUniqueId' );
        $data = $comment -> media () -> first ();
        $attachment = $comment -> media () -> updateOrCreate ( [
            'attachment_id' => isset( $data -> attachment_id ) ? $data -> attachment_id : null,
            'attachment_type' => isset( $data -> attachment_type ) ? $data -> attachment_type : null
        ], [ 'attachment_url' => $url ] );
        $comment -> setRelation ( 'media', $attachment );
    }

    /**
     * @return $this
     */
    private function detach_media ()
    {
        $this -> setRelation ( 'media', null );
        return $this -> media () -> delete ();
    }

}
