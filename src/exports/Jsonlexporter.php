<?php

namespace App\Exports;

/**
 * JsonlExporter
 * ---------------------------------------------------------------
 * Export générique au format JSONL (une ligne JSON par exemple),
 * sans transformation du contenu — une entrée par ligne, telle que
 * stockée dans MongoDB.
 * ---------------------------------------------------------------
 */
class JsonlExporter extends AbstractExporter
{
    public function export(array $examples): string
    {
        $lines = array_map(
            fn (array $example) => $this->jsonEncode((array) $example['content'], false),
            $examples
        );

        return implode("\n", $lines);
    }

    public function getFileExtension(): string
    {
        return 'jsonl';
    }

    public function getMimeType(): string
    {
        return 'application/jsonl';
    }

    public function getFormatKey(): string
    {
        return 'jsonl';
    }
}