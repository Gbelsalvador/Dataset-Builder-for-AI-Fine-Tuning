<?php

namespace App\Exports;

/**
 * ExporterInterface
 * ---------------------------------------------------------------
 * Contrat commun à tous les exporteurs de dataset. Pour ajouter un
 * nouveau format d'export, il suffit de créer une classe qui
 * implémente cette interface puis de l'enregistrer dans
 * ExporterFactory — aucune autre partie de l'application n'a besoin
 * d'être modifiée (contrôleurs, routes, vues restent inchangés).
 * ---------------------------------------------------------------
 */
interface ExporterInterface
{
    /**
     * Transforme une liste d'exemples (documents MongoDB, chacun
     * disposant d'un champ "content") en une chaîne de caractères
     * prête à être écrite dans un fichier ou renvoyée en téléchargement.
     *
     * @param array $examples Liste de documents issus de
     *                         Example::getAllForExport()
     */
    public function export(array $examples): string;

    /**
     * Extension de fichier à utiliser lors du téléchargement (sans le point).
     */
    public function getFileExtension(): string;

    /**
     * Type MIME renvoyé lors du téléchargement / de la prévisualisation.
     */
    public function getMimeType(): string;

    /**
     * Clé unique identifiant le format (utilisée dans la route /export/{format}
     * et dans le registre de ExporterFactory).
     */
    public function getFormatKey(): string;
}