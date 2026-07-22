<?php

namespace App\Exports;

/**
 * InstructionOutputExporter
 * ---------------------------------------------------------------
 * Exporte le dataset au format simplifié "Instruction / Output" en
 * JSONL, sans le champ "input" intermédiaire d'Alpaca (l'input, s'il
 * existe, est concaténé à l'instruction).
 * ---------------------------------------------------------------
 */
class InstructionOutputExporter extends AbstractExporter
{
    public function export(array $examples): string
    {
        $lines = [];

        foreach ($examples as $example) {
            $normalized = $this->normalize((array) $example['content']);

            $instruction = trim(
                ($normalized['instruction'] ?? $normalized['user'] ?? '') .
                (!empty($normalized['input']) ? "\n\n" . $normalized['input'] : '')
            );

            $lines[] = $this->jsonEncode([
                'instruction' => $instruction,
                'output'      => $normalized['output'] ?? $normalized['assistant'] ?? '',
            ], false);
        }

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
        return 'instruction_output';
    }
}