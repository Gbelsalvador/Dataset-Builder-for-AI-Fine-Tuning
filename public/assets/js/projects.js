/**
 * projects.js
 * ---------------------------------------------------------------
 * Logique de la page d'accueil (views/projects/index.php) :
 * chargement de la liste des projets et création d'un nouveau
 * projet, via l'API JSON (ProjectController).
 * ---------------------------------------------------------------
 */

document.addEventListener('DOMContentLoaded', () => {
  loadProjects();
  document.getElementById('createProjectForm').addEventListener('submit', onCreateProject);
});

async function loadProjects() {
  const grid = document.getElementById('projectsGrid');
  const empty = document.getElementById('projectsEmpty');

  try {
    const { data: projects } = await Api.get('/projects/list');

    if (!projects.length) {
      grid.innerHTML = '';
      empty.classList.remove('d-none');
      return;
    }

    empty.classList.add('d-none');
    grid.innerHTML = projects.map(renderProjectCard).join('');
  } catch (e) {
    Toast.error(e.message);
  }
}

function renderProjectCard(project) {
  const id = project._id?.$oid || project._id;
  const formatLabel = FORMAT_LABELS[project.format] || project.format;

  return `
    <div class="col-md-6 col-xl-4">
      <a href="/projects/${id}" class="text-decoration-none text-reset">
        <div class="db-project-card">
          <span class="db-tag mb-3">${escapeHtml(formatLabel)}</span>
          <h3 class="h6 mb-1 mt-2">${escapeHtml(project.name)}</h3>
          <p class="text-secondary small mb-3">${escapeHtml(project.description || 'Aucune description')}</p>
          <div class="db-count">${project.examples_count ?? 0} exemple(s)</div>
        </div>
      </a>
    </div>`;
}

async function onCreateProject(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);

  try {
    await Api.post('/projects/create', {
      name: formData.get('name'),
      description: formData.get('description'),
      format: formData.get('format'),
    });

    bootstrap.Modal.getInstance(document.getElementById('createProjectModal')).hide();
    form.reset();
    Toast.success('Projet créé avec succès.');
    loadProjects();
  } catch (e) {
    Toast.error(e.message);
  }
}