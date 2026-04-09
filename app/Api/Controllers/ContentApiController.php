<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Api\Controllers;

use ItsMeStevieG\PHPBasePlate\Api\Serializers\EntrySerializer;
use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Exceptions\HttpException;
use ItsMeStevieG\PHPBasePlate\Core\Http\JsonResponse;
use ItsMeStevieG\PHPBasePlate\Core\Http\Request;

class ContentApiController
{
    private ContentTypeRegistry $typeRegistry;
    private EntryService $entryService;
    private EntrySerializer $serializer;

    public function __construct(Container $container)
    {
        $this->typeRegistry = $container->get(ContentTypeRegistry::class);
        $this->entryService = $container->get(EntryService::class);
        $this->serializer = new EntrySerializer();
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertPublicRead($schema);

        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(100, max(1, (int) $request->query('per_page', 20)));
        $status = $request->query('status');
        $search = $request->query('search');
        $sort = $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc');

        // Only show published entries for public API unless explicitly filtered
        if ($status === null && !$request->getAttribute('api_token')) {
            $status = 'published';
        }

        $result = $this->entryService->list(
            $type, $page, $perPage, $status, $search, (string) $sort, (string) $direction,
        );

        return JsonResponse::success(
            $this->serializer->serializeList($result['items'], $type),
            'Entries retrieved successfully.',
            [
                'pagination' => [
                    'page' => $result['page'],
                    'per_page' => $result['per_page'],
                    'total' => $result['total'],
                    'total_pages' => $result['total_pages'],
                ],
            ],
        );
    }

    public function show(Request $request, string $type, string $identifier): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertPublicRead($schema);

        // Try slug first, then ID
        $entry = $this->entryService->findBySlug($type, $identifier);

        if ($entry === null && is_numeric($identifier)) {
            $entry = $this->entryService->find((int) $identifier);
        }

        if ($entry === null) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        // Public consumers should not see draft entries
        if ($entry['status'] !== 'published' && !$request->getAttribute('api_token')) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        return JsonResponse::success(
            $this->serializer->serialize($entry, $type),
            'Entry retrieved successfully.',
        );
    }

    public function store(Request $request, string $type): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertAuthWrite($schema, $request);

        $input = $request->json() ?? $request->all();
        $userId = $request->getAttribute('api_user_id');

        $result = $this->entryService->create($type, $input, $userId ? (int) $userId : null);

        if (!$result['success']) {
            return JsonResponse::error('Validation failed.', $result['errors'], 422);
        }

        $entry = $this->entryService->find($result['id']);

        return JsonResponse::success(
            $this->serializer->serialize($entry, $type),
            'Entry created successfully.',
            [],
            201,
        );
    }

    public function update(Request $request, string $type, string $id): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertAuthWrite($schema, $request);

        $entry = $this->entryService->find((int) $id);
        if ($entry === null) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        $input = $request->json() ?? $request->all();
        $userId = $request->getAttribute('api_user_id');

        $result = $this->entryService->update((int) $id, $type, $input, $userId ? (int) $userId : null);

        if (!$result['success']) {
            return JsonResponse::error('Validation failed.', $result['errors'], 422);
        }

        $entry = $this->entryService->find((int) $id);

        return JsonResponse::success(
            $this->serializer->serialize($entry, $type),
            'Entry updated successfully.',
        );
    }

    public function destroy(Request $request, string $type, string $id): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertAuthWrite($schema, $request);

        if (!($schema['api']['allow_delete'] ?? false)) {
            return JsonResponse::error('Delete not allowed for this content type.', [], 403);
        }

        $entry = $this->entryService->find((int) $id);
        if ($entry === null) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        $this->entryService->delete((int) $id);

        return new JsonResponse([
            'success' => true,
            'message' => 'Entry deleted.',
            'data' => null,
            'meta' => (object) [],
            'errors' => [],
        ], 204);
    }

    public function publish(Request $request, string $type, string $id): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertAuthWrite($schema, $request);

        $entry = $this->entryService->find((int) $id);
        if ($entry === null) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        $userId = $request->getAttribute('api_user_id');
        $this->entryService->publish((int) $id, (int) $userId);

        $entry = $this->entryService->find((int) $id);

        return JsonResponse::success(
            $this->serializer->serialize($entry, $type),
            'Entry published.',
        );
    }

    public function unpublish(Request $request, string $type, string $id): JsonResponse
    {
        $schema = $this->resolveSchema($type);
        $this->assertAuthWrite($schema, $request);

        $entry = $this->entryService->find((int) $id);
        if ($entry === null) {
            return JsonResponse::error('Entry not found.', [], 404);
        }

        $userId = $request->getAttribute('api_user_id');
        $this->entryService->unpublish((int) $id, (int) $userId);

        $entry = $this->entryService->find((int) $id);

        return JsonResponse::success(
            $this->serializer->serialize($entry, $type),
            'Entry unpublished.',
        );
    }

    private function resolveSchema(string $type): array
    {
        $schema = $this->typeRegistry->get($type);
        if ($schema === null) {
            throw new HttpException(404, "Content type '{$type}' not found.");
        }

        return $schema;
    }

    private function assertPublicRead(array $schema): void
    {
        if (!($schema['api']['public_read'] ?? true)) {
            throw new HttpException(403, 'Public read access is not enabled for this content type.');
        }
    }

    private function assertAuthWrite(array $schema, Request $request): void
    {
        if (!($schema['api']['auth_write'] ?? true)) {
            throw new HttpException(403, 'API write access is not enabled for this content type.');
        }

        if (!$request->getAttribute('api_token')) {
            throw new HttpException(401, 'Authentication required for write operations.');
        }
    }
}
