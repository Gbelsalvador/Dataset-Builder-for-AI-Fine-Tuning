<?php

namespace App\Exports;

/**
 * ShareGPTExporter
 * ---------------------------------------------------------------
 * Exporte le dataset au format ShareGPT : un tableau JSON d'objets
 * { "conversations": [ {"from": "system|human|gpt", "value": "..."} ] }.
 * ---------------------------------------------------------------
 */
class ShareGPTExporter extends AbstractExporter
{
    private const ROLE_MAP = [
        'system'    => 'system',
        'user'      => 'human',
        'assistant' => 'gpt',
    ];

    public function export(array $examples): string
    {
        $rows = [];

        foreach ($examples as $example) {
            $normalized = $this->normalize((array) $example['content']);

            $conversations = [];
            foreach ($normalized['messages'] as $message) {
                $conversations[] = [
                    'from'  => self::ROLE_MAP[$message['role']] ?? $message['role'],
                    'value' => $message['content'],
                ];
            }

            $rows[] = ['conversations' => $conversations];
        }

        return $this->jsonEncode($rows, true);
    }

    public function getFileExtension(): string
    {
        return 'json';
    }

    public function getMimeType(): string
    {
        return 'application/json';
    }

    public function getFormatKey(): string
    {
        return 'sharegpt';
    }
}