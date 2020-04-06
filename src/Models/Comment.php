<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Psycho\Groups\Traits\Likes;
use Psycho\Groups\Traits\Reporting;

class Comment extends Model
{
    use Likes, Reporting, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [ 'post_id', 'user_id', 'body', 'unique_id', 'type' ];

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
     * @param array $comment
     *
     * @return Comment
     */
    public function add ( $comment )
    {
        return $this -> create ( $comment );
    }
}
