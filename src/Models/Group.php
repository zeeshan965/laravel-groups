<?php

namespace Psycho\Groups\Models;

use App\Models\GroupCategory;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Psycho\Groups\Groups;

class Group extends Model
{
    use SoftDeletes;

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName ()
    {
        return 'unique_id';
    }

    /**
     * @var array
     */
    protected $fillable = [
        'unique_id', 'name', 'user_id', 'description', 'short_description', 'image', 'icon', 'private', 'extra_info', 'settings', 'conversation_id'
    ];

    /**
     * @var array
     */
    protected $appends = [ 'have_access', 'has_admin_access', 'has_requested' ];

    /**
     * Boot method for Group
     * On create add unique_id
     */
    public static function boot ()
    {
        parent ::boot ();
        self ::creating ( function ( $model ) {
            $model -> unique_id = md5 ( uniqid ( rand (), true ) );
        } );
    }

    /**
     * @var array
     */
    protected $invited_users = [];

    /**
     * @param $value
     * @return mixed
     */
    public function getImageAttribute ( $value )
    {
        if ( $value == null ) return $value;
        return Storage ::disk ( 's3' ) -> url ( $value );
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getIconAttribute ( $value )
    {
        if ( $value == null ) return $value;
        return Storage ::disk ( 's3' ) -> url ( $value );
    }

    /**
     * @return BelongsTo
     */
    public function user ()
    {
        return $this -> belongsTo ( Groups ::userModel (), 'user_id', 'id' );
    }

    /**
     * @return BelongsToMany
     */
    public function users ()
    {
        return $this -> belongsToMany ( Groups ::userModel (), 'group_user' ) -> withTimestamps ();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function onlyAdminUser ()
    {
        return $this -> hasOne ( GroupUser::class, 'group_id', 'id' ) -> where ( 'user_id', Auth ::id () );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function onlyGroupRequest ()
    {
        return $this -> hasOne ( GroupRequest::class, 'group_id', 'id' ) -> where ( 'user_id', Auth ::id () );
    }

    /**
     * @return mixed|null
     */
    public function getHaveAccessAttribute ()
    {
        return $this -> user_id === Auth ::id () ? true : false;
    }

    /**
     * @return mixed|null
     */
    public function getHasAdminAccessAttribute ()
    {
        if ( $this -> onlyAdminUser === null ) return false;
        return $this -> onlyAdminUser -> is_admin == 1 ? true : false;
    }

    /**
     * @return mixed|null
     */
    public function getHasRequestedAttribute ()
    {
        if ( $this -> onlyGroupRequest === null ) return false;
        return $this -> onlyGroupRequest -> is_invite == 1 ? true : false;
    }

    /**
     * @return BelongsToMany
     */
    public function posts ()
    {
        return $this -> belongsToMany ( Post::class, 'group_post' ) -> withTimestamps () -> orderBy ( 'id', 'desc' );
    }

    /**
     * @return HasMany
     */
    public function requests ()
    {
        return $this -> hasMany ( GroupRequest::class, 'group_id' ) -> with ( 'sender' );
    }

    /**
     * Creates a group.
     *
     * @param int $userId
     * @param array $data
     *
     * @return Group
     */
    public function make ( $userId, $data )
    {
        $data[ 'user_id' ] = $userId;
        return $this -> create ( $data ) -> addMembers ( $userId );
    }

    /**
     * Creates a group join request.
     *
     * @param $users
     * @param bool $is_invite
     * @param bool $settings
     * @return array|void
     */
    public function request ( $users, $is_invite = false, $settings = true )
    {
        if ( ! is_array ( $users ) ) return;
        if ( $settings === true ) GroupRequest ::where ( 'group_id', $this -> id ) -> forceDelete ();
        foreach ( $users as $item ) {
            $request = new GroupRequest( [
                'user_id' => $item[ 'id' ],
                'is_invite' => $is_invite === true ? 1 : 0
            ] );
            $this -> requests () -> save ( $request );

            $user = $this -> fetch_user_role ( $item, $request, 'getRoleId' );
            array_push ( $this -> invited_users, $user );
        }
    }

    /**
     * @param $user_id
     */
    public function deleteRequest ( $user_id )
    {
        $this -> requests () -> where ( 'user_id', $user_id ) -> forceDelete ();
    }

    /**
     * Accepts a group join request.
     *
     * @param int $userId
     *
     * @return Group
     */
    public function acceptRequest ( $userId )
    {
        $this -> addMembers ( $userId );
        $this -> deleteRequest ( $userId );
        return $this;
    }

    /**
     * Decline a group join request.
     *
     * @param int $userId
     *
     * @return Group
     */
    public function declineRequest ( $userId )
    {
        $this -> deleteRequest ( $userId );
        return $this;
    }

    /**
     * Add members / join group.
     *
     * @param mixed $members integer user_id or an array of user ids
     *
     * @return Group
     */
    public function addMembers ( $members )
    {
        if ( is_array ( $members ) ) {
            $this -> users () -> sync ( $members );
        } else {
            $group_user = new GroupUser();
            $group_user -> user_id = $members;
            $group_user -> group_id = $this -> id;
            $group_user -> save ();
        }

        return $this;
    }

    /**
     * @return BelongsToMany
     */
    public function categoryData ()
    {
        return $this -> belongsTo ( GroupCategory::class, 'category','id' );
    }

    /**
     * Removes user from group.
     *
     * @param mixed $members this can be user_id or an array of user ids
     *
     * @return Group
     */
    public function leave ( $members )
    {
        if ( is_array ( $members ) )
            foreach ( $members as $id ) $this -> users () -> detach ( $id );
        else $this -> users () -> detach ( $members );

        return $this;
    }

    /**
     * Attach a post to a group.
     *
     * @param int $postId
     *
     * @return Group
     */
    public function attachPost ( $postId )
    {
        if ( is_array ( $postId ) ) $this -> posts () -> sync ( $postId );
        else $this -> posts () -> attach ( $postId );

        return $this;
    }

    /**
     * @param $postId
     * @return $this
     */
    public function detachPost ( $postId )
    {
        $this -> posts () -> detach ( $postId );
        return $this;
    }

    /**
     * @param $request
     * @return array
     */
    public static function insert_group ( $request, $user_id )
    {
        try {
            $user_members = $request -> has ( 'user_members' ) ? explode ( ",", $request -> user_members ) : [];
            $users = User ::whereIn ( 'id', $user_members ) -> get ();
            $group = self ::create ( self ::prepare_data ( $request, $user_id, true ) );
            $group -> request ( $users -> toArray (), true );

            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record saved successfully!', 'data' => $group ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }

    }

    /**
     * @param $request
     * @param $user_id
     * @param $id
     * @return array
     */
    public static function update_group ( $request, $user_id, $id )
    {
        try {
            $user_members = $request -> has ( 'user_members' ) ? explode ( ",", $request -> user_members ) : [];
            $users = User ::whereIn ( 'id', $user_members ) -> get ();
            $group = self ::find ( $id ) -> update ( self ::prepare_data ( $request, $user_id ) );
            self ::find ( $id ) -> request ( $users -> toArray (), true );

            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record update successfully!', 'data' => $group ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $request
     * @param $user_id
     * @param bool $create
     * @return mixed
     */
    private static function prepare_data ( $request, $user_id, $create = false )
    {
        if ( $request -> has ( 'group_cover' ) === true ) $array[ 'image' ] = Groups ::save_to_s3 ( $request -> group_cover, 'getCompanyUniqueId' );
        if ( $request -> has ( 'group_icon' ) === true ) $array[ 'icon' ] = Groups ::save_to_s3 ( $request -> group_icon, 'getCompanyUniqueId' );
        if ( $request -> has ( 'cover_image_remove' ) === true && $request -> cover_image_remove == 1 ) $array[ 'image' ] = "";
        if ( $request -> has ( 'icon_image_remove' ) === true && $request -> icon_image_remove == 1 ) $array[ 'icon' ] = "";

        $array[ 'name' ] = $request -> group_name;
        $array[ 'description' ] = $request -> description;
        $array[ 'private' ] = $request -> privacy;
        $array[ 'user_id' ] = $user_id;
        return $array;
    }

    /**
     * @param $item
     * @param $request
     * @param $callback
     * @return array
     */
    private function fetch_user_role ( $item, $request, $callback )
    {
        if ( ! function_exists ( $callback ) ) return null;

        //$role will return integer value
        $role = $callback ( $item[ 'role_id' ], $item[ 'status' ] );
        $user = $request -> toArray ();
        $user[ 'role' ] = $role;
        return $user;
    }

    /**
     * @param $data
     * @return array
     */
    public function invite_users ( $data )
    {
        try {
            $user_members = isset( $data[ 'user_members' ] ) ? explode ( ",", $data[ 'user_members' ] ) : [];
            $users = User ::whereIn ( 'id', $user_members ) -> get ();
            $this -> request ( $users -> toArray (), true, false );
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record saved successfully!', 'data' => $this -> invited_users ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $request
     * @return array
     */
    public function update_cover ( $request )
    {
        try {
            $old = isset( $this -> image ) && $this -> image !== null ? explode ( "groups", $this -> image )[ 1 ] : '';
            Storage ::disk ( 's3' ) -> delete ( "groups" . $old );
            $this -> image = Groups ::save_to_s3 ( $request -> cover, 'getCompanyUniqueId' );
            if ( $request -> has ( 'position' ) && $request -> position !== null ) $this -> cover_position = $request -> position;
            $this -> save ();
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record saved successfully!', 'data' => $this -> image ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }
    }

    /**
     * @param $id
     * @return array
     */
    public static function recursiveDelete ( $id )
    {
        try {
            $self = self ::find ( $id );
            self ::removePosts ( $self -> posts () -> get () );
            $self -> posts () -> detach ();
            $status = $self -> delete ();
            return [ 'status' => 'success', 'status_code' => 200, 'messages' => 'Record deleted successfully!', 'data' => $status ];
        } catch ( Exception $e ) {
            $message = $e -> getLine () . "Something went wrong, Please contact support!" . $e -> getMessage ();
            return [ 'status' => 'error', 'status_code' => 500, 'messages' => $message, 'data' => null ];
        }

    }

    /**
     * @param $posts
     */
    private static function removePosts ( $posts )
    {
        foreach ( $posts as $post ) {
            $post :: recursiveDelete ( $post -> id );
        }
    }
}
