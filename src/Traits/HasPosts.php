<?php

namespace Psycho\Groups\Traits;

use Psycho\Groups\Models\Post;

trait HasPosts
{
    /**
     * @return mixed
     */
    public function posts ()
    {
        return $this -> hasMany ( Post::class, 'user_id' );
    }
}
