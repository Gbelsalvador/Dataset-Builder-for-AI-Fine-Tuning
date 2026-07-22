<?php

namespace App\Exports;

/**
 * AlpacaExporter
 * ---------------------------------------------------------------
 * Exporte le dataset au format Alpaca : un tableau JSON d'objets
 * { "instruction": "...", "input": "...", "output": "..." }.
 * ---------------------------------------------------------------
 */
class AlpacaExporter extends AbstractExporter
{
    public function export(array $examples): string
    {
        $rows = [];

        foreach ($examples as $example) {
            $normalized = $this->normalize((array) $example['content']);

            $rows[] = [
                'instruction' => $normalized['instruction'] ?? $normalized['user'] ?? '',
                'input'       => $normalized['input'] ?? '',
                'output'      => $normalized['output'] ?? $normalized['assistant'] ?? '',
            ];
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
        return 'alpaca';
    }
}