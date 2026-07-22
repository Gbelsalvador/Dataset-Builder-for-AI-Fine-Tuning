<?php
/**
 * views/projects/show.php
 * ---------------------------------------------------------------
 * Atelier d'un projet : en-tête du projet, gestion des exemples
 * (ajout, édition, suppression, recherche, pagination) et panneau
 * d'export (prévisualisation + téléchargement).
 * Toute la donnée est chargée en AJAX par assets/js/examples.js et
 * assets/js/export.js, à partir de l'identifiant présent dans l'URL.
 * ---------------------------------------------------------------
 */
$pageTitle = 'Atelier du projet';
$extraScripts = '<script src="/assets/js/examples.js"></script><script src="/assets/js/export.js"></script>';
require __DIR__ . '/../layouts/header.php';
?>

<a href="/" class="db-back-link mb-3 d-inline-block">&larr; Tous les projets</a>

<div id="projectHeader" class="db-project-header mb-4">
    <!-- Injecté en JS par examples.js -->
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h2 class="h5 mb-0">Exemples d'entraînement</h2>
            <button type="button" class="btn btn-primary btn-sm" id="btnAddExample">
                + Ajouter un exemple
            </button>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text bg-white">🔍</span>
            <input type="search" class="form-control" id="searchInput" placeholder="Rechercher dans les exemples…">
        </div>

        <div id="examplesList"><!-- Injecté en JS par examples.js --></div>

        <div id="examplesEmpty" class="db-empty d-none">
            <p class="mb-2 fw-semibold">Aucun exemple pour l'instant</p>
            <p class="mb-3 text-secondary">Ajoutez votre premier exemple d'entraînement pour ce projet.</p>
            <button type="button" class="btn btn-primary" id="btnAddExampleEmpty">Ajouter un exemple</button>
        </div>

        <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center" id="pagination"></ul>
        </nav>
    </div>

    <div class="col-lg-4">
        <div class="db-export-panel">
            <p class="db-eyebrow mb-1">Export</p>
            <h2 class="h6 mb-3">Télécharger le dataset</h2>

            <div class="d-flex flex-column gap-2 mb-3" id="formatChoices">
                <!-- Généré en JS par export.js -->
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-outline-secondary" id="btnPreview">Prévisualiser</button>
                <button type="button" class="btn btn-primary" id="btnDownload">⬇ Télécharger</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal : ajout / édition d'exemple -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="exampleForm">
        <input type="hidden" id="exampleId" name="id">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalTitle">Nouvel exemple</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">

          <div class="btn-group mb-3" role="group" aria-label="Structure de l'exemple">
            <input type="radio" class="btn-check" name="structure" id="structureInstruction" value="instruction" checked>
            <label class="btn btn-outline-secondary btn-sm" for="structureInstruction">Instruction / Output</label>

            <input type="radio" class="btn-check" name="structure" id="structureMessages" value="messages">
            <label class="btn btn-outline-secondary btn-sm" for="structureMessages">Conversation</label>
          </div>

          <div id="fieldsInstruction">
            <div class="mb-3">
              <label class="form-label">Instruction</label>
              <textarea class="form-control" name="instruction" rows="2" placeholder="Que doit faire le modèle ?"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Input <span class="text-secondary">(optionnel)</span></label>
              <textarea class="form-control" name="input" rows="2" placeholder="Contexte ou donnée d'entrée"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Output attendu</label>
              <textarea class="form-control" name="output" rows="3" placeholder="Réponse idéale du modèle"></textarea>
            </div>
          </div>

          <div id="fieldsMessages" class="d-none">
            <div class="mb-3">
              <label class="form-label">System <span class="text-secondary">(optionnel)</span></label>
              <textarea class="form-control" name="system" rows="2" placeholder="Consigne système"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">User</label>
              <textarea class="form-control" name="user" rows="2" placeholder="Message de l'utilisateur"></textarea>
            </div>
            <div class="mb-3">
              <label class="form-label">Assistant</label>
              <textarea class="form-control" name="assistant" rows="3" placeholder="Réponse de l'assistant"></textarea>
            </div>
          </div>

          <div class="mb-1">
            <label class="form-label">Tags <span class="text-secondary">(séparés par des virgules)</span></label>
            <input type="text" class="form-control" name="tags" placeholder="résumé, français, facile">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer l'exemple</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal : prévisualisation avant export -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Prévisualisation — <span id="previewFormatLabel"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
      </div>
      <div class="modal-body">
        <p class="text-secondary small mb-2" id="previewMeta"></p>
        <pre class="db-json-preview" id="previewContent"></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary" id="btnDownloadFromPreview">⬇ Télécharger le dataset complet</button>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>