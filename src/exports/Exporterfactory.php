<?php

namespace App\Exports;

use InvalidArgumentException;

/**
 * ExporterFactory
 * ---------------------------------------------------------------
 * Fabrique + registre des exporteurs disponibles. C'est le point
 * unique à modifier pour ajouter un nouveau format d'export :
 *
 *   ExporterFactory::register('mon_format', MonFormatExporter::class);
 *
 * Le reste de l'application (contrôleurs, routes /export/{format})
 * n'a jamais besoin d'être modifié.
 * ---------------------------------------------------------------
 */
class ExporterFactory
{
    /** @var array<string, class-string<ExporterInterface>> */
    private static array $registry = [
        'alpaca'             => AlpacaExporter::class,
        'sharegpt'           => ShareGPTExporter::class,
        'openai_messages'    => OpenAIMessagesExporter::class,
        'instruction_output' => InstructionOutputExporter::class,
        'json'               => JsonExporter::class,
        'jsonl'              => JsonlExporter::class,
    ];

    /**
     * Enregistre un nouveau format d'export (ou remplace un format existant).
     */
    public static function register(string $formatKey, string $exporterClass): void
    {
        if (!is_subclass_of($exporterClass, ExporterInterface::class)) {
            throw new InvalidArgumentException(
                sprintf('%s doit implémenter %s', $exporterClass, ExporterInterface::class)
            );
        }

        self::$registry[$formatKey] = $exporterClass;
    }

    /**
     * Instancie l'exporteur correspondant à un format donné.
     */
    public static function make(string $formatKey): ExporterInterface
    {
        if (!isset(self::$registry[$formatKey])) {
            throw new InvalidArgumentException(
                sprintf('Format d\'export inconnu : "%s"', $formatKey)
            );
        }

        $class = self::$registry[$formatKey];

        return new $class();
    }

    /**
     * Retourne la liste des clés de formats actuellement disponibles.
     *
     * @return string[]
     */
    public static function availableFormats(): array
    {
        return array_keys(self::$registry);
    }
}