<?php

namespace Psycho\Groups\Traits;

use Psycho\Groups\Models\Report;

trait Reporting
{
    /**
     * @param $user_id
     * @return mixed
     */
    public function report ( $user_id )
    {
        $report = new Report( [ 'user_id' => $user_id ] );

        return $this -> reports () -> save ( $report );
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function removeReport ( $user_id )
    {
        return $this -> reports () -> where ( 'user_id', $user_id ) -> delete ();
    }

    /**
     * @param $user_id
     * @return mixed
     */
    public function toggleReport ( $user_id )
    {
        if ( $this -> isReported ( $user_id ) ) {
            return $this -> removeReport ( $user_id );
        }

        $this -> report ( $user_id );
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isReported ( $user_id )
    {
        return (bool) $this -> reports () -> where ( 'user_id', $user_id ) -> count ();
    }

    /**
     * @return mixed
     */
    public function getReportsCountAttribute ()
    {
        return $this -> reports () -> count ();
    }

}
