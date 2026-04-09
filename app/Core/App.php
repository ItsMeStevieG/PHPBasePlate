<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core;

use ItsMeStevieG\PHPBasePlate\Auth\Middleware\AuthMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\ApiTokenMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\CsrfMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\GuestMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\RoleMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Middleware\StartSessionMiddleware;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\ApiTokenRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\PermissionRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\RoleRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\UserRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Services\ApiTokenService;
use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Auth\Services\RbacService;
use ItsMeStevieG\PHPBasePlate\Core\Config\Config;
use ItsMeStevieG\PHPBasePlate\Core\Config\Env;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\ExceptionHandler;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\Middleware\MiddlewarePipeline;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;
use ItsMeStevieG\PHPBasePlate\Core\Http\Response;
use ItsMeStevieG\PHPBasePlate\Core\Logging\Logger;
use ItsMeStevieG\PHPBasePlate\Core\Routing\Router;
use ItsMeStevieG\PHPBasePlate\Core\Support\Session;
use ItsMeStevieG\PHPBasePlate\Core\View\ViewRenderer;

class App
{
    private Container $container;
    private Router $router;
    private Config $config;
    private ?ExceptionHandler $exceptionHandler = null;
    private array $globalMiddleware = [];

    public function __construct(private readonly string $basePath)
    {
        $this->container = new Container();
        $this->router = new Router();

        $this->container->instance(self::class, $this);
        $this->container->instance(Container::class, $this->container);
        $this->container->instance(Router::class, $this->router);
    }

    public function bootstrap(): void
    {
        // Load environment
        Env::load($this->basePath);

        // Load configuration
        $this->config = new Config($this->basePath . '/config');
        $this->config->load();
        $this->container->instance(Config::class, $this->config);

        // Set timezone
        $timezone = $this->config->get('app.timezone', 'UTC');
        date_default_timezone_set($timezone);

        // Register logger
        $logger = new Logger($this->basePath . '/storage/logs/app.log');
        $this->container->instance(Logger::class, $logger);

        // Register exception handler
        $this->exceptionHandler = new ExceptionHandler(
            $logger,
            (bool) $this->config->get('app.debug', false),
        );
        $this->exceptionHandler->register();
        $this->container->instance(ExceptionHandler::class, $this->exceptionHandler);

        // Register database connection (lazy)
        $this->container->singleton(Connection::class, function (): Connection {
            return new Connection(
                host: $this->config->get('database.host', 'localhost'),
                port: (int) $this->config->get('database.port', 3306),
                database: $this->config->get('database.database', ''),
                username: $this->config->get('database.username', ''),
                password: $this->config->get('database.password', ''),
                charset: $this->config->get('database.charset', 'utf8mb4'),
            );
        });

        // Register view renderer (lazy)
        $this->container->singleton(ViewRenderer::class, function (): ViewRenderer {
            $cachePath = $this->config->get('view.cache')
                ? $this->basePath . '/storage/cache/views'
                : null;

            return new ViewRenderer(
                $this->basePath . '/resources/views',
                $cachePath,
            );
        });

        // Register session
        $session = new Session();
        $this->container->instance(Session::class, $session);

        // Register auth repositories (lazy)
        $this->container->singleton(UserRepository::class, function (): UserRepository {
            return new UserRepository($this->container->get(Connection::class));
        });
        $this->container->singleton(RoleRepository::class, function (): RoleRepository {
            return new RoleRepository($this->container->get(Connection::class));
        });
        $this->container->singleton(PermissionRepository::class, function (): PermissionRepository {
            return new PermissionRepository($this->container->get(Connection::class));
        });
        $this->container->singleton(ApiTokenRepository::class, function (): ApiTokenRepository {
            return new ApiTokenRepository($this->container->get(Connection::class));
        });

        // Register auth services (lazy)
        $this->container->singleton(AuthService::class, function (): AuthService {
            return new AuthService(
                $this->container->get(UserRepository::class),
                $this->container->get(Session::class),
            );
        });
        $this->container->singleton(RbacService::class, function (): RbacService {
            return new RbacService(
                $this->container->get(RoleRepository::class),
                $this->container->get(PermissionRepository::class),
            );
        });
        $this->container->singleton(ApiTokenService::class, function (): ApiTokenService {
            return new ApiTokenService($this->container->get(ApiTokenRepository::class));
        });

        // Register middleware instances (lazy)
        $this->container->singleton(StartSessionMiddleware::class, function (): StartSessionMiddleware {
            return new StartSessionMiddleware($this->container->get(Session::class));
        });
        $this->container->singleton(AuthMiddleware::class, function (): AuthMiddleware {
            return new AuthMiddleware($this->container->get(AuthService::class));
        });
        $this->container->singleton(GuestMiddleware::class, function (): GuestMiddleware {
            return new GuestMiddleware($this->container->get(AuthService::class));
        });
        $this->container->singleton(CsrfMiddleware::class, function (): CsrfMiddleware {
            return new CsrfMiddleware($this->container->get(Session::class));
        });
        $this->container->singleton(RoleMiddleware::class, function (): RoleMiddleware {
            return new RoleMiddleware(
                $this->container->get(AuthService::class),
                $this->container->get(RbacService::class),
            );
        });
        $this->container->singleton(ApiTokenMiddleware::class, function (): ApiTokenMiddleware {
            return new ApiTokenMiddleware($this->container->get(ApiTokenService::class));
        });

        // Load routes
        $this->loadRoutes();
    }

    public function run(Request $request): Response
    {
        try {
            $route = $this->router->resolve($request);

            if ($route === null) {
                throw new HttpException(404, "Route not found: {$request->method()} {$request->path()}");
            }

            // Inject route params into request
            foreach ($route->getParams() as $key => $value) {
                $request->setAttribute($key, $value);
            }

            // Build middleware stack: global + route-specific
            $middleware = array_merge($this->globalMiddleware, $route->getMiddleware());

            $pipeline = new MiddlewarePipeline($this->container);
            $pipeline->through($middleware);

            return $pipeline->run($request, function (Request $request) use ($route): Response {
                return $this->callHandler($route->getHandler(), $request, $route->getParams());
            });
        } catch (\Throwable $e) {
            return $this->exceptionHandler->handle($e, $request);
        }
    }

    public function middleware(array $middleware): void
    {
        $this->globalMiddleware = array_merge($this->globalMiddleware, $middleware);
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        $app = $this;

        $routeFiles = ['web', 'admin', 'api'];

        foreach ($routeFiles as $file) {
            $path = $this->basePath . '/routes/' . $file . '.php';
            if (file_exists($path)) {
                require $path;
            }
        }
    }

    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        // Closure handler
        if ($handler instanceof \Closure) {
            $result = $handler($request, ...array_values($params));
            return $this->prepareResponse($result);
        }

        // [ControllerClass, method] array
        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = is_string($class) ? new $class($this->container) : $class;
            $result = $controller->$method($request, ...array_values($params));
            return $this->prepareResponse($result);
        }

        // "Controller@method" string
        if (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler, 2);
            $controller = new $class($this->container);
            $result = $controller->$method($request, ...array_values($params));
            return $this->prepareResponse($result);
        }

        throw new \RuntimeException('Invalid route handler.');
    }

    private function prepareResponse(mixed $result): Response
    {
        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return new Http\JsonResponse($result);
        }

        return new Response((string) $result);
    }
}
