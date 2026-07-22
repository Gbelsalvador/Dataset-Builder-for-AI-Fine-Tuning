<?php

namespace App\Exports;

/**
 * AbstractExporter
 * ---------------------------------------------------------------
 * Classe de base pour tous les exporteurs. Fournit :
 *
 *   - normalize() : transforme le champ "content" d'un exemple, quelle
 *     que soit sa forme de stockage d'origine (Alpaca, ShareGPT,
 *     OpenAI Messages...), vers une structure interne "pivot".
 *     Cela permet à n'importe quel exporteur de produire n'importe
 *     quel format cible, même si l'exemple a été saisi dans un
 *     format différent.
 *
 *   - jsonEncode() : encodage JSON cohérent (UTF-8, sans échappement
 *     des accents ni des slashs).
 * ---------------------------------------------------------------
 */
abstract class AbstractExporter implements ExporterInterface
{
    /**
     * Structure pivot retournée :
     * [
     *   'instruction' => ?string,
     *   'input'       => ?string,
     *   'output'      => ?string,
     *   'system'      => ?string,
     *   'user'        => ?string,
     *   'assistant'   => ?string,
     *   'messages'    => [ ['role' => string, 'content' => string], ... ],
     * ]
     */
    protected function normalize(array $content): array
    {
        $normalized = [
            'instruction' => $content['instruction'] ?? null,
            'input'       => $content['input'] ?? null,
            'output'      => $content['output'] ?? null,
            'system'      => $content['system'] ?? null,
            'user'        => $content['user'] ?? null,
            'assistant'   => $content['assistant'] ?? null,
            'messages'    => [],
        ];

        // Cas 1 : la structure "messages" est déjà présente
        // (format OpenAI Messages ou ShareGPT importé/saisi tel quel).
        if (!empty($content['messages']) && is_array($content['messages'])) {
            foreach ($content['messages'] as $message) {
                $role = $message['role'] ?? $message['from'] ?? null;
                $text = $message['content'] ?? $message['value'] ?? null;

                // Compatibilité avec la nomenclature ShareGPT (human/gpt).
                $role = match ($role) {
                    'human' => 'user',
                    'gpt'   => 'assistant',
                    default => $role,
                };

                if ($role !== null && $text !== null) {
                    $normalized['messages'][] = ['role' => $role, 'content' => $text];

                    if (in_array($role, ['system', 'user', 'assistant'], true) && $normalized[$role] === null) {
                        $normalized[$role] = $text;
                    }
                }
            }
        }

        // Cas 2 : champs system / user / assistant saisis à plat.
        if (empty($normalized['messages']) && ($normalized['system'] || $normalized['user'] || $normalized['assistant'])) {
            foreach (['system', 'user', 'assistant'] as $role) {
                if (!empty($normalized[$role])) {
                    $normalized['messages'][] = ['role' => $role, 'content' => $normalized[$role]];
                }
            }
        }

        // Cas 3 : format instruction / input / output -> reconstitution
        // d'une conversation équivalente pour les formats conversationnels.
        if (empty($normalized['messages']) && $normalized['instruction'] !== null) {
            $userContent = trim(
                $normalized['instruction'] . (!empty($normalized['input']) ? "\n\n" . $normalized['input'] : '')
            );

            $messages = [];
            if (!empty($normalized['system'])) {
                $messages[] = ['role' => 'system', 'content' => $normalized['system']];
            }
            $messages[] = ['role' => 'user', 'content' => $userContent];
            if ($normalized['output'] !== null) {
                $messages[] = ['role' => 'assistant', 'content' => $normalized['output']];
            }

            $normalized['messages'] = $messages;
        }

        return $normalized;
    }

    /**
     * Encode proprement en JSON (UTF-8, lisible, sans échappement des slashs).
     *
     * @param mixed $data
     */
    protected function jsonEncode($data, bool $pretty = true): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;

        if ($pretty) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($data, $flags);
    }
}