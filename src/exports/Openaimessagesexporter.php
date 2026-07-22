<?php

namespace App\Exports;

/**
 * OpenAIMessagesExporter
 * ---------------------------------------------------------------
 * Exporte le dataset au format "OpenAI Messages" en JSONL : chaque
 * ligne contient un objet { "messages": [ {role, content}, ... ] },
 * compatible avec l'API Chat Completions et la plupart des
 * frameworks de fine-tuning (Axolotl, LLaMA-Factory, TRL...).
 * ---------------------------------------------------------------
 */
class OpenAIMessagesExporter extends AbstractExporter
{
    public function export(array $examples): string
    {
        $lines = [];

        foreach ($examples as $example) {
            $normalized = $this->normalize((array) $example['content']);

            $messages = array_map(
                static fn (array $m) => ['role' => $m['role'], 'content' => $m['content']],
                $normalized['messages']
            );

            $lines[] = $this->jsonEncode(['messages' => $messages], false);
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
        return 'openai_messages';
    }
}