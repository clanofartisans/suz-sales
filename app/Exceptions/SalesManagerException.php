<?php

namespace App\Exceptions;

use Exception;

abstract class SalesManagerException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        $info = [];
        return $this->response($info);
    }

    /**
     * Render the exception into an HTTP response.
     * Use info from render() or some defaults.
     *
     * @param array $info
     * @return \Illuminate\Http\Response
     */
    protected function response(array $info)
    {
        $data['code'] = isset($info['code']) ? $info['code'] : 500;
        $data['title'] = isset($info['title']) ? $info['title'] : substr(get_class($this), strrpos(get_class($this), '\\')+1);
        $data['message'] = isset($info['message']) ? $info['message'] : $this->getMessage();

        return response()->view('errors.custom', $data, $data['code']);
    }
}
