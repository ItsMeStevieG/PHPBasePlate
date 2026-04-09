<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Exceptions;

use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Logging\Logger;

class ExceptionHandler
{
    public function __construct(
        private readonly Logger $logger,
        private readonly bool $debug = false,
    ) {
    }

    public function handle(\Throwable $e, ?Request $request = null): Response
    {
        $this->logger->error($e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        $statusCode = $e instanceof HttpException ? $e->getStatusCode() : 500;

        if ($request?->wantsJson()) {
            return $this->jsonResponse($e, $statusCode);
        }

        return $this->htmlResponse($e, $statusCode);
    }

    public function register(): void
    {
        set_exception_handler(function (\Throwable $e): void {
            $response = $this->handle($e);
            $response->send();
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }

    private function jsonResponse(\Throwable $e, int $statusCode): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $this->debug ? $e->getMessage() : $this->defaultMessage($statusCode),
            'data' => null,
            'meta' => (object) [],
            'errors' => [],
        ];

        if ($this->debug) {
            $payload['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ];
        }

        return new JsonResponse($payload, $statusCode);
    }

    private function htmlResponse(\Throwable $e, int $statusCode): Response
    {
        if ($this->debug) {
            $body = $this->renderDebugPage($e, $statusCode);
        } else {
            $body = $this->renderProductionPage($statusCode);
        }

        return new Response($body, $statusCode, ['Content-Type' => 'text/html']);
    }

    private function renderDebugPage(\Throwable $e, int $statusCode): string
    {
        $class = htmlspecialchars(get_class($e));
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = $e->getLine();
        $trace = htmlspecialchars($e->getTraceAsString());

        return <<<HTML
        <!DOCTYPE html>
        <html><head><title>Error {$statusCode}</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 2rem; background: #f8f9fa; }
            .error-box { background: #fff; border-left: 4px solid #dc3545; padding: 1.5rem; margin-bottom: 1rem; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
            h1 { color: #dc3545; font-size: 1.25rem; margin: 0 0 0.5rem; }
            .meta { color: #6c757d; font-size: 0.875rem; }
            pre { background: #212529; color: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.8rem; }
        </style></head><body>
        <div class="error-box">
            <h1>{$class}</h1>
            <p>{$message}</p>
            <p class="meta">{$file}:{$line}</p>
        </div>
        <pre>{$trace}</pre>
        </body></html>
        HTML;
    }

    private function renderProductionPage(int $statusCode): string
    {
        $title = $this->defaultMessage($statusCode);

        return <<<HTML
        <!DOCTYPE html>
        <html><head><title>{$title}</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                   display: flex; justify-content: center; align-items: center; min-height: 100vh;
                   margin: 0; background: #f8f9fa; color: #212529; }
            .error { text-align: center; }
            h1 { font-size: 4rem; margin: 0; color: #6c757d; }
            p { font-size: 1.25rem; color: #6c757d; }
        </style></head><body>
        <div class="error">
            <h1>{$statusCode}</h1>
            <p>{$title}</p>
        </div>
        </body></html>
        HTML;
    }

    private function defaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            default => 'Error',
        };
    }
}
