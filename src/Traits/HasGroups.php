<?php

namespace Psycho\Groups\Traits;

use Psycho\Groups\Models\Group;

trait HasGroups
{
    /**
     * @return mixed
     */
    public function groups ()
    {
        return $this -> belongsToMany ( Group::class, 'group_user' );
    }
}
