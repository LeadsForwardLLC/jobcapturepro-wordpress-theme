(() => {
  const cfg = window.JCP_NICHE_EDITOR;
  if (!cfg || !cfg.postId || !cfg.restUrl) return;

  let content = {};
  let editing = false;
  let dirty = false;

  const setPath = (obj, path, value) => {
    const parts = path.split('.');
    let cur = obj;
    for (let i = 0; i < parts.length - 1; i++) {
      const key = parts[i];
      const next = parts[i + 1];
      if (/^\d+$/.test(next)) {
        if (!Array.isArray(cur[key])) {
          cur[key] = [];
        }
      } else if (cur[key] === undefined || cur[key] === null || typeof cur[key] !== 'object' || Array.isArray(cur[key])) {
        cur[key] = {};
      }
      cur = cur[key];
    }
    const last = parts[parts.length - 1];
    if (Array.isArray(cur)) {
      const idx = parseInt(last, 10);
      cur[idx] = value;
    } else {
      cur[last] = value;
    }
  };

  const getPath = (obj, path) => {
    return path.split('.').reduce((acc, key) => (acc && acc[key] !== undefined ? acc[key] : undefined), obj);
  };

  const bar = document.createElement('div');
  bar.className = 'jcp-niche-edit-bar';
  bar.innerHTML = `
    <strong>Industry page editor</strong>
    <button type="button" class="btn btn-primary" id="jcpNicheToggleEdit">Click to edit page</button>
    <button type="button" class="btn btn-secondary" id="jcpNicheSave" disabled>Save changes</button>
    <span id="jcpNicheStatus" class="jcp-niche-edit-status"></span>
    <a href="${cfg.adminUrl || '#'}" class="jcp-niche-edit-link">WP Admin</a>
  `;

  const popover = document.createElement('div');
  popover.className = 'jcp-niche-link-popover';
  popover.hidden = true;
  popover.innerHTML = `
    <label>Button link URL</label>
    <input type="text" id="jcpNicheLinkUrl" placeholder="/demo or https://..." />
    <div class="jcp-niche-link-popover-actions">
      <button type="button" class="btn btn-primary" id="jcpNicheLinkApply">Apply</button>
      <button type="button" class="btn btn-secondary" id="jcpNicheLinkCancel">Cancel</button>
    </div>
  `;

  document.body.appendChild(bar);
  document.body.appendChild(popover);
  document.body.classList.add('jcp-niche-editing');

  const statusEl = bar.querySelector('#jcpNicheStatus');
  const saveBtn = bar.querySelector('#jcpNicheSave');
  const toggleBtn = bar.querySelector('#jcpNicheToggleEdit');
  let activeLink = null;

  const markDirty = () => {
    dirty = true;
    saveBtn.disabled = false;
    statusEl.textContent = 'Unsaved changes';
  };

  const load = async () => {
    const res = await fetch(cfg.restUrl, {
      credentials: 'same-origin',
      headers: { 'X-WP-Nonce': cfg.nonce },
    });
    const data = await res.json();
    content = data.content || {};
  };

  const collectFromDom = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-path');
      if (!path) return;
      const text = (el.textContent || '').trim();
      setPath(content, path, text);
    });
    document.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-href-path');
      if (!path) return;
      setPath(content, path, el.getAttribute('href') || '');
    });
  };

  const enableEditing = () => {
    editing = true;
    document.body.classList.add('jcp-inline-editing');
    toggleBtn.textContent = 'Editing — click text to change';
    toggleBtn.classList.add('is-active');

    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      el.setAttribute('contenteditable', 'true');
      el.setAttribute('spellcheck', 'true');
      el.addEventListener('input', markDirty);
    });

    document.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
      el.addEventListener('click', (e) => {
        if (!editing) return;
        e.preventDefault();
        e.stopPropagation();
        activeLink = el;
        const urlInput = popover.querySelector('#jcpNicheLinkUrl');
        urlInput.value = el.getAttribute('href') || '';
        popover.hidden = false;
        const rect = el.getBoundingClientRect();
        popover.style.top = `${Math.min(window.innerHeight - 120, rect.bottom + 8)}px`;
        popover.style.left = `${Math.max(8, Math.min(window.innerWidth - 320, rect.left))}px`;
      });
    });
  };

  const disableEditing = () => {
    editing = false;
    document.body.classList.remove('jcp-inline-editing');
    toggleBtn.textContent = 'Click to edit page';
    toggleBtn.classList.remove('is-active');
    popover.hidden = true;
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      el.removeAttribute('contenteditable');
      el.removeAttribute('spellcheck');
    });
  };

  toggleBtn.addEventListener('click', () => {
    if (editing) {
      disableEditing();
    } else {
      enableEditing();
    }
  });

  popover.querySelector('#jcpNicheLinkApply').addEventListener('click', () => {
    if (!activeLink) return;
    const url = popover.querySelector('#jcpNicheLinkUrl').value.trim();
    activeLink.setAttribute('href', url);
    popover.hidden = true;
    markDirty();
  });

  popover.querySelector('#jcpNicheLinkCancel').addEventListener('click', () => {
    popover.hidden = true;
    activeLink = null;
  });

  saveBtn.addEventListener('click', async () => {
    collectFromDom();
    statusEl.textContent = 'Saving…';
    saveBtn.disabled = true;
    const res = await fetch(cfg.restUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
      body: JSON.stringify({ content }),
    });
    if (res.ok) {
      dirty = false;
      statusEl.textContent = 'Saved';
      window.location.reload();
    } else {
      statusEl.textContent = 'Save failed';
      saveBtn.disabled = false;
    }
  });

  load().then(() => {
    if (new URLSearchParams(window.location.search).get('jcp_edit') === '1') {
      enableEditing();
    }
  });
})();
