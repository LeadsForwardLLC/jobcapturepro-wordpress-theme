(() => {
  const cfg = window.JCP_NICHE_EDITOR;
  if (!cfg || !cfg.postId || !cfg.restUrl) return;

  const bootstrap = cfg.bootstrap || {};
  const PAGE_KIND = bootstrap.pageKind || 'industry';
  const HISTORY_MAX = 50;
  const UNSAVED_MSG = 'You have unsaved changes. Leave this page anyway?';

  const BLOCK_SELECTORS = {
    breadcrumb: '.jcp-niche-breadcrumb',
    hero: '.jcp-niche-hero, .jcp-hero.jcp-hero-variant-home',
    what_it_is: '.jcp-niche-what',
    core_mechanic: '.jcp-niche-core-mechanic',
    how_it_works: '#how-it-works',
    check_ins: '.jcp-niche-checkins',
    problem: '.jcp-niche-problem',
    benefits: '.jcp-niche-benefits',
    differentiation: '.jcp-niche-diff',
    who_its_for: '.jcp-niche-audiences, #who-its-for',
    faq: '#faq',
    final_cta: '.jcp-niche-final',
    cta_band: '.jcp-niche-cta-band',
    commission: '.jcp-niche-commission',
    partners: '.jcp-niche-partners',
    share: '.jcp-niche-share',
    media_text: '.jcp-media-text',
    proof_flow: '.jcp-block-proof-flow, #real-job-proof',
    demo_preview: '.jcp-block-demo-preview, #demo-preview',
    directory_preview: '.jcp-block-directory-preview, .directory-preview',
    conversion: '.jcp-block-conversion, .conversion-section',
  };

  const HERO_VARIANTS = [
    { value: 'split', label: 'Split', hint: 'Copy + demo image' },
    { value: 'centered', label: 'Centered', hint: 'Headline & CTA focus' },
    { value: 'stacked', label: 'Stacked', hint: 'Copy above visual' },
    { value: 'home', label: 'Homepage', hint: 'Rotating headline + live phone' },
  ];

  let flatContent = bootstrap.content && typeof bootstrap.content === 'object' ? bootstrap.content : {};
  let pageDocument = bootstrap.blocks && Array.isArray(bootstrap.blocks.blocks)
    ? bootstrap.blocks
    : { version: 1, blocks: [] };
  let registry = Array.isArray(bootstrap.registry) ? bootstrap.registry : [];
  let editing = false;
  let dirty = false;
  let structureOpen = false;
  let dragIndex = null;
  let loaded = Array.isArray(bootstrap.registry) && bootstrap.registry.length > 0;
  let suppressRecord = false;
  let history = [];
  let historyIndex = -1;
  let savedSnapshot = null;
  const detachedPool = new Map();
  let recordTimer = null;

  const defaultProps = {
    hero: { h1: 'Page headline', subheadline: '', cta_primary: { label: 'Start free trial', url: '' }, cta_secondary: { label: 'See how it works', url: '#how-it-works' }, trust_line: '' },
    what_it_is: { headline: 'Section headline', subheadline: '' },
    how_it_works: { headline: 'How it works', subheadline: '', cta_label: 'See it in action', cta_url: '/demo', steps: [] },
    check_ins: { headline: 'Section headline', subheadline: '', features: [] },
    problem: { headline: 'Section headline', subheadline: '', pain_points: [] },
    benefits: { headline: 'Section headline', items: [] },
    differentiation: { headline: 'Section headline', body: '', bullets: [] },
    who_its_for: { headline: "Who it's for", audiences: [] },
    faq: { headline: 'Frequently asked questions', items: [] },
    final_cta: { headline: 'Ready to get started?', subheadline: '', cta_primary: { label: 'Start free trial', url: '' }, cta_secondary: { label: 'See how it works', url: '/demo' } },
    cta_band: { cta_primary: { label: 'Get started', url: '' }, band_key: 'cta_band_1' },
    breadcrumb: {},
    core_mechanic: [],
    commission: {},
    partners: {},
    share: {},
    proof_flow: {},
    demo_preview: {
      badge: 'Live Demo',
      headline: 'See it in action',
      body: '',
      cta_primary: { label: 'Launch Interactive Demo', url: '/demo' },
    },
    directory_preview: { headline: 'Section headline', cards: [] },
    conversion: { headline: 'Section headline', points: [] },
  };

  const getPath = (obj, path) => path.split('.').reduce((cur, key) => {
    if (cur == null) return undefined;
    return /^\d+$/.test(key) ? cur[parseInt(key, 10)] : cur[key];
  }, obj);

  const setPath = (obj, path, value) => {
    const parts = path.split('.');
    let cur = obj;
    for (let i = 0; i < parts.length - 1; i++) {
      const key = parts[i];
      const next = parts[i + 1];
      if (/^\d+$/.test(next)) {
        if (!Array.isArray(cur[key])) cur[key] = [];
      } else if (cur[key] === undefined || cur[key] === null || typeof cur[key] !== 'object' || Array.isArray(cur[key])) {
        cur[key] = {};
      }
      cur = cur[key];
    }
    const last = parts[parts.length - 1];
    if (Array.isArray(cur)) cur[parseInt(last, 10)] = value;
    else cur[last] = value;
  };

  const snapshot = () => ({
    pageDocument: JSON.parse(JSON.stringify(pageDocument)),
    flatContent: JSON.parse(JSON.stringify(flatContent)),
  });

  const statesEqual = (a, b) => JSON.stringify(a) === JSON.stringify(b);

  const bar = document.createElement('div');
  bar.className = 'jcp-niche-edit-bar';
  bar.innerHTML = `
    <strong class="jcp-niche-edit-bar-title">Page editor</strong>
    <button type="button" class="btn btn-secondary" id="jcpNicheUndo" disabled aria-label="Undo">Undo</button>
    <button type="button" class="btn btn-secondary" id="jcpNicheRedo" disabled aria-label="Redo">Redo</button>
    <button type="button" class="btn btn-secondary" id="jcpNicheStructureBtn">Page structure</button>
    <button type="button" class="btn btn-primary" id="jcpNicheToggleEdit">Click to edit page</button>
    <button type="button" class="btn btn-secondary" id="jcpNicheSave" disabled aria-label="Save changes">Save changes</button>
    <span id="jcpNicheStatus" class="jcp-niche-edit-status" aria-live="polite"></span>
    <a href="${cfg.adminUrl || '#'}" class="jcp-niche-edit-link">WP Admin</a>
  `;

  const structurePanel = document.createElement('aside');
  structurePanel.className = 'jcp-block-structure';
  structurePanel.hidden = true;
  structurePanel.innerHTML = `
    <div class="jcp-block-structure__header">
      <h2>Page structure</h2>
      <button type="button" class="jcp-block-structure__close" id="jcpStructureClose" aria-label="Close">×</button>
    </div>
    <p class="jcp-block-structure__hint">Drag to reorder. Click a section to scroll the preview. Rename titles for this page only — click Save to publish.</p>
    <ul class="jcp-block-structure__list" id="jcpBlockList"></ul>
    <button type="button" class="btn btn-secondary jcp-block-structure__add" id="jcpAddBlockBtn">+ Add block</button>
  `;

  const addModal = document.createElement('div');
  addModal.className = 'jcp-block-add-modal';
  addModal.hidden = true;
  addModal.innerHTML = `
    <div class="jcp-block-add-modal__dialog" role="dialog" aria-labelledby="jcpAddBlockTitle">
      <h3 id="jcpAddBlockTitle">Add block</h3>
      <ul class="jcp-block-add-modal__list" id="jcpAddBlockList"></ul>
      <button type="button" class="btn btn-secondary" id="jcpAddBlockCancel">Cancel</button>
    </div>
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
  document.body.appendChild(structurePanel);
  document.body.appendChild(addModal);
  document.body.appendChild(popover);
  document.body.classList.add('jcp-niche-editing');

  const statusEl = bar.querySelector('#jcpNicheStatus');
  const saveBtn = bar.querySelector('#jcpNicheSave');
  const undoBtn = bar.querySelector('#jcpNicheUndo');
  const redoBtn = bar.querySelector('#jcpNicheRedo');
  const toggleBtn = bar.querySelector('#jcpNicheToggleEdit');
  const structureBtn = bar.querySelector('#jcpNicheStructureBtn');
  const blockListEl = structurePanel.querySelector('#jcpBlockList');
  const addBlockListEl = addModal.querySelector('#jcpAddBlockList');
  const adminLink = bar.querySelector('.jcp-niche-edit-link');
  let activeLink = null;

  const getMain = () => document.querySelector('main.jcp-home, main.jcp-niche, main[data-page-kind]');

  const blockLabel = (type) => {
    const found = registry.find((b) => b.type === type);
    return found ? found.label : type;
  };

  const blockDisplayLabel = (block) => {
    const custom = typeof block.label === 'string' ? block.label.trim() : '';
    return custom || blockLabel(block.type);
  };

  const scrollToBlock = (block) => {
    const main = getMain();
    if (!main || !block?.id) return;
    const el = main.querySelector(`[data-jcp-block-id="${block.id}"]`);
    if (!el) return;
    const bar = document.querySelector('.jcp-niche-edit-bar');
    const offset = (bar?.offsetHeight || 0) + 16;
    const top = el.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    el.classList.add('jcp-block-scroll-target');
    window.setTimeout(() => el.classList.remove('jcp-block-scroll-target'), 1500);
  };

  const setBlockInstanceLabel = (block, value) => {
    const trimmed = value.trim();
    const defaultLabel = blockLabel(block.type);
    if (!trimmed || trimmed === defaultLabel) {
      delete block.label;
    } else {
      block.label = trimmed;
    }
  };

  const LAYOUT_CLASS_NAMES = [
    'jcp-layout-align-left',
    'jcp-layout-align-center',
    'jcp-layout-align-right',
    'jcp-layout-width-contained',
    'jcp-layout-width-wide',
    'jcp-layout-width-full',
    'jcp-hero-variant-split',
    'jcp-hero-variant-centered',
    'jcp-hero-variant-stacked',
    'jcp-hero-variant-home',
  ];

  const defaultLayout = (type) => {
    if (type === 'hero') {
      if (PAGE_KIND === 'referral') return { hero_variant: 'centered' };
      if (PAGE_KIND === 'home') return { hero_variant: 'home' };
      return { hero_variant: 'split' };
    }
    const layout = { align: 'center', width: 'contained' };
    if (type === 'breadcrumb') layout.align = 'left';
    return layout;
  };

  const resolveHeroVariant = (block) => {
    const layout = { ...defaultLayout('hero'), ...(block.layout || {}) };
    const variant = layout.hero_variant;
    if (['split', 'centered', 'stacked', 'home'].includes(variant)) return variant;
    if (block.layout?.hero_visual === false || block.props?.show_visual === false) return 'centered';
    return 'split';
  };

  const resolveLayout = (block) => {
    if (block.type === 'hero') {
      return { hero_variant: resolveHeroVariant(block) };
    }
    const layout = { ...defaultLayout(block.type), ...(block.layout || {}) };
    return layout;
  };

  const layoutClassNames = (block) => {
    if (block.type === 'hero') {
      return `jcp-hero-variant-${resolveHeroVariant(block)}`;
    }
    const layout = resolveLayout(block);
    return `jcp-layout-align-${layout.align} jcp-layout-width-${layout.width}`;
  };

  const layoutOptionsFor = (type) => {
    const found = registry.find((b) => b.type === type);
    return found?.layout_options || (type === 'hero'
      ? { hero_variant: true }
      : type === 'media_text'
        ? { media_position: true, align: true, width: true }
        : { align: true, width: true });
  };

  const applyLayoutToDom = () => {
    (pageDocument.blocks || []).forEach((block) => {
      const root = document.querySelector(`[data-jcp-block-id="${block.id}"]`);
      if (!root) return;

      if (block.type === 'hero') {
        const variant = resolveHeroVariant(block);
        ['split', 'centered', 'stacked'].forEach((v) => {
          root.classList.remove(`jcp-hero-variant-${v}`);
          root.querySelector('.jcp-niche-hero')?.classList.remove(`jcp-hero-variant-${v}`);
        });
        root.classList.add(`jcp-hero-variant-${variant}`);
        root.querySelector('.jcp-niche-hero')?.classList.add(`jcp-hero-variant-${variant}`);
        const visual = root.querySelector('.jcp-hero-visual');
        if (visual) visual.setAttribute('aria-hidden', variant === 'centered' ? 'true' : 'false');
        return;
      }

      LAYOUT_CLASS_NAMES.filter((cls) => cls.startsWith('jcp-layout-')).forEach((cls) => root.classList.remove(cls));
      layoutClassNames(block).split(' ').filter(Boolean).forEach((cls) => root.classList.add(cls));

      if (block.type === 'media_text') {
        const section = root.querySelector('.jcp-media-text') || root;
        section.classList.remove('jcp-media-text--media-left', 'jcp-media-text--media-right');
        const pos = block.props?.media_position === 'left' ? 'left' : 'right';
        section.classList.add(`jcp-media-text--media-${pos}`);
      }
    });
  };

  const setBlockLayout = (block, key, value) => {
    if (key === 'media_position') {
      block.props = block.props || {};
      block.props.media_position = value;
      applyLayoutToDom();
      renderBlockList();
      recordChange();
      return;
    }
    block.layout = { ...resolveLayout(block), [key]: value };
    if (block.type === 'hero' && key === 'hero_variant') {
      block.props = block.props || {};
      block.props.show_visual = value !== 'centered';
    }
    applyLayoutToDom();
    renderBlockList();
    recordChange();
  };

  const buildLayoutControlsHtml = (block) => {
    const layout = resolveLayout(block);
    const options = layoutOptionsFor(block.type);
    let html = '<div class="jcp-block-structure__layout">';

    if (options.hero_variant) {
      const variant = resolveHeroVariant(block);
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Hero style</span><div class="jcp-layout-btns jcp-layout-btns--stacked" data-setting="hero_variant">';
      HERO_VARIANTS.forEach((item) => {
        const active = variant === item.value ? ' is-active' : '';
        html += `<button type="button" class="jcp-layout-btn jcp-layout-btn--variant${active}" data-value="${item.value}" title="${item.hint}">${item.label}</button>`;
      });
      html += '</div></div>';
    }

    if (options.media_position) {
      const pos = block.props?.media_position === 'left' ? 'left' : 'right';
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Media</span><div class="jcp-layout-btns" data-setting="media_position">';
      html += `<button type="button" class="jcp-layout-btn${pos === 'left' ? ' is-active' : ''}" data-value="left">Left</button>`;
      html += `<button type="button" class="jcp-layout-btn${pos === 'right' ? ' is-active' : ''}" data-value="right">Right</button>`;
      html += '</div></div>';
    }

    if (options.align) {
      html += `<div class="jcp-layout-group"><span class="jcp-layout-group__label">Align</span><div class="jcp-layout-btns" data-setting="align">`;
      html += ['left', 'center', 'right'].map((value) => {
        const label = value === 'left' ? 'Left' : value === 'center' ? 'Center' : 'Right';
        const active = layout.align === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      html += '</div></div>';
    }

    if (options.width) {
      html += `<div class="jcp-layout-group"><span class="jcp-layout-group__label">Width</span><div class="jcp-layout-btns" data-setting="width">`;
      html += ['contained', 'wide', 'full'].map((value) => {
        const label = value === 'contained' ? 'Box' : value === 'wide' ? 'Wide' : 'Full';
        const active = layout.width === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      html += '</div></div>';
    }

    html += '</div>';
    return html;
  };

  const updateDirtyState = () => {
    dirty = savedSnapshot ? !statesEqual(snapshot(), savedSnapshot) : false;
    saveBtn.disabled = !dirty;
    saveBtn.classList.toggle('is-ready', dirty);
    statusEl.textContent = dirty ? 'Unsaved changes' : '';
    document.body.classList.toggle('jcp-has-unsaved', dirty);
  };

  const updateUndoRedoButtons = () => {
    undoBtn.disabled = historyIndex <= 0;
    redoBtn.disabled = historyIndex >= history.length - 1;
  };

  const initHistory = () => {
    const snap = snapshot();
    history = [snap];
    historyIndex = 0;
    savedSnapshot = JSON.parse(JSON.stringify(snap));
    updateUndoRedoButtons();
    updateDirtyState();
  };

  const recordChange = () => {
    if (suppressRecord) return;
    collectFromDom();
    const snap = snapshot();
    if (historyIndex >= 0 && statesEqual(snap, history[historyIndex])) {
      updateDirtyState();
      return;
    }
    history = history.slice(0, historyIndex + 1);
    history.push(snap);
    if (history.length > HISTORY_MAX) {
      history.shift();
    } else {
      historyIndex += 1;
    }
    updateUndoRedoButtons();
    updateDirtyState();
  };

  const scheduleRecordChange = () => {
    window.clearTimeout(recordTimer);
    recordTimer = window.setTimeout(recordChange, 400);
  };

  const restoreFromHistory = (snap) => {
    suppressRecord = true;
    pageDocument = JSON.parse(JSON.stringify(snap.pageDocument));
    flatContent = JSON.parse(JSON.stringify(snap.flatContent));
    applyFlatContentToDom();
    applyStructureToDom();
    renderBlockList();
    suppressRecord = false;
    updateDirtyState();
    updateUndoRedoButtons();
  };

  const undo = () => {
    if (historyIndex <= 0) return;
    historyIndex -= 1;
    restoreFromHistory(history[historyIndex]);
  };

  const redo = () => {
    if (historyIndex >= history.length - 1) return;
    historyIndex += 1;
    restoreFromHistory(history[historyIndex]);
  };

  const getBlockRoot = (node) => {
    if (!node) return null;
    if (node.classList && node.classList.contains('jcp-block-root')) return node;
    return node.closest('.jcp-block-root') || node;
  };

  const indexBlockSections = () => {
    const main = getMain();
    if (!main) return;
    const assigned = new Set(
      [...main.querySelectorAll('[data-jcp-block-id]')].map((el) => el.dataset.jcpBlockId)
    );
    (pageDocument.blocks || []).forEach((block) => {
      if (assigned.has(block.id)) return;
      const sel = BLOCK_SELECTORS[block.type];
      if (!sel) return;
      const match = [...main.querySelectorAll(sel)].find((node) => {
        const root = getBlockRoot(node);
        return root && !root.dataset.jcpBlockId;
      });
      if (!match) return;
      const root = getBlockRoot(match);
      root.dataset.jcpBlockId = block.id;
      root.dataset.jcpBlockType = block.type;
      assigned.add(block.id);
    });
  };

  const createPlaceholder = (block) => {
    const wrap = document.createElement('div');
    wrap.className = `jcp-block-root ${layoutClassNames(block)}`;
    wrap.dataset.jcpBlockId = block.id;
    wrap.dataset.jcpBlockType = block.type;
    const section = document.createElement('section');
    section.className = 'jcp-section jcp-block-placeholder';
    section.innerHTML = `
      <div class="jcp-container">
        <p class="jcp-block-placeholder__label">${blockLabel(block.type)}</p>
        <p class="jcp-block-placeholder__hint">New section — click to edit after adding, then save to publish.</p>
      </div>
    `;
    wrap.appendChild(section);
    return wrap;
  };

  const applyStructureToDom = () => {
    const main = getMain();
    if (!main) return;

    indexBlockSections();

    const pool = new Map();
    main.querySelectorAll('[data-jcp-block-id]').forEach((el) => {
      pool.set(el.dataset.jcpBlockId, getBlockRoot(el));
    });
    detachedPool.forEach((node, id) => {
      if (!pool.has(id)) pool.set(id, node);
    });

    const usedIds = new Set();
    const ordered = [];

    (pageDocument.blocks || []).forEach((block) => {
      let node = pool.get(block.id) || detachedPool.get(block.id);
      if (!node) {
        const sel = BLOCK_SELECTORS[block.type];
        if (sel) {
          const match = [...main.querySelectorAll(sel)].find((el) => {
            const root = getBlockRoot(el);
            return root && (!root.dataset.jcpBlockId || !usedIds.has(root.dataset.jcpBlockId));
          });
          if (match) node = getBlockRoot(match);
        }
      }
      if (!node) node = createPlaceholder(block);
      node.dataset.jcpBlockId = block.id;
      node.dataset.jcpBlockType = block.type;
      node.hidden = false;
      node.style.removeProperty('display');
      node.classList.remove('jcp-block-hidden');
      detachedPool.delete(block.id);
      usedIds.add(block.id);
      ordered.push(node);
    });

    pool.forEach((node, id) => {
      if (!usedIds.has(id)) {
        node.remove();
        detachedPool.set(id, node);
      }
    });

    ordered.forEach((node) => main.appendChild(node));
    applyLayoutToDom();
  };

  const applyFlatContentToDom = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-path');
      if (!path) return;
      const val = getPath(flatContent, path);
      if (val !== undefined && val !== null) el.textContent = String(val);
    });
    document.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-href-path');
      if (!path) return;
      const val = getPath(flatContent, path);
      if (val !== undefined && val !== null) el.setAttribute('href', String(val));
    });
  };

  const applyStructureChange = () => {
    renderBlockList();
    applyStructureToDom();
    recordChange();
  };

  const renderBlockList = () => {
    blockListEl.innerHTML = '';
    if (!(pageDocument.blocks || []).length) {
      blockListEl.innerHTML = '<li class="jcp-block-structure__empty">No sections listed yet. Use + Add block or refresh the page.</li>';
      return;
    }
    (pageDocument.blocks || []).forEach((block, index) => {
      const li = document.createElement('li');
      li.className = 'jcp-block-structure__item';
      li.dataset.index = String(index);
      const defaultLabel = blockLabel(block.type);
      li.innerHTML = `
        <div class="jcp-block-structure__row">
          <span class="jcp-block-structure__handle" aria-hidden="true" title="Drag to reorder">⋮⋮</span>
          <input
            type="text"
            class="jcp-block-structure__label-input"
            aria-label="Section title on this page"
            title="Rename for this page only"
          >
          <button type="button" class="jcp-block-structure__remove" data-index="${index}" aria-label="Remove block">Remove</button>
        </div>
        ${buildLayoutControlsHtml(block)}
      `;
      const handle = li.querySelector('.jcp-block-structure__handle');
      const labelInput = li.querySelector('.jcp-block-structure__label-input');
      labelInput.value = blockDisplayLabel(block);
      labelInput.placeholder = defaultLabel;

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

      li.addEventListener('click', (e) => {
        if (e.target.closest('input, button, .jcp-block-structure__handle, .jcp-block-structure__layout')) return;
        scrollToBlock(block);
      });

      labelInput.addEventListener('click', (e) => e.stopPropagation());
      labelInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          labelInput.blur();
        }
      });
      labelInput.addEventListener('blur', () => {
        const next = labelInput.value.trim();
        const prev = blockDisplayLabel(block);
        setBlockInstanceLabel(block, next);
        labelInput.value = blockDisplayLabel(block);
        if (blockDisplayLabel(block) !== prev) recordChange();
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
        const blocks = pageDocument.blocks.slice();
        const [moved] = blocks.splice(from, 1);
        blocks.splice(to, 0, moved);
        pageDocument.blocks = blocks;
        dragIndex = null;
        applyStructureChange();
      });
      li.querySelector('.jcp-block-structure__remove').addEventListener('click', (e) => {
        e.stopPropagation();
        if (!window.confirm(`Remove "${blockDisplayLabel(block)}" from this page?`)) return;
        pageDocument.blocks = pageDocument.blocks.filter((_, i) => i !== index);
        applyStructureChange();
      });
      li.querySelectorAll('.jcp-layout-btn').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const setting = btn.closest('[data-setting]').dataset.setting;
          let value = btn.dataset.value;
          if (setting === 'hero_variant') {
            setBlockLayout(block, setting, value);
            return;
          }
          if (setting === 'media_position') {
            setBlockLayout(block, setting, value);
            return;
          }
          setBlockLayout(block, setting, value);
        });
      });
      blockListEl.appendChild(li);
    });
  };

  const renderAddBlockList = () => {
    addBlockListEl.innerHTML = '';
    if (!registry.length) {
      addBlockListEl.innerHTML = '<li class="jcp-block-add-modal__empty">No blocks available for this page. Refresh the page or open WP Admin.</li>';
      return;
    }
    registry.forEach((item) => {
      const li = document.createElement('li');
      li.innerHTML = `<button type="button" class="jcp-block-add-modal__option"><strong>${item.label}</strong><span>${item.description || ''}</span></button>`;
      li.querySelector('button').addEventListener('click', () => {
        const props = defaultProps[item.type] ? JSON.parse(JSON.stringify(defaultProps[item.type])) : {};
        pageDocument.blocks = pageDocument.blocks || [];
        pageDocument.blocks.push({
          id: newBlockId(item.type),
          type: item.type,
          layout: defaultLayout(item.type),
          props,
        });
        closeAddModal();
        applyStructureChange();
      });
      addBlockListEl.appendChild(li);
    });
  };

  const newBlockId = (type) => `b-${type}-${Math.random().toString(36).slice(2, 8)}`;

  const closeAddModal = () => {
    addModal.hidden = true;
    addModal.setAttribute('hidden', '');
  };

  const openAddModal = () => {
    if (!loaded) {
      statusEl.textContent = 'Loading page data…';
      return;
    }
    renderAddBlockList();
    addModal.hidden = false;
    addModal.removeAttribute('hidden');
  };

  const openStructure = () => {
    structureOpen = true;
    structurePanel.hidden = false;
    structurePanel.removeAttribute('hidden');
    document.body.classList.add('jcp-structure-open');
    renderBlockList();
  };

  const closeStructure = () => {
    structureOpen = false;
    structurePanel.hidden = true;
    structurePanel.setAttribute('hidden', '');
    document.body.classList.remove('jcp-structure-open');
    closeAddModal();
  };

  const applyLoadedData = (data) => {
    if (!data || data.code) return false;
    flatContent = data.content || flatContent;
    if (data.blocks && Array.isArray(data.blocks.blocks)) {
      pageDocument = data.blocks;
    }
    if (Array.isArray(data.registry) && data.registry.length) {
      registry = data.registry;
    }
    return true;
  };

  const load = async () => {
    try {
      const res = await fetch(cfg.restUrl, {
        credentials: 'same-origin',
        headers: { 'X-WP-Nonce': cfg.nonce },
      });
      const data = await res.json();
      if (!res.ok || !applyLoadedData(data)) {
        if (!registry.length) statusEl.textContent = 'Editor data unavailable — try refreshing';
        return;
      }
      indexBlockSections();
      if (structureOpen) renderBlockList();
    } catch (err) {
      if (!registry.length) statusEl.textContent = 'Editor data unavailable — try refreshing';
    } finally {
      loaded = true;
    }
  };

  const collectFromDom = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-path');
      if (!path) return;
      setPath(flatContent, path, (el.textContent || '').trim());
    });
    document.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-href-path');
      if (!path) return;
      setPath(flatContent, path, el.getAttribute('href') || '');
    });
  };

  const enableEditing = () => {
    editing = true;
    document.body.classList.add('jcp-inline-editing');
    toggleBtn.textContent = 'Editing — click text to change';
    toggleBtn.classList.add('is-active');
    if (!dirty) statusEl.textContent = 'Click highlighted text or buttons to edit';
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      el.setAttribute('contenteditable', 'true');
      el.setAttribute('spellcheck', 'true');
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

  const confirmLeave = () => !dirty || window.confirm(UNSAVED_MSG);

  undoBtn.addEventListener('click', undo);
  redoBtn.addEventListener('click', redo);

  structureBtn.addEventListener('click', () => {
    if (structureOpen) closeStructure();
    else openStructure();
  });

  structurePanel.querySelector('#jcpStructureClose').addEventListener('click', closeStructure);
  structurePanel.querySelector('#jcpAddBlockBtn').addEventListener('click', openAddModal);
  addModal.querySelector('#jcpAddBlockCancel').addEventListener('click', closeAddModal);
  addModal.querySelector('.jcp-block-add-modal__dialog').addEventListener('click', (e) => e.stopPropagation());
  addModal.addEventListener('click', (e) => {
    if (e.target === addModal) closeAddModal();
  });

  toggleBtn.addEventListener('click', () => {
    if (editing) disableEditing();
    else enableEditing();
  });

  adminLink.addEventListener('click', (e) => {
    if (!confirmLeave()) e.preventDefault();
  });

  document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (!dirty || !link || link === adminLink) return;
    if (link.closest('.jcp-niche-edit-bar, .jcp-block-structure, .jcp-block-add-modal, .jcp-niche-link-popover')) return;
    if (editing && link.hasAttribute('data-jcp-href-path')) return;
    if (link.target === '_blank' || link.hasAttribute('download')) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (!window.confirm(UNSAVED_MSG)) e.preventDefault();
  }, true);

  document.addEventListener('input', (e) => {
    if (!editing || suppressRecord) return;
    if (!e.target.matches('[data-jcp-path]')) return;
    updateDirtyState();
    scheduleRecordChange();
  });

  document.addEventListener('click', (e) => {
    if (!editing) return;
    const link = e.target.closest('[data-jcp-href-path]');
    if (!link) return;
    e.preventDefault();
    e.stopPropagation();
    activeLink = link;
    popover.querySelector('#jcpNicheLinkUrl').value = link.getAttribute('href') || '';
    popover.hidden = false;
    popover.removeAttribute('hidden');
    const rect = link.getBoundingClientRect();
    popover.style.top = `${Math.min(window.innerHeight - 120, rect.bottom + 8)}px`;
    popover.style.left = `${Math.max(8, Math.min(window.innerWidth - 320, rect.left))}px`;
  });

  popover.querySelector('#jcpNicheLinkApply').addEventListener('click', () => {
    if (!activeLink) return;
    activeLink.setAttribute('href', popover.querySelector('#jcpNicheLinkUrl').value.trim());
    popover.hidden = true;
    popover.setAttribute('hidden', '');
    recordChange();
  });

  popover.querySelector('#jcpNicheLinkCancel').addEventListener('click', () => {
    popover.hidden = true;
    popover.setAttribute('hidden', '');
    activeLink = null;
  });

  saveBtn.addEventListener('click', async () => {
    if (saveBtn.disabled) return;
    collectFromDom();
    statusEl.textContent = 'Saving…';
    saveBtn.disabled = true;
    const res = await fetch(cfg.restUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': cfg.nonce },
      body: JSON.stringify({ blocks: pageDocument, content: flatContent }),
    });
    if (res.ok) {
      dirty = false;
      document.body.classList.remove('jcp-has-unsaved');
      statusEl.textContent = 'Saved';
      window.location.reload();
    } else {
      statusEl.textContent = 'Save failed — try again';
      updateDirtyState();
    }
  });

  document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
      if (dirty) {
        e.preventDefault();
        saveBtn.click();
      }
      return;
    }
    if ((e.metaKey || e.ctrlKey) && e.key === 'z') {
      e.preventDefault();
      if (e.shiftKey) redo();
      else undo();
      return;
    }
    if (e.key === 'Escape') {
      if (!addModal.hidden) {
        closeAddModal();
        return;
      }
      if (structureOpen) closeStructure();
    }
  });

  window.addEventListener('beforeunload', (e) => {
    if (!dirty) return;
    e.preventDefault();
    e.returnValue = UNSAVED_MSG;
    return UNSAVED_MSG;
  });

  initHistory();
  (pageDocument.blocks || []).forEach((block) => {
    if (!block.layout) block.layout = defaultLayout(block.type);
    if (block.type === 'hero') {
      if (!block.layout.hero_variant) {
        block.layout.hero_variant = resolveHeroVariant(block);
      }
      block.props = block.props || {};
      block.props.show_visual = block.layout.hero_variant !== 'centered';
    }
    if (block.type === 'media_text' && !block.props?.media_position) {
      block.props = { ...defaultProps.media_text, ...(block.props || {}) };
    }
  });
  indexBlockSections();
  applyLayoutToDom();

  if (new URLSearchParams(window.location.search).get('jcp_edit') === '1') {
    enableEditing();
  }
  if (new URLSearchParams(window.location.search).get('jcp_structure') === '1') {
    openStructure();
  }

  load().finally(() => {
    loaded = true;
    indexBlockSections();
    if (structureOpen) renderBlockList();
  });
})();
