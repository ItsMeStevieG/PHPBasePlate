<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Api\Serializers;

class EntrySerializer
{
    public function serialize(array $entry, string $typeName): array
    {
        $payload = $entry['payload']
            ?? (isset($entry['payload_json']) ? json_decode($entry['payload_json'], true) : [])
            ?? [];

        return [
            'id' => (int) $entry['id'],
            'type' => $typeName,
            'title' => $entry['title'],
            'slug' => $entry['slug'],
            'status' => $entry['status'],
            'summary' => $entry['summary'],
            'published_at' => $entry['published_at'],
            'created_at' => $entry['created_at'],
            'updated_at' => $entry['updated_at'],
            'fields' => $payload,
            'links' => [
                'self' => "/api/{$typeName}/{$entry['slug']}",
            ],
        ];
    }

    public function serializeList(array $entries, string $typeName): array
    {
        return array_map(
            fn(array $entry) => $this->serialize($entry, $typeName),
            $entries,
        );
    }
}
