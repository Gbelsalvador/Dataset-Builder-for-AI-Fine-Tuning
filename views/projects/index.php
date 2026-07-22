<?php
/**
 * views/projects/index.php
 * ---------------------------------------------------------------
 * Page d'accueil de l'application : liste des projets de dataset
 * (chargée en AJAX par assets/js/projects.js) et formulaire de
 * création d'un nouveau projet.
 * ---------------------------------------------------------------
 */
$pageTitle = 'Projets';
$extraScripts = '<script src="/assets/js/projects.js"></script>';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-end mb-4 gap-3">
    <div>
        <p class="db-eyebrow mb-1">Vos jeux de données</p>
        <h1 class="h3 mb-1">Projets de dataset</h1>
        <p class="text-secondary mb-0">Regroupez vos exemples d'entraînement par projet, puis exportez-les en un clic.</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
        + Nouveau projet
    </button>
</div>

<div id="projectsGrid" class="row g-3">
    <!-- Injecté en JS par projects.js -->
</div>

<div id="projectsEmpty" class="db-empty d-none">
    <p class="mb-2 fw-semibold">Aucun projet pour l'instant</p>
    <p class="mb-3 text-secondary">Créez votre premier projet pour commencer à ajouter des exemples d'entraînement.</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createProjectModal">
        Créer un projet
    </button>
</div>

<!-- Modal : création de projet -->
<div class="modal fade" id="createProjectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="createProjectForm">
        <div class="modal-header">
          <h5 class="modal-title">Nouveau projet de dataset</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" for="projectName">Nom du projet</label>
            <input type="text" class="form-control" id="projectName" name="name" required placeholder="Ex : Support client FR">
          </div>
          <div class="mb-3">
            <label class="form-label" for="projectDescription">Description <span class="text-secondary">(optionnel)</span></label>
            <textarea class="form-control" id="projectDescription" name="description" rows="2" placeholder="À quoi servira ce dataset ?"></textarea>
          </div>
          <div class="mb-1">
            <label class="form-label" for="projectFormat">Format cible</label>
            <select class="form-select" id="projectFormat" name="format" required>
              <option value="alpaca">Alpaca (instruction / input / output)</option>
              <option value="sharegpt">ShareGPT (conversations)</option>
              <option value="openai_messages">OpenAI Messages</option>
              <option value="instruction_output">Instruction / Output</option>
              <option value="json">JSON générique</option>
              <option value="jsonl">JSONL générique</option>
            </select>
            <div class="form-text">Vous pourrez tout de même exporter vers n'importe quel autre format ensuite.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Créer le projet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>