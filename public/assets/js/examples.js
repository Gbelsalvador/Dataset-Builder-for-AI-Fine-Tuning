const PROJECT_ID = getProjectIdFromUrl();
const PAGE_SIZE = 10;
let currentPage = 1;
let currentProject = null;
let searchTimeout = null;

document.addEventListener('DOMContentLoaded', () => {
  loadProject();
  loadExamples();
  document.getElementById('btnAddExample').addEventListener('click', () => openExampleModal());
  document.getElementById('btnAddExampleEmpty').addEventListener('click', () => openExampleModal());
  document.getElementById('exampleForm').addEventListener('submit', onSaveExample);
  document.getElementById('recordQuantity').addEventListener('input', () => renderGeneratedRecords());
  document.getElementById('btnAddSchemaField').addEventListener('click', addSchemaField);
  document.getElementById('btnSaveSchema').addEventListener('click', saveSchema);
  document.getElementById('schemaFields').addEventListener('click', onSchemaClick);
  document.getElementById('schemaFields').addEventListener('input', refreshSchemaOptions);
  document.getElementById('searchInput').addEventListener('input', (event) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => onSearch(event.target.value.trim()), 350);
  });
});

async function loadProject() {
  try {
    const { data: project } = await Api.get(`/projects/show/${PROJECT_ID}`);
    currentProject = project;
    const schema = Array.isArray(project.schema) ? project.schema : [];
    document.getElementById('projectHeader').innerHTML = `
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div><span class="db-tag mb-2">${escapeHtml(FORMAT_LABELS[project.format] || project.format)}</span>
        <h1 class="h4 mb-1 mt-2">${escapeHtml(project.name)}</h1>
        <p class="text-secondary mb-0">${escapeHtml(project.description || 'Aucune description')}</p></div>
        <button type="button" class="btn btn-outline-danger btn-sm" id="btnDeleteProject">Supprimer le projet</button>
      </div>`;
    document.getElementById('btnDeleteProject').addEventListener('click', onDeleteProject);
    renderSchema(schema);
    renderFormatChoices(project.format);
  } catch (error) { Toast.error(error.message); }
}

async function loadExamples(page = 1) {
  currentPage = page;
  try {
    const { data } = await Api.get(`/examples/list?project_id=${PROJECT_ID}&page=${page}&limit=${PAGE_SIZE}`);
    renderExamples(data.examples);
    renderPagination(data.total, page, PAGE_SIZE);
  } catch (error) { Toast.error(error.message); }
}

async function onSearch(query) {
  if (!query) return loadExamples(1);
  try {
    const { data } = await Api.get(`/examples/search?project_id=${PROJECT_ID}&q=${encodeURIComponent(query)}`);
    renderExamples(data.examples);
    document.getElementById('pagination').innerHTML = '';
  } catch (error) { Toast.error(error.message); }
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
  list.querySelectorAll('[data-edit]').forEach((button) => button.addEventListener('click', () => openExampleModal(button.dataset.edit, JSON.parse(button.dataset.content))));
  list.querySelectorAll('[data-delete]').forEach((button) => button.addEventListener('click', () => onDeleteExample(button.dataset.delete)));
}

function renderExampleCard(example) {
  const id = example._id?.$oid || example._id;
  const entries = Object.entries(example.content || {}).slice(0, 3);
  return `<div class="db-example-card">
    ${entries.map(([key, value]) => `<div class="db-field-label">${escapeHtml(key)}</div><div class="db-field-value">${escapeHtml(truncate(displayValue(value), 220))}</div>`).join('')}
    <div class="d-flex justify-content-end gap-2 mt-2">
      <button type="button" class="btn btn-sm btn-outline-secondary" data-edit="${id}" data-content='${JSON.stringify(example.content || {}).replace(/'/g, '&#39;')}'>Modifier</button>
      <button type="button" class="btn btn-sm btn-outline-danger" data-delete="${id}">Supprimer</button>
    </div></div>`;
}

function displayValue(value) { return typeof value === 'object' ? JSON.stringify(value) : String(value ?? ''); }

function renderPagination(total, page, limit) {
  const pages = Math.ceil(total / limit);
  const pagination = document.getElementById('pagination');
  if (pages <= 1) return void (pagination.innerHTML = '');
  pagination.innerHTML = Array.from({ length: pages }, (_, index) => {
    const number = index + 1;
    return `<li class="page-item ${number === page ? 'active' : ''}"><button type="button" class="page-link" data-page="${number}">${number}</button></li>`;
  }).join('');
  pagination.querySelectorAll('[data-page]').forEach((button) => button.addEventListener('click', () => loadExamples(Number(button.dataset.page))));
}

function renderSchema(schema) {
  const fields = schema.length ? schema : [{ name: '', type: 'string', parent: '', repeat_on: '', required: false }];
  document.getElementById('schemaFields').innerHTML = fields.map(renderSchemaRow).join('');
  refreshSchemaOptions();
}

function renderSchemaRow(field) {
  return `<div class="row g-2 align-items-end schema-row mb-2" data-schema-row>
    <div class="col-md-3"><label class="form-label small">Nom du champ</label><input class="form-control" data-schema-name value="${escapeHtml(field.name || '')}" placeholder="ex. nom_enfant"></div>
    <div class="col-md-2"><label class="form-label small">Type</label><select class="form-select" data-schema-type>${['string', 'integer', 'number', 'boolean'].map((type) => `<option value="${type}" ${field.type === type ? 'selected' : ''}>${type}</option>`).join('')}</select></div>
    <div class="col-md-2"><label class="form-label small">Parent</label><select class="form-select" data-schema-parent data-initial-value="${escapeHtml(field.parent || '')}"><option value="">Racine</option></select></div>
    <div class="col-md-3"><label class="form-label small">Répéter selon</label><select class="form-select" data-schema-repeat data-initial-value="${escapeHtml(field.repeat_on || '')}"><option value="">Jamais</option></select></div>
    <div class="col-md-1 form-check pb-2"><input class="form-check-input" type="checkbox" data-schema-required ${field.required ? 'checked' : ''}><label class="form-check-label small">Requis</label></div>
    <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm w-100" data-remove-schema aria-label="Supprimer le champ">×</button></div>
  </div>`;
}

function addSchemaField() {
  const fields = collectSchema();
  fields.push({ name: '', type: 'string', parent: '', repeat_on: '', required: false });
  renderSchema(fields);
  document.querySelector('#schemaFields .schema-row:last-child [data-schema-name]').focus();
}

function onSchemaClick(event) {
  if (!event.target.matches('[data-remove-schema]')) return;
  event.target.closest('[data-schema-row]').remove();
  refreshSchemaOptions();
}

function collectSchema() {
  return [...document.querySelectorAll('[data-schema-row]')].map((row) => ({
    name: row.querySelector('[data-schema-name]').value.trim(),
    type: row.querySelector('[data-schema-type]').value,
    parent: row.querySelector('[data-schema-parent]').value,
    repeat_on: row.querySelector('[data-schema-repeat]').value,
    required: row.querySelector('[data-schema-required]').checked,
  })).filter((field) => field.name);
}

function refreshSchemaOptions() {
  const fields = [...document.querySelectorAll('[data-schema-row]')].map((row) => ({ name: row.querySelector('[data-schema-name]').value.trim(), type: row.querySelector('[data-schema-type]').value }));
  document.querySelectorAll('[data-schema-row]').forEach((row, index) => {
    const parent = row.querySelector('[data-schema-parent]');
    const repeat = row.querySelector('[data-schema-repeat]');
    const oldParent = parent.value || parent.dataset.initialValue || '';
    const oldRepeat = repeat.value || repeat.dataset.initialValue || '';
    parent.innerHTML = '<option value="">Racine</option>' + fields.filter((field, i) => i !== index && field.name).map((field) => `<option value="${escapeHtml(field.name)}">${escapeHtml(field.name)}</option>`).join('');
    repeat.innerHTML = '<option value="">Jamais</option>' + fields.filter((field, i) => i !== index && field.type === 'integer' && field.name).map((field) => `<option value="${escapeHtml(field.name)}">${escapeHtml(field.name)}</option>`).join('');
    parent.value = oldParent;
    repeat.value = oldRepeat;
    delete parent.dataset.initialValue;
    delete repeat.dataset.initialValue;
  });
}

async function saveSchema() {
  const schema = collectSchema();
  if (!schema.length) return Toast.error('Ajoutez au moins un champ au schéma.');
  try {
    await Api.put(`/projects/update/${PROJECT_ID}`, { schema });
    currentProject.schema = schema;
    Toast.success('Structure enregistrée.');
  } catch (error) { Toast.error(error.message); }
}

function openExampleModal(id = null, content = null) {
  if (!currentProject?.schema?.length) return Toast.error('Enregistrez d’abord la structure du dataset.');
  document.getElementById('exampleForm').reset();
  document.getElementById('exampleId').value = id || '';
  document.getElementById('recordQuantity').value = '1';
  document.getElementById('exampleModalTitle').textContent = id ? 'Modifier la donnée' : 'Nouvelles données';
  renderGeneratedRecords(1, content ? [content] : []);
  new bootstrap.Modal(document.getElementById('exampleModal')).show();
}

function renderGeneratedRecords(quantity = null, drafts = null) {
  const count = Math.min(100, Math.max(1, Number(quantity || document.getElementById('recordQuantity').value || 1)));
  const previous = drafts || collectRecordDrafts();
  document.getElementById('recordQuantity').value = String(count);
  document.getElementById('generatedRecordFields').innerHTML = Array.from({ length: count }, (_, index) => `<fieldset class="border rounded p-3 mb-3" data-record="${index}"><legend class="float-none w-auto px-2 fs-6">Donnée ${index + 1}</legend>${renderFieldsForRecord(currentProject.schema, previous[index] || {})}</fieldset>`).join('');
  const repeatSources = new Set(currentProject.schema.map((field) => field.repeat_on).filter(Boolean));
  document.querySelectorAll('[data-field-path]').forEach((input) => {
    if (repeatSources.has(input.dataset.fieldPath.split('.').pop())) {
      input.addEventListener('input', () => renderGeneratedRecords(count));
    }
  });
}

function renderFieldsForRecord(schema, draft) {
  return schema.map((field) => {
    const repeatPath = field.parent && field.repeat_on ? `${field.parent}.${field.repeat_on}` : field.repeat_on;
    const count = repeatPath ? Math.max(0, Number(getDraftValue(draft, repeatPath) || 0)) : 1;
    if (!count) return '';
    return Array.from({ length: count }, (_, index) => {
      const key = index ? `${field.name}_${index + 1}` : field.name;
      const path = field.parent ? `${field.parent}.${key}` : key;
      const value = getDraftValue(draft, path);
      const type = field.type === 'boolean' ? 'checkbox' : field.type === 'number' || field.type === 'integer' ? 'number' : 'text';
      return `<div class="mb-3"><label class="form-label">${escapeHtml(field.name)}${field.repeat_on ? ` ${index + 1}` : ''}${field.required ? ' *' : ''}</label><input class="form-control" type="${type}" data-field-path="${escapeHtml(path)}" data-field-type="${field.type}" ${field.repeat_on ? 'data-repeat-source="false"' : ''} ${field.required ? 'required' : ''} ${type === 'checkbox' ? (value ? 'checked' : '') : `value="${escapeHtml(value ?? '')}"`}></div>`;
    }).join('');
  }).join('');
}

function getDraftValue(object, path) { return path.split('.').reduce((value, key) => value == null ? undefined : value[key], object); }

function collectRecordDrafts() {
  return [...document.querySelectorAll('[data-record]')].map((record) => {
    const values = {};
    record.querySelectorAll('[data-field-path]').forEach((input) => setNestedValue(values, input.dataset.fieldPath, input.type === 'checkbox' ? input.checked : input.value));
    return values;
  });
}

function collectRecordValues() {
  return collectRecordDrafts().map((record) => {
    currentProject.schema.forEach((field) => {
      const value = field.parent ? getDraftValue(record, field.parent)?.[field.name] : record[field.name];
      if (value === undefined || value === '') return;
      const converted = field.type === 'integer' ? Number.parseInt(value, 10) : field.type === 'number' ? Number(value) : field.type === 'boolean' ? Boolean(value) : value;
      if (field.parent) {
        const parent = getDraftValue(record, field.parent);
        if (parent) parent[field.name] = converted;
      } else record[field.name] = converted;
    });
    return record;
  });
}

function setNestedValue(object, path, value) {
  const keys = path.split('.');
  const last = keys.pop();
  const target = keys.reduce((current, key) => current[key] || (current[key] = {}), object);
  target[last] = value;
}

async function onSaveExample(event) {
  event.preventDefault();
  const id = document.getElementById('exampleId').value;
  const records = collectRecordValues();
  try {
    if (id) {
      await Api.put(`/examples/update/${id}`, { content: records[0] });
      Toast.success('Donnée mise à jour.');
    } else {
      for (const content of records) await Api.post('/examples/create', { project_id: PROJECT_ID, content });
      Toast.success(`${records.length} donnée(s) ajoutée(s).`);
    }
    bootstrap.Modal.getInstance(document.getElementById('exampleModal')).hide();
    loadExamples(currentPage);
    loadProject();
  } catch (error) { Toast.error(error.message); }
}

async function onDeleteExample(id) {
  if (!confirm('Supprimer cette donnée ?')) return;
  try {
    await Api.del(`/examples/delete/${id}`);
    Toast.success('Donnée supprimée.');
    loadExamples(currentPage);
    loadProject();
  } catch (error) { Toast.error(error.message); }
}

async function onDeleteProject() {
  if (!confirm('Supprimer ce projet et toutes ses données ? Cette action est irréversible.')) return;
  try {
    await Api.del(`/projects/delete/${PROJECT_ID}`);
    window.location.href = '/';
  } catch (error) { Toast.error(error.message); }
}
