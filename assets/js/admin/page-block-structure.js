(() => {
  const cfg = window.jcpAdminBlockStructure;
  const root = document.getElementById('jcp-admin-block-structure');
  const textarea = document.getElementById(cfg?.textareaId || 'jcp_niche_content_json');
  if (!cfg || !root || !textarea) return;

  const registry = cfg.registry || [];
  const defaultProps = cfg.defaultProps || {};
  const i18n = cfg.i18n || {};

  let dragIndex = null;

  const labelFor = (type) => {
    const hit = registry.find((r) => r.type === type);
    return hit?.label || type;
  };

  const readDoc = () => {
    try {
      const parsed = JSON.parse(textarea.value || '{}');
      if (!parsed || typeof parsed !== 'object') return { blocks: [] };
      if (!Array.isArray(parsed.blocks)) parsed.blocks = [];
      return parsed;
    } catch (e) {
      return null;
    }
  };

  const writeDoc = (doc) => {
    textarea.value = JSON.stringify(doc, null, 2);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
  };

  const blockLabel = (block) => {
    if (block.label && String(block.label).trim()) return String(block.label).trim();
    return labelFor(block.type);
  };

  const render = () => {
    const doc = readDoc();
    root.innerHTML = '';
    if (!doc) {
      root.innerHTML = `<p class="jcp-admin-structure__error">${i18n.syncError || 'JSON error'}</p>`;
      return;
    }

    const list = document.createElement('ul');
    list.className = 'jcp-admin-structure__list';

    if (!(doc.blocks || []).length) {
      const empty = document.createElement('li');
      empty.className = 'jcp-admin-structure__empty';
      empty.textContent = i18n.empty || 'No sections';
      list.appendChild(empty);
    } else {
      doc.blocks.forEach((block, index) => {
        const li = document.createElement('li');
        li.className = 'jcp-admin-structure__item';
        li.dataset.index = String(index);
        li.innerHTML = `
          <span class="jcp-admin-structure__handle" title="Drag to reorder" aria-hidden="true">⋮⋮</span>
          <span class="jcp-admin-structure__type">${labelFor(block.type)}</span>
          <input type="text" class="jcp-admin-structure__label" aria-label="Section label" />
          <button type="button" class="button-link-delete jcp-admin-structure__remove">${i18n.remove || 'Remove'}</button>
        `;
        const labelInput = li.querySelector('.jcp-admin-structure__label');
        labelInput.value = blockLabel(block);

        const handle = li.querySelector('.jcp-admin-structure__handle');
        handle.draggable = true;
        handle.addEventListener('dragstart', (e) => {
          dragIndex = index;
          li.classList.add('is-dragging');
          e.dataTransfer.effectAllowed = 'move';
        });
        handle.addEventListener('dragend', () => {
          dragIndex = null;
          li.classList.remove('is-dragging');
        });

        li.addEventListener('dragover', (e) => {
          e.preventDefault();
          e.dataTransfer.dropEffect = 'move';
        });
        li.addEventListener('drop', (e) => {
          e.preventDefault();
          const from = dragIndex;
          const to = index;
          if (from === null || from === to) return;
          const current = readDoc();
          if (!current) return;
          const blocks = current.blocks.slice();
          const [moved] = blocks.splice(from, 1);
          blocks.splice(to, 0, moved);
          current.blocks = blocks;
          writeDoc(current);
          render();
        });

        labelInput.addEventListener('change', () => {
          const current = readDoc();
          if (!current || !current.blocks[index]) return;
          const next = labelInput.value.trim();
          if (next) current.blocks[index].label = next;
          else delete current.blocks[index].label;
          writeDoc(current);
        });

        li.querySelector('.jcp-admin-structure__remove').addEventListener('click', () => {
          if (!window.confirm(i18n.removeConfirm || 'Remove?')) return;
          const current = readDoc();
          if (!current) return;
          current.blocks = current.blocks.filter((_, i) => i !== index);
          writeDoc(current);
          render();
        });

        list.appendChild(li);
      });
    }

    root.appendChild(list);

    const toolbar = document.createElement('div');
    toolbar.className = 'jcp-admin-structure__toolbar';
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'button';
    addBtn.textContent = i18n.add || '+ Add block';
    addBtn.addEventListener('click', openAddModal);
    toolbar.appendChild(addBtn);
    root.appendChild(toolbar);
  };

  const openAddModal = () => {
    const doc = readDoc();
    if (!doc) {
      window.alert(i18n.syncError || 'Fix JSON first');
      return;
    }

    const overlay = document.createElement('div');
    overlay.className = 'jcp-admin-structure__modal';
    const panel = document.createElement('div');
    panel.className = 'jcp-admin-structure__modal-panel';
    const options = registry.map((r) => `<option value="${r.type}">${r.label}</option>`).join('');
    panel.innerHTML = `
      <h3>${i18n.chooseType || 'Choose block'}</h3>
      <select class="jcp-admin-structure__select">${options}</select>
      <div class="jcp-admin-structure__modal-actions">
        <button type="button" class="button button-primary jcp-admin-structure__insert">${i18n.insert || 'Insert'}</button>
        <button type="button" class="button jcp-admin-structure__cancel">${i18n.cancel || 'Cancel'}</button>
      </div>
    `;
    overlay.appendChild(panel);
    document.body.appendChild(overlay);

    const close = () => overlay.remove();
    panel.querySelector('.jcp-admin-structure__cancel').addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) close();
    });
    panel.querySelector('.jcp-admin-structure__insert').addEventListener('click', () => {
      const type = panel.querySelector('.jcp-admin-structure__select').value;
      const props = defaultProps[type] ? JSON.parse(JSON.stringify(defaultProps[type])) : {};
      const block = {
        type,
        props,
        layout: { width: 'contained' },
      };
      doc.blocks = doc.blocks || [];
      doc.blocks.push(block);
      writeDoc(doc);
      close();
      render();
    });
  };

  textarea.addEventListener('input', render);
  render();
})();
