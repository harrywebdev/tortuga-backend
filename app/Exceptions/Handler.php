<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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
            $title  = 'Internal Server Error';

            if ($this->isHttpException($exception)) {
                $status = $exception->getStatusCode();
            }

            if ($exception instanceof AuthenticationException) {
                $status = 401;
                $title  = $exception->getMessage();
            }

            if ($exception instanceof AuthorizationException) {
                $status = 403;
                $title  = $exception->getMessage();
            }

            return response()->json((object)['errors' => [(object)[
                'status' => $status,
                'source' => (object)['pointer' => '/'],
                'title'  => $title,
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

    /**
     * @param Exception $e
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        // ignore exception caught by Sentry but gracefully recovered from by Laravel
        // https://github.com/laravel/framework/issues/28920
        // https://github.com/getsentry/sentry-laravel/issues/254
        if (strpos($e->getMessage(), 'STMT_PREPARE packet') !== false) {
            return false;
        }

        return parent::shouldReport($e);
    }
}
