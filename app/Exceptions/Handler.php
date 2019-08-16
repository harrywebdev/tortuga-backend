<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if (app()->bound('sentry') && $this->shouldReport($exception)) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->wantsJson()) {
            $status = 500;

            if ($this->isHttpException($exception)) {
                $status = $exception->getStatusCode();
            }

            return response()->json((object)['errors' => [(object)[
                'status' => $status,
                'source' => (object)['pointer' => '/'],
                'title'  => 'Internal Server Error',
                'detail' => $exception->getMessage(),
            ],]], $status);
        }

        if ($request->isMethod('post') && $exception instanceof MethodNotAllowedHttpException) {
            return response()->json((object)['errors' => [(object)[
                'status' => 404,
                'source' => (object)['pointer' => '/'],
                'title'  => 'Not Found',
                'detail' => 'Not Found',
            ],]], 404);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Exception $e
     * @return string
     */
    protected function renderExceptionWithWhoops(Exception $e)
    {
        return $this->renderExceptionWithSymfony($e, config('app.debug'));
    }
}
