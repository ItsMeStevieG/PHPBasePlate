<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\Database;

/**
 * Wraps two repository instances (primary + secondary) and delegates
 * all calls to the primary. Write operations are also forwarded to
 * the secondary so both stores stay in sync automatically.
 *
 * If the secondary write fails, it logs a warning but does not
 * block the primary operation.
 */
class DualWriteProxy
{
    /** Method prefixes that indicate a write operation */
    private const WRITE_PREFIXES = [
        'create', 'insert', 'update', 'delete', 'remove',
        'upsert', 'save', 'store', 'publish', 'unpublish',
        'assign', 'sync', 'updateLastLogin', 'updateLastUsed',
        'updateItem', 'updateMeta', 'deleteItem', 'createItem',
        'saveItems',
    ];

    private ?object $logger;

    public function __construct(
        private readonly object $primary,
        private readonly object $secondary,
        ?object $logger = null,
    ) {
        $this->logger = $logger;
    }

    public function __call(string $method, array $args): mixed
    {
        // Always call primary
        $result = $this->primary->$method(...$args);

        // If it's a write method, also call secondary
        if ($this->isWriteMethod($method)) {
            try {
                $this->secondary->$method(...$args);
            } catch (\Throwable $e) {
                $class = get_class($this->secondary);
                $this->logger?->warning(
                    "DualWrite: secondary {$class}->{$method}() failed: {$e->getMessage()}"
                );
            }
        }

        return $result;
    }

    private function isWriteMethod(string $method): bool
    {
        foreach (self::WRITE_PREFIXES as $prefix) {
            if ($method === $prefix || str_starts_with($method, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
