/**
 * export.js
 * ---------------------------------------------------------------
 * Logique du panneau d'export (views/projects/show.php) : choix du
 * format, prévisualisation avant export et téléchargement du
 * dataset, via ExportController.
 * ---------------------------------------------------------------
 */

let selectedFormat = null;

/**
 * Génère les pastilles de sélection de format et présélectionne le
 * format par défaut du projet. Appelée par examples.js une fois le
 * projet chargé.
 */
function renderFormatChoices(defaultFormat) {
  const container = document.getElementById('formatChoices');
  selectedFormat = defaultFormat;

  container.innerHTML = Object.entries(FORMAT_LABELS).map(([key, label]) => `
    <label class="db-format-choice ${key === defaultFormat ? 'active' : ''}" data-format="${key}">
      <input type="radio" class="db-format-radio" name="exportFormat" value="${key}" ${key === defaultFormat ? 'checked' : ''}>
      ${escapeHtml(label)}
    </label>`).join('');

  container.querySelectorAll('.db-format-choice').forEach((label) => {
    label.addEventListener('click', () => {
      container.querySelectorAll('.db-format-choice').forEach((l) => l.classList.remove('active'));
      label.classList.add('active');
      selectedFormat = label.dataset.format;
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('btnPreview').addEventListener('click', onPreview);
  document.getElementById('btnDownload').addEventListener('click', onDownload);
  document.getElementById('btnDownloadFromPreview').addEventListener('click', onDownload);
});

async function onPreview() {
  if (!selectedFormat) return;

  try {
    const { data } = await Api.get(`/export/preview/${selectedFormat}?project_id=${PROJECT_ID}&limit=5`);

    document.getElementById('previewFormatLabel').textContent = FORMAT_LABELS[selectedFormat] || selectedFormat;
    document.getElementById('previewMeta').textContent =
      `Aperçu de ${data.preview_count} exemple(s) sur ${data.total_examples} au total.`;
    document.getElementById('previewContent').textContent = data.content;

    new bootstrap.Modal(document.getElementById('previewModal')).show();
  } catch (e) {
    Toast.error(e.message);
  }
}

function onDownload() {
  if (!selectedFormat) return;
  window.location.href = `/export/${selectedFormat}?project_id=${PROJECT_ID}`;
}