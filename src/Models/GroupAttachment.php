<?php

namespace Psycho\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
    public function attachmentable ()
    {
        return $this -> morphTo ();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getAttachmentUrlAttribute ( $value )
    {
        if ( $value == null ) return $value;
        return Storage ::disk ( 's3' ) -> url ( $value );
    }
}
