<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Psycho\Groups\Traits\Likes;
use Psycho\Groups\Traits\Reporting;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * @return mixed
     */
    public function comments ()
    {
        return $this -> hasMany ( Comment::class, 'post_id' ) -> with ( 'commentator' ) -> where ( 'parent_id', null );
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
     * Creates a post.
     *
     * @param array $data
     *
     * @return Post
     */
    public function make ( $data )
    {
        return $this -> create ( $data );
    }

    /**
     * Updates Post.
     *
     * @param int $postId
     * @param array $data
     *
     * @return Post
     */
    public function updatePost ( $postId, $data )
    {
        $this -> where ( 'id', $postId ) -> update ( $data );
        return $this;
    }
}
