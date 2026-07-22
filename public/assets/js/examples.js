/**
 * examples.js
 * ---------------------------------------------------------------
 * Logique de la page atelier d'un projet (views/projects/show.php) :
 * en-tête du projet, CRUD des exemples, recherche et pagination.
 * ---------------------------------------------------------------
 */

const PROJECT_ID = getProjectIdFromUrl();
const PAGE_SIZE = 10;
let currentPage = 1;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
  loadProject();
  loadExamples();

  document.getElementById('btnAddExample').addEventListener('click', () => openExampleModal());
  document.getElementById('btnAddExampleEmpty').addEventListener('click', () => openExampleModal());
  document.getElementById('exampleForm').addEventListener('submit', onSaveExample);

  document.querySelectorAll('input[name="structure"]').forEach((radio) => {
    radio.addEventListener('change', toggleStructureFields);
  });

  document.getElementById('searchInput').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => onSearch(e.target.value.trim()), 350);
  });
});

/**
 * Charge l'en-tête du projet (nom, description, format, actions)
 * et initialise le panneau d'export (export.js).
 */
async function loadProject() {
  try {
    const { data: project } = await Api.get(`/projects/show/${PROJECT_ID}`);

    document.getElementById('projectHeader').innerHTML = `
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
          <span class="db-tag mb-2">${escapeHtml(FORMAT_LABELS[project.format] || project.format)}</span>
          <h1 class="h4 mb-1 mt-2">${escapeHtml(project.name)}</h1>
          <p class="text-secondary mb-0">${escapeHtml(project.description || 'Aucune description')}</p>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteProject">Supprimer le projet</button>
      </div>`;

    document.getElementById('btnDeleteProject').addEventListener('click', onDeleteProject);

    // Initialise le panneau d'export avec le format par défaut du projet (export.js)
    renderFormatChoices(project.format);
  } catch (e) {
    Toast.error(e.message);
  }
}

async function loadExamples(page = 1) {
  currentPage = page;

  try {
    const { data } = await Api.get(`/examples/list?project_id=${PROJECT_ID}&page=${page}&limit=${PAGE_SIZE}`);
    renderExamples(data.examples);
    renderPagination(data.total, page, PAGE_SIZE);
  } catch (e) {
    Toast.error(e.message);
  }
}

async function onSearch(query) {
  if (!query) {
    loadExamples(1);
    return;
  }

  try {
    const { data } = await Api.get(`/examples/search?project_id=${PROJECT_ID}&q=${encodeURIComponent(query)}`);
    renderExamples(data.examples);
    document.getElementById('pagination').innerHTML = '';
  } catch (e) {
    Toast.error(e.message);
  }
}

function renderExamples(examples) {
  const list = document.getElementById('examplesList');
  const empty = document.getElementById('examplesEmpty');

  if (!examples.length) {
    list.innerHTML = '';
    empty.classList.remove('d-none');
    return;
  }

  empty.classList.add('d-none');
  list.innerHTML = examples.map(renderExampleCard).join('');

  list.querySelectorAll('[data-edit]').forEach((btn) => {
    btn.addEventListener('click', () => openExampleModal(btn.dataset.edit, JSON.parse(btn.dataset.content)));
  });
  list.querySelectorAll('[data-delete]').forEach((btn) => {
    btn.addEventListener('click', () => onDeleteExample(btn.dataset.delete));
  });
}

function renderExampleCard(example) {
  const id = example._id?.$oid || example._id;
  const content = example.content || {};
  const mainText = content.instruction || content.user || (content.messages?.[0]?.content) || '(sans instruction)';
  const outputText = content.output || content.assistant || '';
  const tags = example.tags || [];

  return `
    <div class="db-example-card">
      <div class="db-field-label">Instruction</div>
      <div class="db-field-value">${escapeHtml(truncate(mainText, 220))}</div>
      ${outputText ? `
        <div class="db-field-label">Output</div>
        <div class="db-field-value">${escapeHtml(truncate(outputText, 220))}</div>
      ` : ''}
      <div class="d-flex justify-content-between align-items-center mt-2">
        <div class="d-flex gap-1 flex-wrap">
          ${tags.map((t) => `<span class="badge text-bg-light border">${escapeHtml(t)}</span>`).join('')}
        </div>
        <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-edit="${id}" data-content='${JSON.stringify(content).replace(/'/g, '&#39;')}'>Modifier</button>
          <button type="button" class="btn btn-sm btn-outline-danger" data-delete="${id}">Supprimer</button>
        </div>
      </div>
    </div>`;
}

function renderPagination(total, page, limit) {
  const pages = Math.ceil(total / limit);
  const pagination = document.getElementById('pagination');

  if (pages <= 1) {
    pagination.innerHTML = '';
    return;
  }

  let html = '';
  for (let i = 1; i <= pages; i++) {
    html += `<li class="page-item ${i === page ? 'active' : ''}">
      <button type="button" class="page-link" data-page="${i}">${i}</button>
    </li>`;
  }
  pagination.innerHTML = html;

  pagination.querySelectorAll('[data-page]').forEach((btn) => {
    btn.addEventListener('click', () => loadExamples(Number(btn.dataset.page)));
  });
}

function openExampleModal(id = null, content = null) {
  const form = document.getElementById('exampleForm');
  form.reset();
  document.getElementById('exampleId').value = id || '';
  document.getElementById('exampleModalTitle').textContent = id ? "Modifier l'exemple" : 'Nouvel exemple';

  const isMessages = !!(content && (content.messages || content.system || content.user || content.assistant));
  document.getElementById(isMessages ? 'structureMessages' : 'structureInstruction').checked = true;
  toggleStructureFields();

  if (content) {
    form.instruction.value = content.instruction || '';
    form.input.value = content.input || '';
    form.output.value = content.output || '';
    form.system.value = content.system || '';
    form.user.value = content.user || '';
    form.assistant.value = content.assistant || '';
  }

  new bootstrap.Modal(document.getElementById('exampleModal')).show();
}

function toggleStructureFields() {
  const isMessages = document.getElementById('structureMessages').checked;
  document.getElementById('fieldsInstruction').classList.toggle('d-none', isMessages);
  document.getElementById('fieldsMessages').classList.toggle('d-none', !isMessages);
}

async function onSaveExample(event) {
  event.preventDefault();
  const form = event.target;
  const formData = new FormData(form);
  const id = formData.get('id');
  const isMessages = formData.get('structure') === 'messages';

  const content = isMessages
    ? {
        system: formData.get('system') || undefined,
        user: formData.get('user'),
        assistant: formData.get('assistant'),
      }
    : {
        instruction: formData.get('instruction'),
        input: formData.get('input') || undefined,
        output: formData.get('output'),
      };

  const tags = (formData.get('tags') || '')
    .split(',')
    .map((t) => t.trim())
    .filter(Boolean);

  try {
    if (id) {
      await Api.put(`/examples/update/${id}`, { content, tags });
      Toast.success('Exemple mis à jour.');
    } else {
      await Api.post('/examples/create', { project_id: PROJECT_ID, content, tags });
      Toast.success('Exemple ajouté.');
    }

    bootstrap.Modal.getInstance(document.getElementById('exampleModal')).hide();
    loadExamples(currentPage);
    loadProject();
  } catch (e) {
    Toast.error(e.message);
  }
}

async function onDeleteExample(id) {
  if (!confirm('Supprimer cet exemple ?')) return;

  try {
    await Api.del(`/examples/delete/${id}`);
    Toast.success('Exemple supprimé.');
    loadExamples(currentPage);
    loadProject();
  } catch (e) {
    Toast.error(e.message);
  }
}

async function onDeleteProject() {
  if (!confirm('Supprimer ce projet et tous ses exemples ? Cette action est irréversible.')) return;

  try {
    await Api.del(`/projects/delete/${PROJECT_ID}`);
    Toast.success('Projet supprimé.');
    window.location.href = '/';
  } catch (e) {
    Toast.error(e.message);
  }
}