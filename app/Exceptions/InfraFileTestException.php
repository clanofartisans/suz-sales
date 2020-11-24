<?php

namespace App\Exceptions;

use Exception;

class InfraFileTestException extends SalesManagerException
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $info = ['title' => 'INFRA File Exception'];

        return $this->response($info);
    }
}
