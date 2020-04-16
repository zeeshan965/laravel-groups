<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GroupAttachment extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'group_attachment';

    /**
     * @var array
     */
    protected $fillable = [ 'attachment_url', 'attachment_type', 'attachment_id' ];

    /**
     * Get the owning attachment model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function attachment ()
    {
        return $this -> morphTo ();
    }
}
