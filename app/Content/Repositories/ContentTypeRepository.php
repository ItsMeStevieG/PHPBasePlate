<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Content\Repositories;

use ItsMeStevieG\PHPBasePlate\Core\Database\Connection;

class ContentTypeRepository
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findByMachineName(string $machineName): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM content_types WHERE machine_name = ?',
            [$machineName],
        );
    }

    public function upsert(array $data): int
    {
        $existing = $this->findByMachineName($data['machine_name']);

        if ($existing) {
            $this->db->execute(
                'UPDATE content_types SET label = ?, mode = ?, schema_path = ?, slug_field = ?,
                 title_field = ?, status_field_enabled = ?, revisioning_enabled = ?,
                 api_public_read = ?, api_auth_write = ?, config_json = ? WHERE id = ?',
                [
                    $data['label'], $data['mode'], $data['schema_path'],
                    $data['slug_field'], $data['title_field'],
                    $data['status_field_enabled'] ? 1 : 0,
                    $data['revisioning_enabled'] ? 1 : 0,
                    $data['api_public_read'] ? 1 : 0,
                    $data['api_auth_write'] ? 1 : 0,
                    json_encode($data['config'] ?? []),
                    $existing['id'],
                ],
            );
            return (int) $existing['id'];
        }

        $this->db->execute(
            'INSERT INTO content_types (machine_name, label, mode, schema_path, slug_field,
             title_field, status_field_enabled, revisioning_enabled, api_public_read,
             api_auth_write, config_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['machine_name'], $data['label'], $data['mode'], $data['schema_path'],
                $data['slug_field'], $data['title_field'],
                $data['status_field_enabled'] ? 1 : 0,
                $data['revisioning_enabled'] ? 1 : 0,
                $data['api_public_read'] ? 1 : 0,
                $data['api_auth_write'] ? 1 : 0,
                json_encode($data['config'] ?? []),
            ],
        );

        return (int) $this->db->lastInsertId();
    }

    public function syncFields(int $contentTypeId, array $fields): void
    {
        // Remove stale fields
        $existingNames = array_column($fields, 'name');
        if (!empty($existingNames)) {
            $placeholders = implode(',', array_fill(0, count($existingNames), '?'));
            $this->db->execute(
                "DELETE FROM content_fields WHERE content_type_id = ? AND machine_name NOT IN ({$placeholders})",
                array_merge([$contentTypeId], $existingNames),
            );
        }

        foreach ($fields as $field) {
            $existing = $this->db->fetchOne(
                'SELECT id FROM content_fields WHERE content_type_id = ? AND machine_name = ?',
                [$contentTypeId, $field['name']],
            );

            if ($existing) {
                $this->db->execute(
                    'UPDATE content_fields SET label = ?, field_type = ?, sort_order = ?,
                     is_required = ?, is_searchable = ?, is_filterable = ?, is_sortable = ?,
                     config_json = ? WHERE id = ?',
                    [
                        $field['label'], $field['type'], $field['sort_order'],
                        $field['is_required'] ? 1 : 0,
                        $field['is_searchable'] ? 1 : 0,
                        $field['is_filterable'] ? 1 : 0,
                        $field['is_sortable'] ? 1 : 0,
                        json_encode($field['config'] ?? []),
                        $existing['id'],
                    ],
                );
            } else {
                $this->db->execute(
                    'INSERT INTO content_fields (content_type_id, machine_name, label, field_type,
                     sort_order, is_required, is_searchable, is_filterable, is_sortable, config_json)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $contentTypeId, $field['name'], $field['label'], $field['type'],
                        $field['sort_order'],
                        $field['is_required'] ? 1 : 0,
                        $field['is_searchable'] ? 1 : 0,
                        $field['is_filterable'] ? 1 : 0,
                        $field['is_sortable'] ? 1 : 0,
                        json_encode($field['config'] ?? []),
                    ],
                );
            }
        }
    }

    public function all(): array
    {
        return $this->db->fetchAll('SELECT * FROM content_types ORDER BY label');
    }
}
