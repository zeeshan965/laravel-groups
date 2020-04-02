<?php

namespace Psycho\Groups\Traits;

use Psycho\Groups\Models\Like;

trait Likes
{
    /**
     * @param $user_id
     * @return mixed
     */
    public function like ( $user_id )
    {
        $like = new Like( [ 'user_id' => $user_id ] );
        return $this -> likes () -> save ( $like );
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function unlike ( $user_id )
    {
        return $this -> likes () -> where ( 'user_id', $user_id ) -> delete ();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function toggleLike ( $user_id )
    {
        if ( $this -> isLiked ( $user_id ) ) return $this -> unlike ( $user_id );
        $this -> like ( $user_id );
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isLiked ( $user_id )
    {
        return (bool) $this -> likes () -> where ( 'user_id', $user_id ) -> count ();
    }

    /**
     * @return mixed
     */
    public function getLikesCountAttribute ()
    {
        return $this -> likes () -> count ();
    }
}
