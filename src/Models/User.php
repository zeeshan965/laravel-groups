<?php

namespace Psycho\Groups\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Psycho\Groups\Traits\GroupHelpers;

class User extends Authenticatable
{
    use GroupHelpers;

    /**
     * @var array
     */
    protected $appends = ['full_name'];

    /**
     * Get the user's first name.
     *
     * @param string $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the user's last name.
     *
     * @param string $value
     * @return string
     */
    public function getLastNameAttribute($value)
    {
        return ucfirst($value);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
