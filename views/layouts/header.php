<?php
/**
 * header.php
 * ---------------------------------------------------------------
 * En-tête commun à toutes les pages : <head>, barre de navigation,
 * ouverture du conteneur principal.
 *
 * Variable attendue (optionnelle) : $pageTitle
 * ---------------------------------------------------------------
 */
$pageTitle = $pageTitle ?? 'Dataset Builder';
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> · Dataset Builder</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand db-navbar">
    <div class="container">
        <a class="navbar-brand db-brand" href="/">
            <span class="db-brand-mark">DB</span> Dataset&nbsp;Builder
        </a>
        <span class="db-navbar-tag">construisez vos datasets de fine-tuning</span>
    </div>
</nav>

<main class="container db-main"></main>