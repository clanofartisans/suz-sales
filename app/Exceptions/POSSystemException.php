<?php

namespace App\Exceptions;

use Exception;

class POSSystemException extends SalesManagerException
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $info = ['title' => 'Point of Sale System Exception'];

        return $this->response($info);
    }
}
