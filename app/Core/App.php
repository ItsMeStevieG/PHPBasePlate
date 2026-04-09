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
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\JsonApiTokenRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\JsonPermissionRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\JsonRoleRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\JsonUserRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\PermissionRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\RoleRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Repositories\UserRepository;
use ItsMeStevieG\PHPBasePlate\Auth\Services\ApiTokenService;
use ItsMeStevieG\PHPBasePlate\Auth\Services\AuthService;
use ItsMeStevieG\PHPBasePlate\Auth\Services\RbacService;
use ItsMeStevieG\PHPBasePlate\Content\FieldTypes\FieldTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentEntryRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentRevisionRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\ContentTypeRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\JsonContentEntryRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\JsonContentRevisionRepository;
use ItsMeStevieG\PHPBasePlate\Content\Repositories\JsonContentTypeRepository;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Schema\SchemaLoader;
use ItsMeStevieG\PHPBasePlate\Content\Schema\SchemaValidator;
use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Content\Services\RevisionService;
use ItsMeStevieG\PHPBasePlate\Content\Services\SchemaService;
use ItsMeStevieG\PHPBasePlate\Content\Validators\EntryValidator;
use ItsMeStevieG\PHPBasePlate\Media\Repositories\JsonMediaRepository;
use ItsMeStevieG\PHPBasePlate\Media\Repositories\MediaRepository;
use ItsMeStevieG\PHPBasePlate\Media\Services\MediaService;
use ItsMeStevieG\PHPBasePlate\Settings\Repositories\JsonMenuRepository;
use ItsMeStevieG\PHPBasePlate\Settings\Repositories\JsonSettingsRepository;
use ItsMeStevieG\PHPBasePlate\Settings\Repositories\MenuRepository;
use ItsMeStevieG\PHPBasePlate\Settings\Repositories\SettingsRepository;
use ItsMeStevieG\PHPBasePlate\Settings\Services\MenuService;
use ItsMeStevieG\PHPBasePlate\Settings\Services\SettingsService;
use ItsMeStevieG\PHPBasePlate\Core\Config\Config;
use ItsMeStevieG\PHPBasePlate\Core\Config\Env;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;
use ItsMeStevieG\PHPBasePlate\Core\Database\DualWriteProxy;
use ItsMeStevieG\PHPBasePlate\Core\Database\JsonStore;
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
    private string $storageDriver = 'json';

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

        // Determine storage driver: json (default) or database
        $this->storageDriver = $this->resolveStorageDriver();

        // Register JsonStore (always available - used as fallback too)
        $jsonStore = new JsonStore($this->basePath . '/storage/data');
        $this->container->instance(JsonStore::class, $jsonStore);

        // Register database connection (lazy, only if driver is database)
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

        // Register repositories (driver-aware)
        $this->registerRepositories($jsonStore);

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
        $this->container->singleton(StartSessionMiddleware::class, fn() => new StartSessionMiddleware($this->container->get(Session::class)));
        $this->container->singleton(AuthMiddleware::class, fn() => new AuthMiddleware($this->container->get(AuthService::class)));
        $this->container->singleton(GuestMiddleware::class, fn() => new GuestMiddleware($this->container->get(AuthService::class)));
        $this->container->singleton(CsrfMiddleware::class, fn() => new CsrfMiddleware($this->container->get(Session::class)));
        $this->container->singleton(RoleMiddleware::class, fn() => new RoleMiddleware($this->container->get(AuthService::class), $this->container->get(RbacService::class)));
        $this->container->singleton(ApiTokenMiddleware::class, fn() => new ApiTokenMiddleware($this->container->get(ApiTokenService::class)));

        // Register content engine
        $fieldTypeRegistry = FieldTypeRegistry::createDefault();
        $this->container->instance(FieldTypeRegistry::class, $fieldTypeRegistry);

        $contentTypeRegistry = new ContentTypeRegistry();
        $this->container->instance(ContentTypeRegistry::class, $contentTypeRegistry);

        $schemaValidator = new SchemaValidator($fieldTypeRegistry);
        $this->container->instance(SchemaValidator::class, $schemaValidator);

        $this->container->singleton(SchemaLoader::class, fn() => new SchemaLoader(
            $this->basePath . '/resources/schemas',
            $schemaValidator,
            $contentTypeRegistry,
            $fieldTypeRegistry,
        ));

        $this->container->singleton(RevisionService::class, fn() => new RevisionService($this->container->get(ContentRevisionRepository::class)));
        $this->container->singleton(EntryValidator::class, fn() => new EntryValidator($contentTypeRegistry, $fieldTypeRegistry));
        $this->container->singleton(EntryService::class, fn() => new EntryService(
            $this->container->get(ContentEntryRepository::class),
            $this->container->get(ContentTypeRepository::class),
            $contentTypeRegistry,
            $fieldTypeRegistry,
            $this->container->get(EntryValidator::class),
            $this->container->get(RevisionService::class),
        ));
        $this->container->singleton(SchemaService::class, fn() => new SchemaService(
            $this->container->get(SchemaLoader::class),
            $contentTypeRegistry,
            $this->container->get(ContentTypeRepository::class),
        ));

        // Register media service
        $this->container->singleton(MediaService::class, fn() => new MediaService(
            $this->container->get(MediaRepository::class),
            $this->basePath . '/public/uploads',
        ));

        // Register settings and menu services
        $this->container->singleton(SettingsService::class, fn() => new SettingsService($this->container->get(SettingsRepository::class)));
        $this->container->singleton(MenuService::class, fn() => new MenuService($this->container->get(MenuRepository::class)));

        // Load schemas
        $this->container->get(SchemaLoader::class)->loadAll();

        // Register Twig extension
        $viewRenderer = $this->container->get(ViewRenderer::class);
        $viewRenderer->addExtension(new View\TwigExtension($this->container));
        $viewRenderer->addGlobal('app_name', $this->config->get('app.name', 'PHPBasePlate'));
        $viewRenderer->addGlobal('app_url', $this->config->get('app.url', ''));
        $viewRenderer->addGlobal('storage_driver', $this->storageDriver);

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

            foreach ($route->getParams() as $key => $value) {
                $request->setAttribute($key, $value);
            }

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

    public function getStorageDriver(): string
    {
        return $this->storageDriver;
    }

    /**
     * Determine storage driver:
     *   json     - flat files only (default)
     *   mysql    - MySQL only, no fallback (fails hard if DB unavailable)
     *   database - alias for mysql
     *   auto     - try MySQL, fall back to json if connection fails
     */
    private function resolveStorageDriver(): string
    {
        $driver = strtolower((string) $this->config->get('app.storage_driver', 'json'));

        if ($driver === 'mysql' || $driver === 'database') {
            return 'database';
        }

        if ($driver === 'auto') {
            try {
                $conn = new Connection(
                    host: $this->config->get('database.host', 'localhost'),
                    port: (int) $this->config->get('database.port', 3306),
                    database: $this->config->get('database.database', ''),
                    username: $this->config->get('database.username', ''),
                    password: $this->config->get('database.password', ''),
                );
                $conn->getPdo();
                return 'database';
            } catch (\Throwable) {
                $this->container->get(Logger::class)->warning(
                    'Database connection failed, falling back to JSON flat-file storage.',
                );
                return 'json';
            }
        }

        return 'json';
    }

    /**
     * Register all repositories based on the active storage driver.
     *
     * When using database driver, a DualWriteProxy wraps each repository
     * so writes go to both MySQL (primary) and JSON (secondary) automatically.
     * This keeps the JSON files as a live "last known good" snapshot.
     */
    private function registerRepositories(JsonStore $jsonStore): void
    {
        if ($this->storageDriver === 'database') {
            $db = fn() => $this->container->get(Connection::class);
            $logger = fn() => $this->container->get(Logger::class);

            // Database primary, JSON secondary - writes go to both
            $this->container->singleton(UserRepository::class, fn() => new DualWriteProxy(new UserRepository($db()), new JsonUserRepository($jsonStore), $logger()));
            $this->container->singleton(RoleRepository::class, fn() => new DualWriteProxy(new RoleRepository($db()), new JsonRoleRepository($jsonStore), $logger()));
            $this->container->singleton(PermissionRepository::class, fn() => new DualWriteProxy(new PermissionRepository($db()), new JsonPermissionRepository($jsonStore), $logger()));
            $this->container->singleton(ApiTokenRepository::class, fn() => new DualWriteProxy(new ApiTokenRepository($db()), new JsonApiTokenRepository($jsonStore), $logger()));
            $this->container->singleton(ContentTypeRepository::class, fn() => new DualWriteProxy(new ContentTypeRepository($db()), new JsonContentTypeRepository($jsonStore), $logger()));
            $this->container->singleton(ContentEntryRepository::class, fn() => new DualWriteProxy(new ContentEntryRepository($db()), new JsonContentEntryRepository($jsonStore), $logger()));
            $this->container->singleton(ContentRevisionRepository::class, fn() => new DualWriteProxy(new ContentRevisionRepository($db()), new JsonContentRevisionRepository($jsonStore), $logger()));
            $this->container->singleton(MediaRepository::class, fn() => new DualWriteProxy(new MediaRepository($db()), new JsonMediaRepository($jsonStore), $logger()));
            $this->container->singleton(SettingsRepository::class, fn() => new DualWriteProxy(new SettingsRepository($db()), new JsonSettingsRepository($jsonStore), $logger()));
            $this->container->singleton(MenuRepository::class, fn() => new DualWriteProxy(new MenuRepository($db()), new JsonMenuRepository($jsonStore), $logger()));
        } else {
            // JSON-only mode
            $this->container->singleton(UserRepository::class, fn() => new JsonUserRepository($jsonStore));
            $this->container->singleton(RoleRepository::class, fn() => new JsonRoleRepository($jsonStore));
            $this->container->singleton(PermissionRepository::class, fn() => new JsonPermissionRepository($jsonStore));
            $this->container->singleton(ApiTokenRepository::class, fn() => new JsonApiTokenRepository($jsonStore));
            $this->container->singleton(ContentTypeRepository::class, fn() => new JsonContentTypeRepository($jsonStore));
            $this->container->singleton(ContentEntryRepository::class, fn() => new JsonContentEntryRepository($jsonStore));
            $this->container->singleton(ContentRevisionRepository::class, fn() => new JsonContentRevisionRepository($jsonStore));
            $this->container->singleton(MediaRepository::class, fn() => new JsonMediaRepository($jsonStore));
            $this->container->singleton(SettingsRepository::class, fn() => new JsonSettingsRepository($jsonStore));
            $this->container->singleton(MenuRepository::class, fn() => new JsonMenuRepository($jsonStore));
        }
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        $app = $this;

        // Admin and API routes must load before web (which has catch-all /{slug})
        foreach (['admin', 'api', 'web'] as $file) {
            $path = $this->basePath . '/routes/' . $file . '.php';
            if (file_exists($path)) {
                require $path;
            }
        }
    }

    private function callHandler(mixed $handler, Request $request, array $params): Response
    {
        if ($handler instanceof \Closure) {
            $result = $handler($request, ...array_values($params));
            return $this->prepareResponse($result);
        }

        if (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = is_string($class) ? new $class($this->container) : $class;
            $result = $controller->$method($request, ...array_values($params));
            return $this->prepareResponse($result);
        }

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
