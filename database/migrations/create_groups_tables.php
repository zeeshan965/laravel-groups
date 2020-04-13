<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTables extends Migration
{
    protected $useBigIncrements;

    public function __construct ()
    {
        $this -> useBigIncrements = app () ::VERSION >= 5.8;
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {

        Schema ::create ( 'groups', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ) {
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'user_id' ) -> unsignedBigIntegers ();
                $table -> integer ( 'conversation_id' ) -> unsignedBigIntegers () -> nullable ();
            }
            else {
                $table -> increments ( 'id' );
                $table -> integer ( 'user_id' ) -> unsigned ();
                $table -> integer ( 'conversation_id' ) -> unsigned () -> nullable ();
            }
            $table -> string ( 'category', 255 );

            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> string ( 'name' );
            $table -> string ( 'description' ) -> nullable ();
            $table -> string ( 'short_description' ) -> nullable ();
            $table -> string ( 'image' ) -> nullable ();
            $table -> string ( 'icon' ) -> nullable ();
            $table -> string ( 'url' ) -> nullable ();
            $table -> boolean ( 'private' ) -> unsigned () ->default ( false );
            $table -> text ( 'extra_info' ) -> nullable ();
            $table -> text ( 'settings' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'group_user', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'user_id' ) -> unsignedBigIntegers ();
                $table -> integer ( 'group_id' ) -> unsignedBigIntegers ();
            }else{
                $table -> increments ( 'id' );
                $table -> integer ( 'user_id' ) -> unsigned ();
                $table -> integer ( 'group_id' ) -> unsigned ();
            }
            $table -> tinyInteger ( 'is_blocked' ) ->default ( 0 );
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> tinyInteger ( 'is_admin' ) ->default ( '0' ) -> comment ( '1=Admin;0=Not Admin' );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'posts', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'user_id' ) -> unsignedBigIntegers ();
            }else{
                $table -> increments ( 'id' );
                $table -> integer ( 'user_id' ) -> unsigned ();
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> string ( 'title' );
            $table -> text ( 'body' );
            $table -> string ( 'type' );
            $table -> text ( 'extra_info' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'groups_comments', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'user_id' ) -> unsignedBigIntegers ();
                $table -> integer ( 'post_id' ) -> unsignedBigIntegers ();
            }else{
                $table -> increments ( 'id' );
                $table -> integer ( 'user_id' ) -> unsigned ();
                $table -> integer ( 'post_id' ) -> unsigned ();
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> text ( 'user_ip' ) -> nullable ();
            $table -> bigInteger ( 'parent_id' ) -> nullable ();
            $table -> string ( 'body' );
            $table -> string ( 'type' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'comments_attachment', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'comment_id' ) -> unsignedBigIntegers ();
            }else{
                $table -> increments ( 'id' );
                $table -> integer ( 'comment_id' ) -> unsignedBigIntegers ();
            }
            $table -> text ( 'attachment_url' );
            $table -> enum ( 'attachment_type', [ 'image', 'video' ] );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'group_post', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> integer ( 'group_id' ) -> unsignedBigIntegers ();
                $table -> integer ( 'post_id' ) -> unsignedBigIntegers ();
            }else{
                $table -> integer ( 'group_id' ) -> unsigned ();
                $table -> integer ( 'post_id' ) -> unsigned ();
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'likes', function ( Blueprint $table ) {
            $table -> integer ( 'user_id' ) -> index ();
            if ( $this -> useBigIncrements ) $table -> integer ( 'likeable_id' ) -> unsignedBigIntegers ();
            else $table -> integer ( 'likeable_id' ) -> unsigned ();
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> string ( 'likeable_type' );
            $table -> primary ( [ 'user_id', 'likeable_id', 'likeable_type' ] );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'reports', function ( Blueprint $table ) {
            $table -> integer ( 'user_id' ) -> index ();
            if ( $this -> useBigIncrements ) $table -> integer ( 'reportable_id' ) -> unsignedBigIntegers ();
            else $table -> integer ( 'reportable_id' ) -> unsigned ();
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> string ( 'reportable_type' );
            $table -> primary ( [ 'user_id', 'reportable_id', 'reportable_type' ] );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'group_request', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> integer ( 'user_id' ) -> unsignedBigIntegers () -> index ();
                $table -> integer ( 'group_id' ) -> unsignedBigIntegers () -> index ();
            }else{
                $table -> increments ( 'id' );
                $table -> integer ( 'user_id' ) -> unsigned () -> index ();
                $table -> integer ( 'group_id' ) -> unsigned () -> index ();
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> tinyInteger ( 'is_invite' ) ->default ( 0 ) -> comment ( '1=true;0=false' );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema ::create ( 'group_tags', function ( Blueprint $table ) {
            $table -> increments ( 'id' );
            $table -> string ( 'name' );
            if ( $this -> useBigIncrements ){
                $table -> unsignedBigInteger ( 'group_id' );
                $table -> integer ( 'created_by' ) -> unsignedBigIntegers ();
            }else{
                $table -> integer ( 'group_id' ) -> unsigned ();
                $table -> integer ( 'created_by' ) -> unsigned ();
                $table -> integer ( 'group_id' ) -> unsigned ();
            }
            $table -> timestamps ();
        } );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down ()
    {
        Schema ::drop ( 'groups' );
        Schema ::drop ( 'group_user' );
        Schema ::drop ( 'posts' );
        Schema ::drop ( 'groups_comments' );
        Schema ::drop ( 'comments_attachment' );
        Schema ::drop ( 'group_post' );
        Schema ::drop ( 'likes' );
        Schema ::drop ( 'reports' );
        Schema ::drop ( 'group_request' );
        Schema ::drop ( 'group_tags' );
    }
}
