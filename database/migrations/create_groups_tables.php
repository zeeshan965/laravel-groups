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
        Schema::dropIfExists('groups');
        Schema ::create ( 'groups', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ) {
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'user_id' );
                $table -> unsignedBigInteger ( 'conversation_id' ) -> nullable ();
            }
            else {
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'user_id' );
                $table -> unsignedInteger ( 'conversation_id' ) -> nullable ();
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

        Schema::dropIfExists('group_user');
        Schema ::create ( 'group_user', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'user_id' );
                $table -> unsignedBigInteger ( 'group_id' );
            }else{
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'user_id' );
                $table -> unsignedInteger ( 'group_id' );
            }
            $table -> tinyInteger ( 'is_blocked' ) ->default ( 0 );
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> tinyInteger ( 'is_admin' ) ->default ( '0' ) -> comment ( '1=Admin;0=Not Admin' );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema::dropIfExists('posts');
        Schema ::create ( 'posts', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'user_id' ) ;
            }else{
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'user_id' );
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> string ( 'title' );
            $table -> text ( 'body' );
            $table -> string ( 'type' );
            $table -> text ( 'extra_info' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema::dropIfExists('groups_comments');
        Schema ::create ( 'groups_comments', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'user_id' );
                $table -> unsignedBigInteger ( 'post_id' );
            }else{
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'user_id' );
                $table -> unsignedInteger ( 'post_id' );
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> text ( 'user_ip' ) -> nullable ();
            $table -> bigInteger ( 'parent_id' ) -> nullable ();
            $table -> string ( 'body' );
            $table -> string ( 'type' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema::dropIfExists('comments_attachment');
        Schema ::create ( 'comments_attachment', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'comment_id' );
            }else{
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'comment_id' );
            }
            $table -> text ( 'attachment_url' );
            $table -> enum ( 'attachment_type', [ 'image', 'video' ] );
            $table -> timestamps ();
            $table -> softDeletes ();

        } );

        Schema::dropIfExists('group_post');
        Schema ::create ( 'group_post', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> unsignedBigInteger ( 'group_id' );
                $table -> unsignedBigInteger ( 'post_id' );
            }else{
                $table -> unsignedInteger ( 'group_id' );
                $table -> unsignedInteger ( 'post_id' );
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema::dropIfExists('likes');
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

        Schema::dropIfExists('reports');
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

        Schema::dropIfExists('group_request');
        Schema ::create ( 'group_request', function ( Blueprint $table ) {
            if ( $this -> useBigIncrements ){
                $table -> bigIncrements ( 'id' );
                $table -> unsignedBigInteger ( 'user_id' ) -> index ();
                $table -> unsignedBigInteger ( 'group_id' ) -> index ();
            }else{
                $table -> increments ( 'id' );
                $table -> unsignedInteger ( 'user_id' ) -> index ();
                $table -> unsignedInteger ( 'group_id' ) -> index ();
            }
            $table -> string ( 'unique_id' ) -> nullable ();
            $table -> tinyInteger ( 'is_invite' ) ->default ( 0 ) -> comment ( '1=true;0=false' );
            $table -> timestamps ();
            $table -> softDeletes ();
        } );

        Schema::dropIfExists('group_tags');
        Schema ::create ( 'group_tags', function ( Blueprint $table ) {
            $table -> increments ( 'id' );
            $table -> string ( 'name' );
            if ( $this -> useBigIncrements ){
                $table -> unsignedBigInteger ( 'group_id' );
                $table -> unsignedBigInteger ( 'created_by' );
            }else{
                $table -> unsignedInteger ( 'group_id' );
                $table -> unsignedInteger ( 'created_by' );
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
