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
    media_text: '.jcp-block-media-text, .jcp-media-text, .demo-preview-section',
    proof_flow: '.jcp-block-proof-flow, #real-job-proof',
    demo_preview: '.jcp-block-demo-preview, #demo-preview',
    directory_preview: '.jcp-block-directory-preview, .directory-preview',
    conversion: '.jcp-block-conversion, .conversion-section',
  };

  const HERO_VARIANTS = [
    { value: 'condensed', label: 'Condensed', hint: 'Internal page hero (recommended)' },
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
    core_mechanic: [
      { value: '1', label: 'photo', detail: 'Proof created instantly' },
      { value: '4', label: 'channels', detail: 'Google, website, social, directory' },
      { value: '0', label: 'busywork', detail: 'Nothing new for your crew' },
    ],
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
    media_text: {
      headline: 'Section headline',
      subheadline: '',
      body: 'Supporting copy for this section.',
      cta_primary: { label: '', url: '' },
      media_type: 'image',
      media_position: 'right',
      phone_mockup_style: 'app_shell',
      show_subheadline: true,
      show_body: true,
      show_cta: false,
      show_divider: false,
    },
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
    <button type="button" class="btn btn-secondary" id="jcpNicheTextLink" hidden>Add text link</button>
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

  const textLinkPopover = document.createElement('div');
  textLinkPopover.className = 'jcp-niche-link-popover jcp-niche-text-link-popover';
  textLinkPopover.hidden = true;
  textLinkPopover.innerHTML = `
    <label>Link URL (e.g. /industries/plumbing/)</label>
    <input type="text" id="jcpNicheTextLinkUrl" placeholder="/pricing" />
    <div class="jcp-niche-link-popover-actions">
      <button type="button" class="btn btn-primary" id="jcpNicheTextLinkApply">Apply link</button>
      <button type="button" class="btn btn-secondary" id="jcpNicheTextLinkCancel">Cancel</button>
    </div>
  `;

  document.body.appendChild(bar);
  document.body.appendChild(structurePanel);
  document.body.appendChild(addModal);
  document.body.appendChild(popover);
  document.body.appendChild(textLinkPopover);
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
  const textLinkBtn = bar.querySelector('#jcpNicheTextLink');
  let activeLink = null;
  let activeRichField = null;

  const isRichField = (el) => el && el.getAttribute('data-jcp-rich') === 'true';

  const sanitizeRichHtml = (html) => {
    const doc = new DOMParser().parseFromString(`<div>${html || ''}</div>`, 'text/html');
    const root = doc.body.firstElementChild;
    if (!root) return '';
    const walk = (node) => {
      if (node.nodeType === Node.TEXT_NODE) return node.textContent || '';
      if (node.nodeName === 'A') {
        const href = (node.getAttribute('href') || '#').replace(/"/g, '&quot;');
        return `<a href="${href}">${node.textContent || ''}</a>`;
      }
      return Array.from(node.childNodes).map(walk).join('');
    };
    return walk(root);
  };

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
    'jcp-hero-variant-condensed',
    'jcp-hero-variant-home',
    'jcp-block-cols-1',
    'jcp-block-cols-2',
    'jcp-block-cols-3',
    'jcp-block-cols-4',
  ];

  const COLUMN_GRID_SELECTORS = '.timeline-steps, .ranking-factors-grid, .guarantees-grid, .proof-flow';

  const columnGridTemplate = (cols) => {
    const count = parseInt(cols, 10);
    if (count >= 1 && count <= 4) return `repeat(${count}, minmax(0, 1fr))`;
    return '';
  };

  const findBlockRootEl = (block) => {
    const main = getMain();
    if (!main || !block?.id) return null;

    const byId = main.querySelector(`[data-jcp-block-id="${block.id}"]`);
    if (byId) return getBlockRoot(byId);

    const sel = BLOCK_SELECTORS[block.type];
    if (!sel) return null;

    const match = [...main.querySelectorAll(sel)].find((node) => {
      const root = getBlockRoot(node);
      return root && root.dataset.jcpBlockId === block.id;
    });
    return match ? getBlockRoot(match) : null;
  };

  const applyColumnGrids = (root, cols) => {
    if (!root) return;
    const template = columnGridTemplate(cols);
    root.querySelectorAll(COLUMN_GRID_SELECTORS).forEach((grid) => {
      if (template) {
        grid.style.setProperty('grid-template-columns', template, 'important');
      } else {
        grid.style.removeProperty('grid-template-columns');
      }
    });
  };

  const ensureBlockRoot = (root) => {
    if (!root) return null;
    root.classList.add('jcp-block-root');
    return root;
  };

  const defaultLayout = (type) => {
    if (type === 'hero') {
      if (PAGE_KIND === 'referral') return { hero_variant: 'centered' };
      if (PAGE_KIND === 'home') return { hero_variant: 'home' };
      return { hero_variant: 'condensed' };
    }
    const layout = { align: 'center', width: 'contained' };
    if (type === 'breadcrumb') layout.align = 'left';
    return layout;
  };

  const normalizePageDocumentBlocks = () => {
    if (!pageDocument || typeof pageDocument !== 'object') {
      pageDocument = { version: 1, blocks: [] };
      return;
    }
    if (!Array.isArray(pageDocument.blocks)) {
      pageDocument.blocks = [];
      return;
    }
    pageDocument.blocks = pageDocument.blocks.filter(
      (block) => block && typeof block === 'object' && block.type
    );
    pageDocument.blocks.forEach((block) => {
      if (!block.layout) block.layout = defaultLayout(block.type);
      if (block.type === 'conversion' && block.layout) {
        block.layout.align = 'left';
      }
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
  };

  const resolveHeroVariant = (block) => {
    const layout = { ...defaultLayout('hero'), ...(block.layout || {}) };
    const variant = layout.hero_variant;
    if (['split', 'centered', 'stacked', 'condensed', 'home'].includes(variant)) return variant;
    if (block.layout?.hero_visual === false || block.props?.show_visual === false) return 'centered';
    return 'split';
  };

  const resolveLayout = (block) => {
    if (block.type === 'hero') {
      return { hero_variant: resolveHeroVariant(block) };
    }
    const layout = { ...defaultLayout(block.type), ...(block.layout || {}) };
    if (block.type !== 'hero' && layout.columns === undefined) layout.columns = 0;
    return layout;
  };

  const layoutClassNames = (block) => {
    if (block.type === 'hero') {
      return `jcp-hero-variant-${resolveHeroVariant(block)}`;
    }
    const layout = resolveLayout(block);
    const classes = [
      `jcp-layout-align-${layout.align}`,
      `jcp-layout-width-${layout.width}`,
    ];
    const cols = Number(layout.columns || 0);
    if (cols >= 1 && cols <= 4) {
      classes.push(`jcp-block-cols-${cols}`);
    }
    return classes.join(' ');
  };

  const layoutOptionsFor = (type) => {
    const found = registry.find((b) => b.type === type);
    if (found?.layout_options) return found.layout_options;
    if (type === 'hero') {
      return { hero_variant: true, media_position: true };
    }
    if (type === 'media_text') {
      return { media_position: true, align: true, width: true };
    }
    if (type === 'demo_preview' || type === 'conversion') {
      return { media_position: true };
    }
    if (type === 'core_mechanic') {
      return {};
    }
    return { align: true, width: true };
  };

  const legacyKeyFor = (type) => {
    const found = registry.find((b) => b.type === type);
    return found?.legacy_key || type;
  };

  const blockLegacyKey = (block) => {
    if (!block) return '';
    if (block.legacy_key) return block.legacy_key;
    return legacyKeyFor(block.type);
  };

  const applyMediaPositionToDom = () => {
    document.querySelectorAll('[data-jcp-media-position-path]').forEach((grid) => {
      const path = grid.dataset.jcpMediaPositionPath;
      const pos = getPath(flatContent, path) === 'left' ? 'left' : 'right';
      grid.classList.remove('jcp-split-layout--media-left', 'jcp-split-layout--media-right');
      grid.classList.add(`jcp-split-layout--media-${pos}`);
      const section = grid.closest('.jcp-media-text, .demo-preview-section, .jcp-split-media-block');
      if (section) {
        section.classList.remove('jcp-media-text--media-left', 'jcp-media-text--media-right');
        section.classList.add(`jcp-media-text--media-${pos}`);
      }
    });
  };

  const applyLayoutToDom = () => {
    (pageDocument.blocks || []).forEach((block) => {
      if (!block || typeof block !== 'object') return;
      const root = ensureBlockRoot(findBlockRootEl(block));
      if (!root) return;

      if (block.type === 'hero') {
        const variant = resolveHeroVariant(block);
        ['split', 'centered', 'stacked', 'condensed', 'home'].forEach((v) => {
          root.classList.remove(`jcp-hero-variant-${v}`);
          root.querySelector('.jcp-niche-hero')?.classList.remove(`jcp-hero-variant-${v}`);
        });
        root.classList.add(`jcp-hero-variant-${variant}`);
        root.querySelector('.jcp-niche-hero')?.classList.add(`jcp-hero-variant-${variant}`);
        const visual = root.querySelector('.jcp-hero-visual');
        if (visual) visual.setAttribute('aria-hidden', variant === 'centered' ? 'true' : 'false');
        return;
      }

      LAYOUT_CLASS_NAMES.filter((cls) => cls.startsWith('jcp-layout-') || cls.startsWith('jcp-block-cols-')).forEach((cls) => root.classList.remove(cls));
      layoutClassNames(block).split(' ').filter(Boolean).forEach((cls) => root.classList.add(cls));
      applyColumnGrids(root, resolveLayout(block).columns);

      if (block.type === 'media_text') {
        const section = root.querySelector('.jcp-media-text') || root;
        section.classList.remove('jcp-media-text--media-left', 'jcp-media-text--media-right');
        const pos = block.props?.media_position === 'left' ? 'left' : 'right';
        section.classList.add(`jcp-media-text--media-${pos}`);
      }
    });
  };

  const setBlockLayout = (block, key, value) => {
    const liveBlock = (pageDocument.blocks || []).find((entry) => entry.id === block.id) || block;
    if (key === 'media_position') {
      liveBlock.props = liveBlock.props || {};
      liveBlock.props.media_position = value;
      const lk = blockLegacyKey(liveBlock);
      if (lk) setPath(flatContent, `${lk}.media_position`, value);
      applyMediaPositionToDom();
      renderBlockList();
      recordChange();
      return;
    }
    liveBlock.layout = { ...resolveLayout(liveBlock), [key]: value };
    if (key === 'columns') {
      liveBlock.layout.columns = parseInt(value, 10) || 0;
    }
    if (liveBlock.type === 'hero' && key === 'hero_variant') {
      liveBlock.props = liveBlock.props || {};
      liveBlock.props.show_visual = value !== 'centered';
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
      const variants = PAGE_KIND === 'home' ? HERO_VARIANTS : HERO_VARIANTS.filter((v) => v.value !== 'home');
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Hero style</span><div class="jcp-layout-btns jcp-layout-btns--stacked" data-setting="hero_variant">';
      variants.forEach((item) => {
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

    if (options.columns) {
      const cols = Number(layout.columns || 0);
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Columns</span><div class="jcp-layout-btns" data-setting="columns">';
      html += ['0', '1', '2', '3', '4'].map((value) => {
        const label = value === '0' ? 'Auto' : value;
        const active = String(cols) === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      html += '</div></div>';
    }

    if (block.type === 'hero') {
      block.props = block.props || {};
      const toggles = [
        { key: 'show_cta_primary', label: 'Primary button' },
        { key: 'show_cta_secondary', label: 'Secondary button' },
        { key: 'show_trust_line', label: 'Trust line' },
      ];
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Show</span><div class="jcp-layout-toggles">';
      toggles.forEach(({ key, label }) => {
        const checked = block.props[key] !== false ? ' checked' : '';
        html += `<label class="jcp-layout-toggle"><input type="checkbox" data-hero-toggle="${key}"${checked}> ${label}</label>`;
      });
      html += '</div></div>';
    }

    if (block.type === 'media_text' || block.type === 'demo_preview') {
      block.props = block.props || {};
      const toggles = [
        { key: 'show_badge', label: 'Badge' },
        { key: 'show_subheadline', label: 'Subheadline' },
        { key: 'show_cue', label: 'Lead line' },
        { key: 'show_body', label: 'Body' },
        { key: 'show_cta', label: 'Button' },
        { key: 'show_cta_note', label: 'Button note' },
      ];
      html += '<div class="jcp-layout-group"><span class="jcp-layout-group__label">Show</span><div class="jcp-layout-toggles">';
      toggles.forEach(({ key, label }) => {
        const checked = isSplitToggleOn(block, key) ? ' checked' : '';
        html += `<label class="jcp-layout-toggle"><input type="checkbox" data-split-toggle="${key}"${checked}> ${label}</label>`;
      });
      html += '</div></div>';
    }

    html += '</div>';
    return html;
  };

  const SPLIT_TOGGLE_DEFAULTS = {
    show_badge: false,
    show_subheadline: true,
    show_cue: false,
    show_body: true,
    show_cta: false,
    show_cta_note: false,
  };

  const isSplitToggleOn = (block, key) => {
    const val = block.props?.[key];
    if (val === true) return true;
    if (val === false) return false;
    return SPLIT_TOGGLE_DEFAULTS[key] ?? false;
  };

  const SPLIT_TOGGLE_SELECTORS = {
    show_badge: '.demo-badge',
    show_subheadline: '.jcp-split-subheadline',
    show_cue: '.demo-preview-cue',
    show_body: '.demo-preview-description',
    show_cta: '.demo-cta-primary',
    show_cta_note: '.demo-cta-note',
  };

  const setBlockSplitToggle = (block, key, enabled) => {
    block.props = block.props || {};
    block.props[key] = enabled;
    const lk = blockLegacyKey(block);
    if (lk) setPath(flatContent, `${lk}.${key}`, enabled);

    const root = document.querySelector(`[data-jcp-block-id="${block.id}"]`);
    const selector = SPLIT_TOGGLE_SELECTORS[key];
    if (root && selector) {
      root.querySelectorAll(selector).forEach((el) => {
        el.style.display = enabled ? '' : 'none';
      });
      if (key === 'show_cta' || key === 'show_cta_note') {
        const wrapper = root.querySelector('.demo-cta-wrapper');
        if (wrapper) {
          const showCta = isSplitToggleOn(block, 'show_cta');
          const showNote = isSplitToggleOn(block, 'show_cta_note');
          wrapper.style.display = (showCta || showNote) ? '' : 'none';
        }
      }
    }
    if (enabled && typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    recordChange();
  };

  const setBlockHeroToggle = (block, key, enabled) => {
    block.props = block.props || {};
    block.props[key] = enabled;
    const lk = blockLegacyKey(block);
    if (lk) setPath(flatContent, `${lk}.${key}`, enabled);

    const root = document.querySelector(`[data-jcp-block-id="${block.id}"]`);
    if (root) {
      if (key === 'show_cta_primary') {
        root.querySelector('.jcp-hero-primary-cta')?.style.setProperty('display', enabled ? '' : 'none');
      }
      if (key === 'show_cta_secondary') {
        root.querySelector('.jcp-actions .btn-secondary')?.style.setProperty('display', enabled ? '' : 'none');
      }
      if (key === 'show_trust_line') {
        root.querySelector('.jcp-niche-trust-line')?.style.setProperty('display', enabled ? '' : 'none');
      }
    }
    recordChange();
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
    syncListBlockPropsFromFlat();
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
    applyStructureToDom();
    if (typeof window.JCP_SYNC_COLLECTIONS_FROM_CONTENT === 'function') {
      window.JCP_SYNC_COLLECTIONS_FROM_CONTENT();
    }
    applyFlatContentToDom();
    applyMediaPositionToDom();
    renderBlockList();
    refreshEditorChrome();
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
      if (!block || typeof block !== 'object') return;
      if (assigned.has(block.id)) return;
      const sel = BLOCK_SELECTORS[block.type];
      if (!sel) return;
      const match = [...main.querySelectorAll(sel)].find((node) => {
        const root = getBlockRoot(node);
        return root && !root.dataset.jcpBlockId;
      });
      if (!match) return;
      const root = ensureBlockRoot(getBlockRoot(match));
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
      if (val === undefined || val === null) return;
      if (isRichField(el)) {
        el.innerHTML = sanitizeRichHtml(String(val));
      } else {
        el.textContent = isListLinePath(path) ? cleanStepLineText(String(val)) : String(val);
      }
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
      if (!block || typeof block !== 'object') return;
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
      li.querySelectorAll('[data-hero-toggle]').forEach((input) => {
        input.addEventListener('change', (e) => {
          e.stopPropagation();
          setBlockHeroToggle(block, input.dataset.heroToggle, input.checked);
        });
      });
      li.querySelectorAll('[data-split-toggle]').forEach((input) => {
        input.addEventListener('change', (e) => {
          e.stopPropagation();
          setBlockSplitToggle(block, input.dataset.splitToggle, input.checked);
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

  const refreshEditorChrome = () => {
    if (typeof window.JCP_REFRESH_PAGE_MEDIA_UI === 'function') {
      window.JCP_REFRESH_PAGE_MEDIA_UI();
    }
    if (typeof window.JCP_REFRESH_COLLECTIONS === 'function') {
      window.JCP_REFRESH_COLLECTIONS();
    }
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
  };

  const openStructure = () => {
    structureOpen = true;
    structurePanel.hidden = false;
    structurePanel.removeAttribute('hidden');
    document.body.classList.add('jcp-structure-open');
    renderBlockList();
    if (editing) refreshEditorChrome();
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
    normalizePageDocumentBlocks();
    sanitizeFlatContentInPlace();
    applyCleanLinesToDom();
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
      applyLayoutToDom();
      if (structureOpen) renderBlockList();
    } catch (err) {
      if (!registry.length) statusEl.textContent = 'Editor data unavailable — try refreshing';
    } finally {
      loaded = true;
    }
  };

  const cleanStepLineText = (text) => {
    let value = (text || '').trim();
    for (let i = 0; i < 3; i += 1) {
      value = value.replace(/(?:nttt)+x*\s*$/giu, '');
      value = value.replace(/[\s\u00D7]+$/gu, '');
      value = value.replace(/x\s*$/giu, '');
      value = value.trim();
    }
    return value;
  };

  const isListLinePath = (path) => /\.(?:lines\.\d+|team_already\.\d+|turns_into\.\d+|job_types\.\d+|bullets\.\d+|points\.\d+)$/.test(path || '');

  const sanitizeFlatContentInPlace = () => {
    const cleanList = (path) => {
      const arr = getPath(flatContent, path);
      if (!Array.isArray(arr)) return;
      setPath(flatContent, path, arr.map((line) => cleanStepLineText(String(line ?? ''))));
    };
    const steps = getPath(flatContent, 'how_it_works.steps');
    if (Array.isArray(steps)) {
      steps.forEach((step, index) => {
        if (!step || !Array.isArray(step.lines)) return;
        setPath(flatContent, `how_it_works.steps.${index}.lines`, step.lines.map((line) => cleanStepLineText(String(line ?? ''))));
      });
    }
    ['what_it_is.team_already', 'what_it_is.turns_into', 'check_ins.job_types', 'differentiation.bullets', 'conversion.points'].forEach(cleanList);
  };

  const applyCleanLinesToDom = () => {
    document.querySelectorAll('.jcp-step-checklist__text[data-jcp-path], .jcp-checklist-item__text[data-jcp-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-path');
      const stored = path ? getPath(flatContent, path) : undefined;
      const value = stored !== undefined && stored !== null ? String(stored) : (el.textContent || '');
      el.textContent = cleanStepLineText(value);
    });
  };

  const isStringArrayItemPath = (el) => {
    const item = el.closest('[data-jcp-array-item]');
    const container = item?.parentElement;
    if (!item || !container?.matches('[data-jcp-array]')) return false;
    const basePath = container.dataset.jcpArray;
    const path = el.getAttribute('data-jcp-path');
    if (!basePath || !path?.startsWith(`${basePath}.`)) return false;
    return /^\d+$/.test(path.slice(basePath.length + 1));
  };

  const isObjectArrayItemPath = (el) => {
    const path = el.getAttribute('data-jcp-path');
    if (!path) return false;
    return /^core_mechanic\.\d+\.(?:value|label|detail)$/.test(path);
  };

  const collectObjectArraysFromDom = () => {
    document.querySelectorAll('[data-jcp-array="core_mechanic"]').forEach((container) => {
      const basePath = 'core_mechanic';
      const items = [...container.querySelectorAll(':scope > [data-jcp-array-item]')];
      const arr = items.map((item) => {
        const index = item.getAttribute('data-jcp-array-item');
        const readField = (field) => {
          const el = item.querySelector(`[data-jcp-path="${basePath}.${index}.${field}"]`);
          return (el?.textContent || '').trim();
        };
        const prev = getPath(flatContent, `${basePath}.${index}`) || {};
        const value = readField('value');
        const label = readField('label');
        const detail = readField('detail');
        return {
          ...(typeof prev === 'object' && prev ? prev : {}),
          value,
          label,
          detail,
        };
      });
      setPath(flatContent, basePath, arr);
    });
  };

  const syncListBlockPropsFromFlat = () => {
    (pageDocument.blocks || []).forEach((block) => {
      if (!block || block.type !== 'core_mechanic') return;
      const key = blockLegacyKey(block);
      if (!key || !Array.isArray(flatContent[key])) return;
      block.props = JSON.parse(JSON.stringify(flatContent[key]));
    });
  };

  const collectStringArraysFromDom = () => {
    const readArrayItemText = (item) => {
      const textEl = item.querySelector('.jcp-step-checklist__text, .jcp-checklist-item__text');
      const el = textEl || (item.hasAttribute('data-jcp-path') ? item : item.querySelector('[data-jcp-path]'));
      if (!el) return '';
      return cleanStepLineText((el.textContent || '').trim());
    };

    document.querySelectorAll('[data-jcp-array]').forEach((container) => {
      const basePath = container.dataset.jcpArray;
      if (!basePath || basePath === 'core_mechanic') return;
      const items = [...container.querySelectorAll(':scope > [data-jcp-array-item]')];
      if (!items.length) {
        setPath(flatContent, basePath, []);
        return;
      }
      const firstPathEl = items[0].hasAttribute('data-jcp-path')
        ? items[0]
        : items[0].querySelector('[data-jcp-path]');
      const samplePath = firstPathEl?.getAttribute('data-jcp-path') || '';
      if (!samplePath.startsWith(`${basePath}.`)) return;
      const suffix = samplePath.slice(basePath.length + 1);
      if (!/^\d+$/.test(suffix)) return;

      const arr = items.map((item) => readArrayItemText(item));
      setPath(flatContent, basePath, arr);
    });
  };

  const collectFromDom = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      if (isStringArrayItemPath(el) || isObjectArrayItemPath(el)) return;
      const path = el.getAttribute('data-jcp-path');
      if (!path) return;
      if (isRichField(el)) {
        setPath(flatContent, path, sanitizeRichHtml(el.innerHTML));
        return;
      }
      const raw = (el.textContent || '').trim();
      const value = isListLinePath(path) ? cleanStepLineText(raw) : raw;
      setPath(flatContent, path, value);
    });
    document.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-href-path');
      if (!path) return;
      setPath(flatContent, path, el.getAttribute('href') || '');
    });
    collectObjectArraysFromDom();
    collectStringArraysFromDom();
  };

  const bindEditableFields = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      if (el.closest('.jcp-collection-add, .jcp-collection-remove, .jcp-optional-restore')) return;
      if (editing) {
        el.setAttribute('contenteditable', 'true');
        el.setAttribute('spellcheck', 'true');
      } else {
        el.removeAttribute('contenteditable');
        el.removeAttribute('spellcheck');
      }
    });
  };

  const enableEditing = () => {
    editing = true;
    document.body.classList.add('jcp-inline-editing');
    toggleBtn.textContent = 'Editing — click text to change';
    toggleBtn.classList.add('is-active');
    if (textLinkBtn) textLinkBtn.hidden = false;
    if (!dirty) statusEl.textContent = 'Click text or images to edit. Select text and use Add text link for internal links.';
    bindEditableFields();
    applyCleanLinesToDom();
    refreshEditorChrome();
    requestAnimationFrame(refreshEditorChrome);
  };

  const disableEditing = () => {
    editing = false;
    document.body.classList.remove('jcp-inline-editing');
    toggleBtn.textContent = 'Click to edit page';
    toggleBtn.classList.remove('is-active');
    if (textLinkBtn) textLinkBtn.hidden = true;
    popover.hidden = true;
    textLinkPopover.hidden = true;
    if (typeof window.JCP_TEARDOWN_COLLECTIONS === 'function') {
      window.JCP_TEARDOWN_COLLECTIONS();
    }
    bindEditableFields();
  };

  window.JCP_REFRESH_INLINE_EDITABLE = () => {
    if (editing) bindEditableFields();
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

  const openTextLinkPopover = () => {
    const sel = window.getSelection();
    if (!sel || !sel.rangeCount) return;
    const node = sel.anchorNode;
    const field = node && node.nodeType === Node.ELEMENT_NODE
      ? node.closest('[data-jcp-rich="true"]')
      : node?.parentElement?.closest('[data-jcp-rich="true"]');
    if (!field) {
      statusEl.textContent = 'Select text inside a paragraph first (subheadlines, FAQ answers, etc.).';
      return;
    }
    activeRichField = field;
    textLinkPopover.hidden = false;
    textLinkPopover.removeAttribute('hidden');
    textLinkPopover.querySelector('#jcpNicheTextLinkUrl').value = '';
    const rect = field.getBoundingClientRect();
    textLinkPopover.style.top = `${Math.min(window.innerHeight - 140, rect.bottom + 8)}px`;
    textLinkPopover.style.left = `${Math.max(8, Math.min(window.innerWidth - 320, rect.left))}px`;
  };

  if (textLinkBtn) {
    textLinkBtn.addEventListener('click', openTextLinkPopover);
  }

  textLinkPopover.querySelector('#jcpNicheTextLinkApply').addEventListener('click', () => {
    const url = textLinkPopover.querySelector('#jcpNicheTextLinkUrl').value.trim();
    if (!url || !activeRichField) return;
    const sel = window.getSelection();
    if (!sel || !sel.rangeCount) return;
    const range = sel.getRangeAt(0);
    if (!activeRichField.contains(range.commonAncestorContainer)) return;
    const label = sel.toString() || url;
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.textContent = label;
    range.deleteContents();
    range.insertNode(anchor);
    textLinkPopover.hidden = true;
    textLinkPopover.setAttribute('hidden', '');
    activeRichField = null;
    recordChange();
  });

  textLinkPopover.querySelector('#jcpNicheTextLinkCancel').addEventListener('click', () => {
    textLinkPopover.hidden = true;
    textLinkPopover.setAttribute('hidden', '');
    activeRichField = null;
  });

  document.addEventListener('keydown', (e) => {
    if (!editing) return;
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      openTextLinkPopover();
    }
  });

  saveBtn.addEventListener('click', async () => {
    if (saveBtn.disabled) return;
    collectFromDom();
    syncListBlockPropsFromFlat();
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
  normalizePageDocumentBlocks();
  sanitizeFlatContentInPlace();
  indexBlockSections();
  applyCleanLinesToDom();
  applyLayoutToDom();
  applyMediaPositionToDom();

  const editorApi = {
    getPath,
    setPath,
    recordChange,
    collectFromDom,
    registry,
    applyMediaPositionToDom,
    editing: () => editing,
    strings: cfg.strings || {},
    get flatContent() { return flatContent; },
    get pageDocument() { return pageDocument; },
  };
  window.__JCP_EDITOR_API__ = editorApi;

  const initSubEditors = () => {
    if (typeof window.JCP_INIT_PAGE_MEDIA_EDITOR === 'function') {
      window.JCP_INIT_PAGE_MEDIA_EDITOR(editorApi);
    }
    if (typeof window.JCP_INIT_COLLECTION_EDITOR === 'function') {
      window.JCP_INIT_COLLECTION_EDITOR(editorApi);
    }
  };
  initSubEditors();

  if (new URLSearchParams(window.location.search).get('jcp_edit') === '1') {
    enableEditing();
  }
  if (new URLSearchParams(window.location.search).get('jcp_structure') === '1') {
    openStructure();
  }

  load().finally(() => {
    loaded = true;
    sanitizeFlatContentInPlace();
    indexBlockSections();
    applyCleanLinesToDom();
    applyMediaPositionToDom();
    initSubEditors();
    if (editing) {
      refreshEditorChrome();
      requestAnimationFrame(refreshEditorChrome);
    }
    if (structureOpen) renderBlockList();
  });
})();
