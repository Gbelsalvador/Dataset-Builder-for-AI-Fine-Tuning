<?php

namespace App\Exports;

/**
 * JsonExporter
 * ---------------------------------------------------------------
 * Export générique : un tableau JSON contenant le champ "content"
 * brut de chaque exemple, sans transformation vers un format
 * conversationnel spécifique. Utile pour un traitement personnalisé
 * en aval ou pour des formats non standards.
 * ---------------------------------------------------------------
 */
class JsonExporter extends AbstractExporter
{
    public function export(array $examples): string
    {
        $rows = array_map(
            static fn (array $example) => (array) $example['content'],
            $examples
        );

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
        return 'json';
    }
}