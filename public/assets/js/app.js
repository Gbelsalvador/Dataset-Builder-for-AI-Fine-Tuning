/**
 * app.js
 * ---------------------------------------------------------------
 * Utilitaires partagés par toutes les pages : appels AJAX (fetch)
 * vers l'API JSON du backend, notifications toast, et petits
 * helpers réutilisés par projects.js / examples.js / export.js.
 * ---------------------------------------------------------------
 */

const FORMAT_LABELS = {
  alpaca: 'Alpaca',
  sharegpt: 'ShareGPT',
  openai_messages: 'OpenAI Messages',
  instruction_output: 'Instruction / Output',
  json: 'JSON',
  jsonl: 'JSONL',
};

const Api = {
  async request(method, url, body = null) {
    const options = {
      method,
      headers: { 'Content-Type': 'application/json' },
    };
    if (body !== null) {
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);

    let payload = null;
    try {
      payload = await response.json();
    } catch (e) {
      payload = null;
    }

    if (!response.ok) {
      const message = payload?.message || `Erreur ${response.status}`;
      throw new Error(message);
    }

    return payload;
  },

  get(url) { return this.request('GET', url); },
  post(url, body) { return this.request('POST', url, body); },
  put(url, body) { return this.request('PUT', url, body); },
  del(url) { return this.request('DELETE', url); },
};

const Toast = {
  show(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const colors = { success: 'text-bg-success', error: 'text-bg-danger', info: 'text-bg-dark' };

    const el = document.createElement('div');
    el.className = `toast align-items-center ${colors[type] || colors.info} border-0`;
    el.setAttribute('role', 'alert');
    el.innerHTML = `
      <div class="d-flex">
        <div class="toast-body">${escapeHtml(message)}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>`;
    container.appendChild(el);

    const toast = new bootstrap.Toast(el, { delay: 4000 });
    toast.show();
    el.addEventListener('hidden.bs.toast', () => el.remove());
  },

  success(message) { this.show(message, 'success'); },
  error(message) { this.show(message, 'error'); },
};

/**
 * Échappe le HTML pour un affichage sûr dans les templates injectés en JS.
 */
function escapeHtml(value) {
  const div = document.createElement('div');
  div.textContent = value ?? '';
  return div.innerHTML;
}

/**
 * Tronque un texte à `max` caractères, avec une ellipse.
 */
function truncate(text, max) {
  text = text ?? '';
  return text.length > max ? text.slice(0, max) + '…' : text;
}

/**
 * Récupère l'identifiant de projet depuis l'URL courante (/projects/{id}).
 */
function getProjectIdFromUrl() {
  const parts = window.location.pathname.split('/').filter(Boolean);
  return parts[0] === 'projects' ? parts[1] : null;
}