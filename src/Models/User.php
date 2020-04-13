<?php

namespace Psycho\Groups\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Psycho\Groups\Traits\GroupHelpers;

class User extends Authenticatable
{
    use GroupHelpers;

    /**
     * @var array
     */
    protected $appends = [ 'full_name' ];

    /**
     * Get the user's first name.
     *
     * @param string $value
     * @return string
     */
    public function getFirstNameAttribute ( $value )
    {
        return ucfirst ( $value );
    }

    /**
     * Get the user's last name.
     *
     * @param string $value
     * @return string
     */
    public function getLastNameAttribute ( $value )
    {
        return ucfirst ( $value );
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute ()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's profile pic.
     *
     * @return string
     */
    public function getProfilePicAttribute ( $value )
    {
        if ( $value == null ) return $value;
        return Storage ::disk ( 's3' ) -> url ( $value );

    }
}
