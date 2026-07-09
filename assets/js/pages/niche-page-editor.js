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

  const SECTION_SURFACE_PRESETS = [
    { value: 'default', label: 'Default', hint: 'Keep the site gradient' },
    { value: 'white', label: 'White', swatch: '#ffffff' },
    { value: 'off_white', label: 'Cream', swatch: '#f6f5f2' },
    { value: 'dark', label: 'Brand', swatch: 'brand' },
    { value: 'image', label: 'Photo', swatch: 'image' },
    { value: 'custom', label: 'Custom', swatch: 'custom' },
  ];

  const SECTION_SURFACE_CLASS_NAMES = [
    'jcp-has-section-surface',
    'jcp-section-surface--white',
    'jcp-section-surface--off_white',
    'jcp-section-surface--dark',
    'jcp-section-surface--image',
    'jcp-section-surface--custom',
  ];

  const SECTION_CTA_PRIMARY_SELECTOR = '.jcp-section-cta-row [data-jcp-optional$=".cta_primary"]';
  const SECTION_CTA_SECONDARY_SELECTOR = '.jcp-section-cta-row [data-jcp-optional$=".cta_secondary"]';

  const SECTION_CTA_PRIMARY_DEFAULTS = {
    what_it_is: { label: 'Learn more', url: '/demo' },
    how_it_works: { label: 'See it in action', url: '/demo' },
    check_ins: { label: 'See it in action', url: '/demo' },
    problem: { label: 'Fix this with JobCapturePro', url: '/demo' },
    benefits: { label: 'See it in the demo', url: '/demo' },
    differentiation: { label: 'Get started', url: '/demo' },
    who_its_for: { label: 'Start free trial', url: '/demo' },
    faq: { label: 'Still have questions? Book a demo', url: '/demo' },
  };

  const SECTION_CTA_SECONDARY_DEFAULTS = {
    how_it_works: { label: 'View pricing', url: '/pricing' },
    benefits: { label: 'Learn more', url: '/pricing' },
    what_it_is: { label: 'See how it works', url: '#how-it-works' },
  };

  const syncOptionalCtaSlotsFromContent = () => {
    if (typeof window.JCP_SYNC_COLLECTIONS_FROM_CONTENT === 'function') {
      window.JCP_SYNC_COLLECTIONS_FROM_CONTENT();
    }
    if (typeof window.JCP_REFRESH_COLLECTIONS === 'function') {
      window.JCP_REFRESH_COLLECTIONS();
    }
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
  };

  const BLOCK_VISIBILITY_TOGGLES = {
    hero: [
      { key: 'show_subheadline', label: 'Subheadline', selector: '.jcp-hero-subtitle', defaultOn: true },
      { key: 'show_trust_line', label: 'Trust line', selector: '.jcp-niche-trust-line', defaultOn: true },
      { key: 'show_cta_primary', label: 'Primary button', selector: '.jcp-hero-primary-cta', defaultOn: true },
      { key: 'show_cta_secondary', label: 'Secondary button', selector: '.jcp-actions .btn-secondary', defaultOn: true },
      { key: 'show_meta_stats', label: 'Proof stats', selector: '.directory-meta', defaultOn: true },
    ],
    what_it_is: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_icons', label: 'Icons', selector: '.factor-icon-wrapper', defaultOn: true },
      { key: 'show_closing', label: 'Closing line', selector: '.jcp-niche-section-closing', defaultOn: true },
      { key: 'show_cta', label: 'Primary button', selector: SECTION_CTA_PRIMARY_SELECTOR, defaultOn: false },
      { key: 'show_cta_secondary', label: 'Secondary link', selector: SECTION_CTA_SECONDARY_SELECTOR, defaultOn: false },
    ],
    how_it_works: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_steps', label: 'Step cards', selector: '.timeline-steps', defaultOn: true },
      { key: 'show_cta', label: 'Primary button', selector: SECTION_CTA_PRIMARY_SELECTOR, defaultOn: true },
      { key: 'show_cta_secondary', label: 'Secondary link', selector: SECTION_CTA_SECONDARY_SELECTOR, defaultOn: false },
    ],
    check_ins: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_tags', label: 'Job tags', selector: '.jcp-niche-tags-wrap', defaultOn: true },
      { key: 'show_icons', label: 'Icons', selector: '.factor-icon-wrapper', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.jcp-section-cta-row', defaultOn: false },
    ],
    problem: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_icons', label: 'Icons', selector: '.factor-icon-wrapper', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.jcp-section-cta-row', defaultOn: false },
    ],
    benefits: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_icons', label: 'Icons', selector: '.factor-icon-wrapper', defaultOn: true },
      { key: 'show_closing', label: 'Closing line', selector: '.jcp-niche-section-closing', defaultOn: true },
      { key: 'show_cta', label: 'Primary button', selector: SECTION_CTA_PRIMARY_SELECTOR, defaultOn: false },
      { key: 'show_cta_secondary', label: 'Secondary link', selector: SECTION_CTA_SECONDARY_SELECTOR, defaultOn: false },
    ],
    differentiation: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.jcp-niche-diff-lead', defaultOn: true },
      { key: 'show_icons', label: 'Checkmarks', selector: '.conversion-point-icon', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.jcp-section-cta-row', defaultOn: false },
    ],
    who_its_for: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_icons', label: 'Icons', selector: '.factor-icon-wrapper', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.jcp-section-cta-row', defaultOn: false },
    ],
    faq: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_items', label: 'FAQ items', selector: '.faq-grid', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.jcp-section-cta-row', defaultOn: false },
    ],
    final_cta: [
      { key: 'show_headline', label: 'Headline', selector: '.cta-content h3', defaultOn: true },
      { key: 'show_subheadline', label: 'Supporting text', selector: '.cta-paragraph', defaultOn: true },
      { key: 'show_cta', label: 'Button', selector: '.rankings-cta-btn, .cta-button-wrapper .jcp-optional-restore', defaultOn: true },
      { key: 'show_cta_note', label: 'Text under button', selector: '.cta-note', defaultOn: true },
    ],
    conversion: [
      { key: 'show_headline', label: 'Headline', selector: '.conversion-content .rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.conversion-content .rankings-subtitle', defaultOn: true },
      { key: 'show_points', label: 'Checklist', selector: '.conversion-points', defaultOn: true },
      { key: 'show_media', label: 'Side image', selector: '.conversion-visual', defaultOn: true },
      { key: 'show_stats', label: 'Stat badges', selector: '.conversion-stats', defaultOn: true },
      { key: 'show_cta', label: 'Button', selector: '.conversion-cta', defaultOn: true },
    ],
    proof_flow: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_items', label: 'Channel items', selector: '.proof-flow', defaultOn: true },
      { key: 'show_callout', label: 'Callout box', selector: '.real-job-proof-callout', defaultOn: true },
      { key: 'show_link', label: 'Bottom link', selector: '.timeline-cta', defaultOn: true },
    ],
    directory_preview: [
      { key: 'show_headline', label: 'Headline', selector: '.rankings-header h2', defaultOn: true },
      { key: 'show_subheadline', label: 'Subheadline', selector: '.rankings-subtitle', defaultOn: true },
      { key: 'show_cards', label: 'Directory cards', selector: '.directory-grid', defaultOn: true },
      { key: 'show_outro', label: 'Outro line', selector: '.directory-preview-outro', defaultOn: true },
      { key: 'show_cta', label: 'Section button', selector: '.directory-preview-cta', defaultOn: false },
    ],
    core_mechanic: [
      { key: 'show_stats', label: 'Stat row', selector: '.jcp-core-mechanic-meta, .directory-meta', defaultOn: true },
    ],
    cta_band: [
      { key: 'show_cta_primary', label: 'Button', selector: '.jcp-niche-cta-band .btn-primary', defaultOn: true },
    ],
  };

  let flatContent = bootstrap.content && typeof bootstrap.content === 'object' ? bootstrap.content : {};
  let pageDocument = bootstrap.blocks && Array.isArray(bootstrap.blocks.blocks)
    ? bootstrap.blocks
    : { version: 1, blocks: [] };
  let registry = Array.isArray(bootstrap.registry) ? bootstrap.registry : [];
  const linkIndex = bootstrap.linkIndex && typeof bootstrap.linkIndex === 'object'
    ? bootstrap.linkIndex
    : { pages: [], current_path: '' };
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
  const structureTabByBlockId = new Map();
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
      cta_note: 'No signup required • Takes 2 minutes',
      show_headline: true,
      show_body: true,
      show_cta: true,
      show_cta_note: true,
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
    <div class="jcp-niche-edit-bar__start">
      <strong class="jcp-niche-edit-bar-title">Page editor</strong>
      <span id="jcpNicheStatus" class="jcp-niche-edit-status" aria-live="polite"></span>
    </div>
    <div class="jcp-niche-edit-bar__actions">
      <button type="button" class="btn btn-secondary jcp-niche-edit-bar__icon-btn" id="jcpNicheUndo" disabled aria-label="Undo" title="Undo">↶</button>
      <button type="button" class="btn btn-secondary jcp-niche-edit-bar__icon-btn" id="jcpNicheRedo" disabled aria-label="Redo" title="Redo">↷</button>
      <button type="button" class="btn btn-secondary" id="jcpNicheStructureBtn">Structure</button>
      <button type="button" class="btn btn-secondary" id="jcpNicheTextLink" hidden>Link</button>
      <button type="button" class="btn btn-primary" id="jcpNicheToggleEdit">Edit page</button>
      <button type="button" class="btn btn-secondary" id="jcpNicheSave" disabled aria-label="Save changes">Save</button>
    </div>
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
    <p class="jcp-block-structure__hint">Drag sections to reorder. Click ⚙ to adjust layout, background, and visibility.</p>
    <div class="jcp-block-structure__page-options">
      <span class="jcp-layout-group__label">Page</span>
      <button type="button" class="jcp-layout-chip" id="jcpToggleBreadcrumb" title="Show breadcrumb trail in the hero">Breadcrumb</button>
    </div>
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
  popover.className = 'jcp-editor-modal jcp-cta-link-modal';
  popover.hidden = true;
  popover.innerHTML = `
    <button type="button" class="jcp-editor-modal__backdrop" aria-label="Close"></button>
    <div class="jcp-editor-modal__panel jcp-niche-link-popover" role="dialog" aria-labelledby="jcpCtaLinkModalTitle">
      <strong id="jcpCtaLinkModalTitle">Button link</strong>
      <label>URL</label>
      <input type="text" id="jcpNicheLinkUrl" placeholder="/demo or https://..." />
      <div class="jcp-niche-link-popover-actions">
        <button type="button" class="btn btn-primary" id="jcpNicheLinkApply">Apply</button>
        <button type="button" class="btn btn-secondary" id="jcpNicheLinkCancel">Cancel</button>
      </div>
    </div>
  `;

  const textLinkPopover = document.createElement('div');
  textLinkPopover.className = 'jcp-editor-modal jcp-text-link-modal';
  textLinkPopover.hidden = true;
  textLinkPopover.innerHTML = `
    <button type="button" class="jcp-editor-modal__backdrop" aria-label="Close"></button>
    <div class="jcp-editor-modal__panel jcp-niche-link-popover jcp-niche-text-link-popover" role="dialog" aria-labelledby="jcpTextLinkModalTitle">
      <div class="jcp-niche-link-popover__header">
        <strong id="jcpTextLinkModalTitle">Internal link</strong>
        <div class="jcp-niche-link-popover__hint" id="jcpNicheTextLinkHint"></div>
      </div>

      <div class="jcp-niche-link-popover__seo" id="jcpNicheLinkSeo"></div>

      <div class="jcp-niche-link-popover__suggestions">
        <div class="jcp-niche-link-popover__suggestions-title">Suggested pages</div>
        <div class="jcp-niche-link-popover__suggestions-list" id="jcpNicheLinkSuggestions"></div>
      </div>

      <label>Link URL</label>
      <input type="text" id="jcpNicheTextLinkUrl" placeholder="/industries/plumbing/" />

      <div class="jcp-niche-link-popover-actions">
        <button type="button" class="btn btn-primary" id="jcpNicheTextLinkApply">Insert link</button>
        <button type="button" class="btn btn-secondary" id="jcpNicheTextLinkCancel">Close</button>
      </div>
    </div>
  `;

  const iconPopover = document.createElement('div');
  iconPopover.className = 'jcp-editor-modal jcp-icon-picker-modal';
  iconPopover.hidden = true;
  iconPopover.innerHTML = `
    <button type="button" class="jcp-editor-modal__backdrop" aria-label="Close"></button>
    <div class="jcp-editor-modal__panel jcp-niche-icon-popover" role="dialog" aria-labelledby="jcpIconPickerTitle">
      <strong id="jcpIconPickerTitle">Choose icon</strong>
      <p class="jcp-niche-icon-popover__hint">Applies to the card you clicked. Use “Icons” in Show on page to hide all icons in this section.</p>
      <div class="jcp-niche-icon-popover__grid" id="jcpNicheIconGrid"></div>
      <button type="button" class="btn btn-secondary" id="jcpNicheIconCancel">Close</button>
    </div>
  `;

  document.body.appendChild(bar);
  document.body.appendChild(structurePanel);
  document.body.appendChild(addModal);
  document.body.appendChild(popover);
  document.body.appendChild(textLinkPopover);
  document.body.appendChild(iconPopover);
  document.body.classList.add('jcp-niche-editing');

  const statusEl = bar.querySelector('#jcpNicheStatus');
  const saveBtn = bar.querySelector('#jcpNicheSave');
  const undoBtn = bar.querySelector('#jcpNicheUndo');
  const redoBtn = bar.querySelector('#jcpNicheRedo');
  const toggleBtn = bar.querySelector('#jcpNicheToggleEdit');
  const structureBtn = bar.querySelector('#jcpNicheStructureBtn');
  const blockListEl = structurePanel.querySelector('#jcpBlockList');
  const breadcrumbToggleBtn = structurePanel.querySelector('#jcpToggleBreadcrumb');
  const addBlockListEl = addModal.querySelector('#jcpAddBlockList');
  const adminLink = bar.querySelector('.jcp-niche-edit-link');
  const textLinkBtn = bar.querySelector('#jcpNicheTextLink');
  let activeLink = null;
  let activeRichField = null;
  let activeBlockId = null;

  const openEditorModal = (modalEl, { focusSelector } = {}) => {
    if (!modalEl) return;
    (document.documentElement || document.body).appendChild(modalEl);
    modalEl.hidden = false;
    modalEl.removeAttribute('hidden');
    document.body.classList.add('jcp-editor-modal-open');
    document.documentElement.classList.add('jcp-editor-modal-open');
    document.querySelectorAll('.jcp-editor-modal').forEach((el) => {
      if (el !== modalEl) {
        el.hidden = true;
        el.setAttribute('hidden', '');
      }
    });
    const focusEl = focusSelector ? modalEl.querySelector(focusSelector) : null;
    if (focusEl) {
      requestAnimationFrame(() => focusEl.focus());
    }
  };

  const closeEditorModal = (modalEl) => {
    if (!modalEl) return;
    modalEl.hidden = true;
    modalEl.setAttribute('hidden', '');
    if (!document.querySelector('.jcp-editor-modal:not([hidden])')) {
      document.body.classList.remove('jcp-editor-modal-open');
      document.documentElement.classList.remove('jcp-editor-modal-open');
    }
  };

  const closeCtaLinkModal = () => {
    closeEditorModal(popover);
    activeLink = null;
  };

  const closeTextLinkModal = () => {
    closeEditorModal(textLinkPopover);
    activeRichField = null;
  };

  const ICON_CHOICES = [
    'badge-check', 'map-pin', 'star', 'phone', 'building-2', 'message-square',
    'camera', 'clock', 'earth', 'share-2', 'users', 'briefcase', 'hard-hat',
    'trending-up', 'shield-check', 'wrench', 'zap', 'target', 'heart',
    'sparkles', 'image-off', 'circle-alert', 'circle-check',
  ];

  const iconGridEl = iconPopover.querySelector('#jcpNicheIconGrid');
  const resolveIconAssetBase = () => {
    if (window.JCP_ASSET_BASE) return window.JCP_ASSET_BASE;
    if (cfg.assetBase) return cfg.assetBase;
    const src = document.currentScript?.src
      || document.querySelector('script[src*="niche-page-editor"]')?.src
      || '';
    if (src.includes('/js/pages/')) return src.split('/js/pages/')[0];
    return '';
  };
  if (!window.JCP_ASSET_BASE) {
    window.JCP_ASSET_BASE = resolveIconAssetBase();
  }
  const iconAssetBase = () => window.JCP_ASSET_BASE || resolveIconAssetBase();
  const iconUrl = (name) => `${iconAssetBase()}/shared/assets/icons/lucide/${name}.svg`;
  let activeIconTarget = null;

  ICON_CHOICES.forEach((name) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jcp-niche-icon-popover__btn';
    btn.dataset.iconName = name;
    btn.title = name;
    btn.innerHTML = `<img src="${iconUrl(name)}" alt="" width="20" height="20" />`;
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      if (!activeIconTarget) return;
      const path = activeIconTarget.dataset.jcpIconPath;
      if (!path) return;
      setPath(flatContent, path, name);
      const blockRoot = activeIconTarget.closest('[data-jcp-block-id]');
      const blockId = blockRoot?.dataset?.jcpBlockId;
      const block = (pageDocument.blocks || []).find((entry) => entry.id === blockId);
      if (block) {
        block.props = block.props || {};
        const lk = blockLegacyKey(block);
        const relPath = lk && path.startsWith(`${lk}.`) ? path.slice(lk.length + 1) : path;
        setPath(block.props, relPath, name);
      }
      const img = activeIconTarget.querySelector('.factor-icon, .meta-icon');
      if (img) img.src = iconUrl(name);
      closeIconPicker();
      recordChange();
    });
    iconGridEl.appendChild(btn);
  });

  const closeIconPicker = () => {
    closeEditorModal(iconPopover);
    activeIconTarget = null;
  };

  const openIconPicker = (wrapper) => {
    activeIconTarget = wrapper;
    openEditorModal(iconPopover);
  };

  document.addEventListener('mousedown', (e) => {
    if (!editing) return;
    const wrapper = e.target.closest('[data-jcp-icon-path]');
    if (!wrapper) return;
    e.preventDefault();
    e.stopPropagation();
    openIconPicker(wrapper);
  }, true);

  document.addEventListener('keydown', (e) => {
    if (!editing || e.key !== 'Enter' && e.key !== ' ') return;
    const wrapper = e.target.closest('[data-jcp-icon-path]');
    if (!wrapper) return;
    e.preventDefault();
    openIconPicker(wrapper);
  }, true);

  iconPopover.querySelector('.jcp-editor-modal__backdrop').addEventListener('click', closeIconPicker);
  iconPopover.querySelector('#jcpNicheIconCancel').addEventListener('click', closeIconPicker);

  const isRichField = (el) => el && el.getAttribute('data-jcp-rich') === 'true';

  const isLinkableTextField = (el) => {
    if (!el?.hasAttribute?.('data-jcp-path')) return false;
    if (el.hasAttribute('data-jcp-href-path')) return false;
    if (el.closest('.jcp-collection-add, .jcp-collection-remove, .jcp-optional-restore')) return false;
    const tag = el.tagName;
    if (['BUTTON', 'INPUT', 'TEXTAREA', 'SELECT'].includes(tag)) return false;
    return true;
  };

  const findLinkableFieldFromNode = (node) => {
    if (!node) return null;
    const start = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
    if (!start) return null;
    let el = start;
    while (el) {
      if (el.matches?.('[data-jcp-path]') && isLinkableTextField(el)) return el;
      el = el.parentElement;
    }
    return null;
  };

  const promoteFieldToRichIfNeeded = (el) => {
    if (!el || isRichField(el)) return;
    if (/<a[\s>]/i.test(el.innerHTML || '')) {
      el.setAttribute('data-jcp-rich', 'true');
    }
  };

  let pendingLinkRange = null;
  let pendingLinkField = null;

  const rememberLinkSelection = () => {
    if (!editing) return;
    const sel = window.getSelection();
    if (!sel?.rangeCount) return;
    const range = sel.getRangeAt(0);
    if (range.collapsed) return;
    const field = findLinkableFieldFromNode(range.commonAncestorContainer);
    if (!field) return;
    try {
      pendingLinkRange = range.cloneRange();
      pendingLinkField = field;
    } catch (e) {
      // ignore range clone errors
    }
  };

  const getLinkContext = () => {
    const sel = window.getSelection();
    let range = null;
    let field = null;

    if (sel?.rangeCount && !sel.isCollapsed) {
      range = sel.getRangeAt(0);
      field = findLinkableFieldFromNode(range.commonAncestorContainer);
    }

    if ((!field || !range || range.collapsed) && pendingLinkField && pendingLinkRange) {
      range = pendingLinkRange;
      field = pendingLinkField;
    }

    if (!field) {
      field = findLinkableFieldFromNode(sel?.anchorNode)
        || findLinkableFieldFromNode(document.activeElement);
    }

    return { range, field };
  };

  const markExistingInlineLinks = () => {
    document.querySelectorAll('[data-jcp-path]').forEach((el) => {
      if (!isLinkableTextField(el)) return;
      promoteFieldToRichIfNeeded(el);
    });
  };

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

  const focusStructureBlock = (block, { scrollPage = false, expand = true } = {}) => {
    if (!block?.id) return;
    activeBlockId = block.id;
    if (!structureOpen) openStructure();

    blockListEl.querySelectorAll('.jcp-block-structure__item').forEach((el) => {
      const isTarget = el.dataset.blockId === block.id;
      el.classList.toggle('is-active', isTarget);
      const layout = el.querySelector('.jcp-block-structure__layout');
      if (!layout) return;
      if (isTarget && expand) {
        layout.classList.remove('is-collapsed');
      } else if (!isTarget) {
        layout.classList.add('is-collapsed');
      }
      if (isTarget) {
        el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }
    });

    document.querySelectorAll('[data-jcp-block-id]').forEach((el) => {
      el.classList.toggle('jcp-block-is-selected', el.dataset.jcpBlockId === block.id);
    });

    if (!scrollPage) return;
    const main = getMain();
    const el = main?.querySelector(`[data-jcp-block-id="${block.id}"]`);
    if (!el) return;
    const barEl = document.querySelector('.jcp-niche-edit-bar');
    const offset = (barEl?.offsetHeight || 0) + 16;
    const top = el.getBoundingClientRect().top + window.scrollY - offset;
    window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    el.classList.add('jcp-block-scroll-target');
    window.setTimeout(() => el.classList.remove('jcp-block-scroll-target'), 1500);
  };

  const scrollToBlock = (block) => {
    focusStructureBlock(block, { scrollPage: true, expand: true });
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

  const SECTION_SURFACE_DEFAULTS = {
    preset: 'default',
    color: '#ffffff',
    opacity: 100,
    image_url: '',
  };

  const defaultLayout = (type) => {
    const surface = { ...SECTION_SURFACE_DEFAULTS };
    if (type === 'hero') {
      if (PAGE_KIND === 'referral') return { hero_variant: 'centered', align: 'center', section_surface: surface };
      if (PAGE_KIND === 'home') return { hero_variant: 'home', align: 'center', section_surface: surface };
      return { hero_variant: 'condensed', align: 'center', section_surface: surface };
    }
    const layout = {
      align: 'center',
      width: 'contained',
      section_surface: surface,
    };
    if (type === 'breadcrumb') layout.align = 'left';
    return layout;
  };

  const isHeroVisualOn = (block) => {
    block.props = block.props || {};
    if (block.props.show_visual === true || block.props.show_visual === false) {
      return block.props.show_visual === true;
    }
    return resolveHeroVariant(block) !== 'centered';
  };

  const heroMediaMode = (block) => {
    if (!isHeroVisualOn(block)) return 'hide';
    return block.props?.media_position === 'left' ? 'left' : 'right';
  };

  const getLiveBlock = (block) => (pageDocument.blocks || []).find((entry) => entry.id === block.id) || block;

  const isHeroFieldOn = (block, key, defaultOn = true) => {
    const liveBlock = getLiveBlock(block);
    if (liveBlock.props && Object.prototype.hasOwnProperty.call(liveBlock.props, key)) {
      if (liveBlock.props[key] === true || liveBlock.props[key] === false) {
        return liveBlock.props[key] !== false;
      }
    }
    const lk = blockLegacyKey(liveBlock);
    if (lk) {
      const val = getPath(flatContent, `${lk}.${key}`);
      if (val === true || val === false) return val !== false;
    }
    return defaultOn;
  };

  const resolveSectionSurface = (block) => {
    const layout = block.layout || {};
    return { ...SECTION_SURFACE_DEFAULTS, ...(layout.section_surface || {}) };
  };

  const isBlockFieldVisible = (block, key, defaultOn = true) => {
    if (block.props && (block.props[key] === true || block.props[key] === false)) {
      return block.props[key] === true;
    }
    const lk = blockLegacyKey(block);
    if (lk) {
      const val = getPath(flatContent, `${lk}.${key}`);
      if (val === true || val === false) return val === true;
    }
    return defaultOn;
  };

  const applyIconVisibilityToRoot = (root, enabled) => {
    if (!root) return;
    root.querySelectorAll('section').forEach((section) => {
      section.classList.toggle('jcp-section--no-icons', !enabled);
    });
  };

  const ensureSectionCtaRow = (root, block) => {
    if (!root || !block) return;
    const lk = blockLegacyKey(block);
    if (!lk) return;
    const host = root.querySelector('.jcp-container') || root;
    let row = root.querySelector('.jcp-section-cta-row');
    if (!row) {
      row = document.createElement('div');
      row.className = 'jcp-section-cta-row benefits-cta-row jcp-section-cta-row--solo';
      host.appendChild(row);
    }
    let slot = row.querySelector('[data-jcp-optional$=".cta_primary"]');
    if (!slot) {
      slot = document.createElement('div');
      slot.className = 'benefits-cta-slot jcp-section-cta-slot';
      slot.dataset.jcpOptional = `${lk}.cta_primary`;
      slot.dataset.jcpOptionalKind = 'cta';
      slot.dataset.jcpOptionalLabel = 'Section button';
      row.appendChild(slot);
    }
  };

  const setBlockFieldVisible = (block, key, enabled, selector) => {
    const liveBlock = getLiveBlock(block);
    liveBlock.props = liveBlock.props || {};
    liveBlock.props[key] = enabled;
    const lk = blockLegacyKey(liveBlock);
    if (lk) setPath(flatContent, `${lk}.${key}`, enabled);

    const root = ensureBlockRoot(findBlockRootEl(liveBlock));
    if (key === 'show_cta' && enabled && lk) {
      const primaryPath = `${lk}.cta_primary`;
      const primary = getPath(flatContent, primaryPath);
      if (!primary || !String(primary.label || '').trim()) {
        const defaults = SECTION_CTA_PRIMARY_DEFAULTS[lk] || { label: 'Learn more', url: '/demo' };
        setPath(flatContent, primaryPath, { ...defaults });
        liveBlock.props.cta_primary = { ...defaults };
      }
      if (root) ensureSectionCtaRow(root, liveBlock);
      syncOptionalCtaSlotsFromContent();
    }

    if (key === 'show_cta_secondary' && enabled && lk) {
      const secPath = `${lk}.cta_secondary`;
      const sec = getPath(flatContent, secPath);
      if (!sec || !String(sec.label || '').trim()) {
        const defaults = SECTION_CTA_SECONDARY_DEFAULTS[lk] || { label: 'Learn more', url: '/pricing' };
        setPath(flatContent, secPath, { ...defaults });
        liveBlock.props.cta_secondary = { ...defaults };
      }
      if (root) ensureSectionCtaRow(root, liveBlock);
      syncOptionalCtaSlotsFromContent();
    }

    syncBlockVisibilityToDom(liveBlock);
    recordChange();
  };

  const clearSectionSurfaceEl = (el) => {
    if (!el) return;
    el.classList.remove(...SECTION_SURFACE_CLASS_NAMES);
    el.removeAttribute('data-jcp-surface');
    el.removeAttribute('data-jcp-surface-color');
    el.removeAttribute('data-jcp-surface-opacity');
    el.removeAttribute('data-jcp-surface-image');
    el.style.removeProperty('--jcp-section-bg-color');
    el.style.removeProperty('--jcp-section-bg-opacity');
    el.style.removeProperty('--jcp-section-bg-image');
  };

  const applySectionSurfaceToDom = (block, root) => {
    if (!root || block.type === 'breadcrumb') return;
    const surface = resolveSectionSurface(block);
    const preset = surface.preset || 'default';
    clearSectionSurfaceEl(root);
    if (preset === 'default') return;

    root.classList.add('jcp-has-section-surface', `jcp-section-surface--${preset}`);
    root.dataset.jcpSurface = preset;
    root.dataset.jcpSurfaceOpacity = String(surface.opacity ?? 100);
    const alpha = Math.max(0, Math.min(100, Number(surface.opacity ?? 100))) / 100;
    root.style.setProperty('--jcp-section-bg-opacity', String(alpha));
    if (preset === 'custom') {
      const color = surface.color || '#ffffff';
      root.dataset.jcpSurfaceColor = color;
      root.style.setProperty('--jcp-section-bg-color', color);
    }
    if (preset === 'image') {
      if (surface.image_url) {
        root.dataset.jcpSurfaceImage = surface.image_url;
        root.style.setProperty('--jcp-section-bg-image', `url(${surface.image_url})`);
      }
    }
  };

  const setSectionSurface = (block, patch, { refreshList = false } = {}) => {
    const liveBlock = (pageDocument.blocks || []).find((entry) => entry.id === block.id) || block;
    liveBlock.layout = liveBlock.layout || defaultLayout(liveBlock.type);
    liveBlock.layout.section_surface = {
      ...resolveSectionSurface(liveBlock),
      ...patch,
    };
    const root = document.querySelector(`[data-jcp-block-id="${block.id}"]`);
    applySectionSurfaceToDom(liveBlock, root);
    if (refreshList) {
      renderBlockList();
    } else {
      refreshSectionSurfaceControls(liveBlock);
    }
    recordChange();
  };

  const refreshSectionSurfaceControls = (block) => {
    const li = blockListEl.querySelector(`[data-block-id="${block.id}"]`);
    if (!li) return;
    const backgroundPanel = li.querySelector('[data-structure-panel="background"]');
    if (!backgroundPanel) return;
    backgroundPanel.innerHTML = buildSectionSurfaceHtml(block);
    bindSectionSurfaceControls(li, block);
  };

  const bindSectionSurfaceControls = (li, block) => {
    li.querySelectorAll('[data-section-surface-preset]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        setSectionSurface(block, { preset: btn.dataset.sectionSurfacePreset });
      });
    });
    li.querySelectorAll('[data-section-surface-color]').forEach((input) => {
      input.addEventListener('input', (e) => {
        e.stopPropagation();
        setSectionSurface(block, { color: input.value }, { refreshList: false });
        const hex = li.querySelector('[data-section-surface-color-hex]');
        if (hex) hex.value = input.value;
      });
    });
    li.querySelectorAll('[data-section-surface-color-hex]').forEach((input) => {
      input.addEventListener('change', (e) => {
        e.stopPropagation();
        const val = input.value.trim();
        if (!/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(val)) return;
        setSectionSurface(block, { color: val }, { refreshList: false });
        const picker = li.querySelector('[data-section-surface-color]');
        if (picker) picker.value = val;
      });
    });
    li.querySelectorAll('[data-section-surface-opacity]').forEach((input) => {
      input.addEventListener('input', (e) => {
        e.stopPropagation();
        const opacity = parseInt(input.value, 10) || 0;
        const valEl = input.closest('.jcp-surface-opacity')?.querySelector('.jcp-surface-opacity__val');
        if (valEl) valEl.textContent = `${opacity}%`;
        setSectionSurface(block, { opacity }, { refreshList: false });
      });
    });
    li.querySelectorAll('[data-section-surface-image]').forEach((input) => {
      input.addEventListener('change', (e) => {
        e.stopPropagation();
        setSectionSurface(block, { image_url: input.value.trim() }, { refreshList: false });
        refreshSectionSurfaceControls(block);
      });
    });
    li.querySelector('[data-section-surface-pick]')?.addEventListener('click', (e) => {
      e.stopPropagation();
      openSurfaceImagePicker(block);
    });
    li.querySelectorAll('[data-section-surface-clear]').forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        setSectionSurface(block, { image_url: '' }, { refreshList: false });
        refreshSectionSurfaceControls(block);
      });
    });
  };

  const isBreadcrumbVisible = () => {
    if (pageDocument.settings && typeof pageDocument.settings.hide_breadcrumb === 'boolean') {
      return !pageDocument.settings.hide_breadcrumb;
    }
    return !flatContent.hide_breadcrumb;
  };

  const setBreadcrumbVisible = (visible) => {
    pageDocument.settings = pageDocument.settings || {};
    pageDocument.settings.hide_breadcrumb = !visible;
    flatContent.hide_breadcrumb = !visible;
    document.querySelectorAll('.jcp-niche-breadcrumb').forEach((el) => {
      el.style.display = visible ? '' : 'none';
    });
    recordChange();
  };

  const buildStructureSection = (title, body, { extraClass = '' } = {}) => {
    if (!body || !String(body).trim()) return '';
    return `<div class="jcp-structure-section${extraClass ? ` ${extraClass}` : ''}">
      <div class="jcp-structure-section__title">${title}</div>
      <div class="jcp-structure-section__body">${body}</div>
    </div>`;
  };

  const buildSectionSurfaceHtml = (block) => {
    if (block.type === 'breadcrumb') return '';
    const surface = resolveSectionSurface(block);
    const preset = surface.preset || 'default';

    let html = '<div class="jcp-surface-swatches" data-setting="section_surface_preset">';
    SECTION_SURFACE_PRESETS.forEach((item) => {
      const active = preset === item.value ? ' is-active' : '';
      const swatchClass = item.swatch ? ` jcp-surface-swatch--${item.swatch === 'brand' ? 'brand' : item.swatch === 'image' ? 'image' : item.swatch === 'custom' ? 'custom' : 'solid'}` : ' jcp-surface-swatch--default';
      const swatchStyle = item.swatch && !['brand', 'image', 'custom'].includes(item.swatch) ? ` style="--jcp-swatch-color:${item.swatch}"` : '';
      html += `<button type="button" class="jcp-surface-swatch${swatchClass}${active}" data-section-surface-preset="${item.value}" title="${item.hint || item.label}" aria-label="${item.label}">
        <span class="jcp-surface-swatch__chip"${swatchStyle}></span>
        <span class="jcp-surface-swatch__label">${item.label}</span>
      </button>`;
    });
    html += '</div>';

    if (['white', 'off_white', 'dark', 'custom'].includes(preset)) {
      html += `<div class="jcp-surface-advanced">
        <label class="jcp-surface-opacity">
          <span>Opacity</span>
          <input type="range" min="0" max="100" step="5" value="${Number(surface.opacity ?? 100)}" data-section-surface-opacity />
          <strong class="jcp-surface-opacity__val">${Number(surface.opacity ?? 100)}%</strong>
        </label>`;
      if (preset === 'custom') {
        html += `<label class="jcp-surface-color">
          <span>Color</span>
          <input type="color" class="jcp-surface-color__picker" data-section-surface-color value="${surface.color || '#ffffff'}" />
          <input type="text" class="jcp-surface-color__hex" data-section-surface-color-hex value="${surface.color || '#ffffff'}" maxlength="7" />
        </label>`;
      }
      html += '</div>';
    }

    if (preset === 'image') {
      const imageUrl = surface.image_url || '';
      const previewStyle = imageUrl ? ` style="background-image:url('${imageUrl.replace(/'/g, '%27')}')"` : '';
      html += `<div class="jcp-surface-image-card">
        <div class="jcp-surface-image-card__preview"${previewStyle}>
          <span class="jcp-surface-image-card__placeholder">${imageUrl ? '' : 'No image selected'}</span>
        </div>
        <div class="jcp-surface-image-card__actions">
          <button type="button" class="jcp-surface-image-card__btn" data-section-surface-pick>${imageUrl ? 'Change photo' : 'Choose photo'}</button>
          ${imageUrl ? '<button type="button" class="jcp-surface-image-card__btn jcp-surface-image-card__btn--ghost" data-section-surface-clear>Remove</button>' : ''}
        </div>
        <input type="hidden" data-section-surface-image value="${imageUrl}" />
      </div>`;
    }

    return html;
  };

  const buildBlockVisibilityHtml = (block) => {
    const toggles = [...(BLOCK_VISIBILITY_TOGGLES[block.type] || [])];
    if (block.type === 'media_text' || block.type === 'demo_preview') {
      toggles.unshift(
        { key: 'show_headline', label: 'Headline', selector: '.jcp-split-headline, .demo-preview-headline, h2' },
      );
      toggles.push(
        { key: 'show_badge', label: 'Badge' },
        { key: 'show_subheadline', label: 'Subheadline' },
        { key: 'show_cue', label: 'Lead' },
        { key: 'show_body', label: 'Body' },
        { key: 'show_cta', label: 'Button' },
        { key: 'show_cta_note', label: 'Note' },
      );
    }
    if (!toggles.length) return '';
    let html = '<div class="jcp-layout-chips">';
    toggles.forEach(({ key, label, defaultOn }) => {
      let on = false;
      if (block.type === 'media_text' || block.type === 'demo_preview') {
        on = isSplitToggleOn(block, key);
      } else if (block.type === 'hero') {
        on = isHeroFieldOn(block, key, defaultOn !== false);
      } else {
        on = isBlockFieldVisible(block, key, defaultOn !== false);
      }
      html += `<button type="button" class="jcp-layout-chip${on ? ' is-on' : ''}" data-block-field-toggle="${key}"${(block.type === 'media_text' || block.type === 'demo_preview') ? ' data-split-toggle="1"' : ''}>${label}</button>`;
    });
    html += '</div>';
    return html;
  };

  const syncBlockVisibilityToDom = (block) => {
    const toggles = [...(BLOCK_VISIBILITY_TOGGLES[block.type] || [])];
    if (block.type === 'media_text' || block.type === 'demo_preview') {
      toggles.unshift({ key: 'show_headline', label: 'Headline', selector: '.jcp-split-headline, .demo-preview-headline, h2' });
      toggles.push(
        { key: 'show_badge', selector: '.demo-badge' },
        { key: 'show_subheadline', selector: '.jcp-split-subheadline' },
        { key: 'show_cue', selector: '.demo-preview-cue' },
        { key: 'show_body', selector: '.demo-preview-description, .jcp-media-text-body' },
      );
    }
    if (!toggles.length) return;
    const root = ensureBlockRoot(findBlockRootEl(block));
    if (!root) return;
    toggles.forEach(({ key, selector, defaultOn }) => {
      if (!selector) return;
      let enabled = isBlockFieldVisible(block, key, defaultOn !== false);
      if (block.type === 'media_text' || block.type === 'demo_preview') {
        if (['show_badge', 'show_subheadline', 'show_cue', 'show_body'].includes(key)) {
          enabled = isSplitToggleOn(block, key);
        }
      }
      if (block.type === 'hero') {
        enabled = isHeroFieldOn(block, key, defaultOn !== false);
      }
      root.querySelectorAll(selector).forEach((el) => {
        el.style.display = enabled ? '' : 'none';
      });
      if (block.type === 'hero' && (key === 'show_cta_primary' || key === 'show_cta_secondary')) {
        const showPrimary = isHeroFieldOn(block, 'show_cta_primary', true);
        const showSecondary = isHeroFieldOn(block, 'show_cta_secondary', true);
        const actions = root.querySelector('.jcp-actions');
        if (actions) actions.style.display = (showPrimary || showSecondary) ? '' : 'none';
      }
      if (key === 'show_icons') {
        applyIconVisibilityToRoot(root, enabled);
      }
      if (key === 'show_cta' || key === 'show_cta_secondary') {
        const showPrimary = key === 'show_cta'
          ? enabled
          : isBlockFieldVisible(block, 'show_cta', (BLOCK_VISIBILITY_TOGGLES[block.type] || []).find((e) => e.key === 'show_cta')?.defaultOn !== false);
        const showSecondary = key === 'show_cta_secondary'
          ? enabled
          : isBlockFieldVisible(block, 'show_cta_secondary', (BLOCK_VISIBILITY_TOGGLES[block.type] || []).find((e) => e.key === 'show_cta_secondary')?.defaultOn !== false);
        const row = root.querySelector('.jcp-section-cta-row');
        if (row) row.style.display = (showPrimary || showSecondary) ? '' : 'none';
        if (showPrimary) {
          root.querySelectorAll(SECTION_CTA_PRIMARY_SELECTOR).forEach((el) => {
            el.style.display = '';
          });
        }
      }
    });
  };

  let surfaceImageFrame = null;
  const openSurfaceImagePicker = (block) => {
    if (!window.wp?.media) {
      window.alert('Media library is not available. Try refreshing the page.');
      return;
    }
    if (!surfaceImageFrame) {
      surfaceImageFrame = window.wp.media({
        title: 'Section background image',
        button: { text: 'Use image' },
        multiple: false,
        library: { type: 'image' },
      });
      surfaceImageFrame.on('select', () => {
        const targetBlock = surfaceImageFrame._jcpSurfaceBlock;
        if (!targetBlock) return;
        const attachment = surfaceImageFrame.state().get('selection').first().toJSON();
        const url = attachment.url || '';
        if (url) setSectionSurface(targetBlock, { image_url: url });
      });
    }
    surfaceImageFrame._jcpSurfaceBlock = block;
    surfaceImageFrame.open();
  };

  const syncBreadcrumbToggleUi = () => {
    if (!breadcrumbToggleBtn) return;
    breadcrumbToggleBtn.classList.toggle('is-on', isBreadcrumbVisible());
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
        if (!block.layout.align) {
          block.layout.align = PAGE_KIND === 'referral' ? 'center' : 'center';
        }
        block.props = block.props || {};
        if (block.props.show_visual !== true && block.props.show_visual !== false) {
          block.props.show_visual = block.layout.hero_variant !== 'centered';
        }
      }
      if (block.type === 'media_text' && !block.props?.media_position) {
        block.props = { ...defaultProps.media_text, ...(block.props || {}) };
      }
      block.layout.section_surface = {
        ...SECTION_SURFACE_DEFAULTS,
        ...(block.layout.section_surface || {}),
      };
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
      const layout = { ...defaultLayout('hero'), ...(block.layout || {}) };
      const align = ['left', 'center', 'right'].includes(layout.align) ? layout.align : 'center';
      return { hero_variant: resolveHeroVariant(block), align };
    }
    const layout = { ...defaultLayout(block.type), ...(block.layout || {}) };
    if (block.type !== 'hero' && layout.columns === undefined) layout.columns = 0;
    return layout;
  };

  const layoutClassNames = (block) => {
    if (block.type === 'hero') {
      const layout = resolveLayout(block);
      return `jcp-hero-variant-${resolveHeroVariant(block)} jcp-layout-align-${layout.align}`;
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
      return { hero_variant: true, media_position: true, align: true };
    }
    if (type === 'media_text' || type === 'demo_preview' || type === 'conversion') {
      return { media_position: true, align: true, width: true };
    }
    if (type === 'core_mechanic' || type === 'breadcrumb') {
      return type === 'core_mechanic' ? { align: true, width: true } : {};
    }
    const columnTypes = [
      'how_it_works', 'check_ins', 'problem', 'benefits', 'who_its_for', 'proof_flow',
      'what_it_is', 'differentiation', 'faq', 'directory_preview',
    ];
    const options = { align: true, width: true };
    if (columnTypes.includes(type)) options.columns = true;
    return options;
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
        const layout = resolveLayout(block);
        ['split', 'centered', 'stacked', 'condensed', 'home'].forEach((v) => {
          root.classList.remove(`jcp-hero-variant-${v}`);
          root.querySelector('.jcp-niche-hero')?.classList.remove(`jcp-hero-variant-${v}`);
        });
        ['left', 'center', 'right'].forEach((a) => {
          root.classList.remove(`jcp-layout-align-${a}`);
          root.querySelector('.jcp-niche-hero')?.classList.remove(`jcp-layout-align-${a}`);
        });
        root.classList.remove('jcp-hero-has-visual', 'jcp-hero--no-visual');
        root.classList.add(`jcp-hero-variant-${variant}`, `jcp-layout-align-${layout.align}`);
        const heroSection = root.querySelector('.jcp-niche-hero') || root;
        heroSection.classList.remove('jcp-hero-variant-split', 'jcp-hero-variant-centered', 'jcp-hero-variant-stacked', 'jcp-hero-variant-condensed', 'jcp-hero-variant-home');
        heroSection.classList.remove('jcp-layout-align-left', 'jcp-layout-align-center', 'jcp-layout-align-right');
        heroSection.classList.add(`jcp-hero-variant-${variant}`, `jcp-layout-align-${layout.align}`);
        heroSection.classList.toggle('jcp-niche-hero--internal', variant === 'condensed');
        heroSection.classList.toggle('jcp-niche-hero--condensed', variant === 'condensed');
        heroSection.classList.toggle('jcp-hero-has-visual', isHeroVisualOn(block));
        heroSection.classList.toggle('jcp-hero--no-visual', !isHeroVisualOn(block));
        root.classList.toggle('jcp-hero-has-visual', isHeroVisualOn(block));
        root.classList.toggle('jcp-hero--no-visual', !isHeroVisualOn(block));
        const visualCol = root.querySelector('.jcp-hero-visual-column');
        if (visualCol) {
          visualCol.style.display = isHeroVisualOn(block) ? '' : 'none';
          visualCol.setAttribute('aria-hidden', isHeroVisualOn(block) ? 'false' : 'true');
        }
        const grid = root.querySelector('[data-jcp-media-position-path="hero.media_position"]');
        if (grid) {
          const pos = block.props?.media_position === 'left' ? 'left' : 'right';
          grid.classList.remove('jcp-split-layout--media-left', 'jcp-split-layout--media-right');
          grid.classList.add(`jcp-split-layout--media-${pos}`);
        }
        const showPrimary = isHeroFieldOn(block, 'show_cta_primary', true);
        const showSecondary = isHeroFieldOn(block, 'show_cta_secondary', true);
        const actions = root.querySelector('.jcp-actions');
        if (actions) actions.style.display = (showPrimary || showSecondary) ? '' : 'none';
        root.querySelector('.jcp-hero-primary-cta')?.style.setProperty('display', showPrimary ? '' : 'none');
        root.querySelector('.jcp-actions .btn-secondary')?.style.setProperty('display', showSecondary ? '' : 'none');
      } else {
        LAYOUT_CLASS_NAMES.filter((cls) => cls.startsWith('jcp-layout-') || cls.startsWith('jcp-block-cols-')).forEach((cls) => root.classList.remove(cls));
        layoutClassNames(block).split(' ').filter(Boolean).forEach((cls) => root.classList.add(cls));
        applyColumnGrids(root, resolveLayout(block).columns);

        if (block.type === 'media_text') {
          const section = root.querySelector('.jcp-media-text') || root;
          section.classList.remove('jcp-media-text--media-left', 'jcp-media-text--media-right');
          const pos = block.props?.media_position === 'left' ? 'left' : 'right';
          section.classList.add(`jcp-media-text--media-${pos}`);
        }
      }

      applySectionSurfaceToDom(block, root);
      syncBlockVisibilityToDom(block);
    });

    document.querySelectorAll('.jcp-niche-breadcrumb').forEach((el) => {
      el.style.display = isBreadcrumbVisible() ? '' : 'none';
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
      const lk = blockLegacyKey(liveBlock) || 'hero';
      if (value === 'centered') {
        liveBlock.props.show_visual = false;
        liveBlock.layout.align = 'center';
        setPath(flatContent, `${lk}.show_visual`, false);
      } else if (value === 'stacked') {
        liveBlock.props.show_visual = true;
        if (!['left', 'center', 'right'].includes(liveBlock.layout?.align)) {
          liveBlock.layout.align = 'center';
        }
        setPath(flatContent, `${lk}.show_visual`, true);
      } else if (value === 'split') {
        liveBlock.props.show_visual = true;
        setPath(flatContent, `${lk}.show_visual`, true);
      } else if (value === 'condensed') {
        if (liveBlock.props.show_visual !== true && liveBlock.props.show_visual !== false) {
          liveBlock.props.show_visual = false;
          setPath(flatContent, `${lk}.show_visual`, false);
        }
        if (!liveBlock.layout?.align) {
          liveBlock.layout.align = 'left';
        }
      }
    }
    if (liveBlock.type === 'hero' && key === 'align') {
      liveBlock.layout = { ...resolveLayout(liveBlock), align: value };
      applyLayoutToDom();
      renderBlockList();
      recordChange();
      return;
    }
    applyLayoutToDom();
    renderBlockList();
    recordChange();
  };

  const buildLayoutControlsHtml = (block) => {
    const layout = resolveLayout(block);
    const options = layoutOptionsFor(block.type);
    let layoutBody = '';

    if (block.type === 'hero') {
      block.props = block.props || {};
      const variant = resolveHeroVariant(block);
      const heroLayout = resolveLayout(block);
      const align = heroLayout.align || (PAGE_KIND === 'home' ? 'left' : 'center');
      const variants = PAGE_KIND === 'home'
        ? HERO_VARIANTS.filter((v) => v.value === 'home')
        : HERO_VARIANTS.filter((v) => v.value !== 'home');

      if (options.hero_variant && variants.length) {
        layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Hero style</span><div class="jcp-layout-btns jcp-layout-btns--stacked" data-setting="hero_variant">';
        variants.forEach((item) => {
          const active = variant === item.value ? ' is-active' : '';
          layoutBody += `<button type="button" class="jcp-layout-btn jcp-layout-btn--variant${active}" data-value="${item.value}" title="${item.hint}">${item.label}</button>`;
        });
        layoutBody += '</div></div>';
      }

      layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Text align</span><div class="jcp-layout-btns" data-setting="align">';
      layoutBody += ['left', 'center', 'right'].map((value) => {
        const label = value === 'left' ? 'Left' : value === 'center' ? 'Center' : 'Right';
        const active = align === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      layoutBody += '</div></div>';

      const mediaMode = heroMediaMode(block);
      layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Media</span><div class="jcp-layout-btns" data-setting="hero_media_mode">';
      layoutBody += ['hide', 'left', 'right'].map((value) => {
        const label = value === 'hide' ? 'Hide' : value === 'left' ? 'Left' : 'Right';
        const active = mediaMode === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-hero-media-mode="${value}">${label}</button>`;
      }).join('');
      layoutBody += '</div></div>';
    }

    if (options.media_position && block.type !== 'hero') {
      const pos = block.props?.media_position === 'left' ? 'left' : 'right';
      const mediaLabel = block.type === 'conversion' ? 'Image side' : 'Media side';
      layoutBody += `<div class="jcp-layout-row"><span class="jcp-layout-row__label">${mediaLabel}</span><div class="jcp-layout-btns" data-setting="media_position">`;
      layoutBody += `<button type="button" class="jcp-layout-btn${pos === 'left' ? ' is-active' : ''}" data-value="left">Left</button>`;
      layoutBody += `<button type="button" class="jcp-layout-btn${pos === 'right' ? ' is-active' : ''}" data-value="right">Right</button>`;
      layoutBody += '</div></div>';
    }

    if (options.align && block.type !== 'hero') {
      layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Align</span><div class="jcp-layout-btns" data-setting="align">';
      layoutBody += ['left', 'center', 'right'].map((value) => {
        const label = value === 'left' ? 'Left' : value === 'center' ? 'Center' : 'Right';
        const active = layout.align === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      layoutBody += '</div></div>';
    }

    if (options.width) {
      layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Width</span><div class="jcp-layout-btns" data-setting="width">';
      layoutBody += ['contained', 'wide', 'full'].map((value) => {
        const label = value === 'contained' ? 'Box' : value === 'wide' ? 'Wide' : 'Full';
        const active = layout.width === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      layoutBody += '</div></div>';
    }

    if (options.columns) {
      const cols = Number(layout.columns || 0);
      layoutBody += '<div class="jcp-layout-row"><span class="jcp-layout-row__label">Columns</span><div class="jcp-layout-btns" data-setting="columns">';
      layoutBody += ['0', '1', '2', '3', '4'].map((value) => {
        const label = value === '0' ? 'Auto' : value;
        const active = String(cols) === value ? ' is-active' : '';
        return `<button type="button" class="jcp-layout-btn${active}" data-value="${value}">${label}</button>`;
      }).join('');
      layoutBody += '</div></div>';
    }

    const backgroundBody = block.type !== 'breadcrumb' ? buildSectionSurfaceHtml(block) : '';
    const visibilityBody = buildBlockVisibilityHtml(block);
    const activeTab = structureTabByBlockId.get(block.id) || 'layout';
    const tabButtons = [];
    const tabPanels = [];
    const addTab = (id, label, body) => {
      if (!body || !String(body).trim()) return;
      const isActive = id === activeTab;
      tabButtons.push(`<button type="button" class="jcp-structure-tab${isActive ? ' is-active' : ''}" data-structure-tab="${id}" role="tab">${label}</button>`);
      tabPanels.push(`<div class="jcp-structure-panel${isActive ? ' is-active' : ''}" data-structure-panel="${id}" role="tabpanel">${body}</div>`);
    };
    addTab('layout', 'Layout', layoutBody);
    addTab('background', 'Background', backgroundBody);
    addTab('visibility', 'Show', visibilityBody);

    if (!tabButtons.length) return '';
    return `<div class="jcp-block-structure__layout is-collapsed">
      <div class="jcp-structure-tabs" role="tablist">${tabButtons.join('')}</div>
      <div class="jcp-structure-panels">${tabPanels.join('')}</div>
    </div>`;
  };

  const SPLIT_TOGGLE_DEFAULTS = {
    show_headline: true,
    show_badge: false,
    show_subheadline: true,
    show_cue: false,
    show_body: true,
    show_cta: false,
    show_cta_note: true,
  };

  const SPLIT_TOGGLE_KEYS = [
    'show_headline',
    'show_badge',
    'show_subheadline',
    'show_cue',
    'show_body',
    'show_cta',
    'show_cta_note',
  ];

  const isSplitToggleOn = (block, key) => {
    if (block.props && (block.props[key] === true || block.props[key] === false)) {
      return block.props[key] === true;
    }
    const lk = blockLegacyKey(block);
    if (lk) {
      const val = getPath(flatContent, `${lk}.${key}`);
      if (val === true || val === false) return val === true;
    }
    return SPLIT_TOGGLE_DEFAULTS[key] ?? false;
  };

  const syncSplitBlockPropsFromFlat = (block) => {
    const lk = blockLegacyKey(block);
    if (!lk) return;
    block.props = block.props || {};
    SPLIT_TOGGLE_KEYS.forEach((key) => {
      const val = getPath(flatContent, `${lk}.${key}`);
      if (val === true || val === false) block.props[key] = val;
    });
  };

  const syncSplitTogglesToDom = (onlyBlock = null) => {
    const blocks = onlyBlock ? [onlyBlock] : (pageDocument.blocks || []);
    blocks.forEach((block) => {
      if (!block || (block.type !== 'media_text' && block.type !== 'demo_preview')) return;
      syncSplitBlockPropsFromFlat(block);
      const root = ensureBlockRoot(findBlockRootEl(block));
      if (!root) return;

      SPLIT_TOGGLE_KEYS.forEach((key) => {
        if (key === 'show_cta' || key === 'show_cta_note') return;
        const selector = SPLIT_TOGGLE_SELECTORS[key];
        if (!selector) return;
        const enabled = isSplitToggleOn(block, key);
        root.querySelectorAll(selector).forEach((el) => {
          el.style.display = enabled ? '' : 'none';
        });
      });

      const showCta = isSplitToggleOn(block, 'show_cta');
      const showNote = isSplitToggleOn(block, 'show_cta_note');
      const wrapper = root.querySelector('.demo-cta-wrapper');
      if (wrapper) wrapper.style.display = (showCta || showNote) ? '' : 'none';
      const slot = root.querySelector('.demo-cta-slot');
      if (slot) slot.style.display = showCta ? '' : 'none';
      root.querySelectorAll('.demo-cta-note').forEach((el) => {
        el.style.display = showNote ? '' : 'none';
      });
    });
  };

  const SPLIT_TOGGLE_SELECTORS = {
    show_headline: '.demo-preview-title',
    show_badge: '.demo-badge',
    show_subheadline: '.jcp-split-subheadline',
    show_cue: '.demo-preview-cue',
    show_body: '.demo-preview-description',
    show_cta: '.demo-cta-slot',
    show_cta_note: '.demo-cta-note',
  };

  const ensureSplitCtaSlot = (root, block) => {
    if (!root || !block) return;
    const lk = blockLegacyKey(block);
    if (!lk) return;
    let wrapper = root.querySelector('.demo-cta-wrapper');
    if (!wrapper) return;
    let slot = root.querySelector('.demo-cta-slot');
    if (!slot) {
      slot = document.createElement('div');
      slot.className = 'demo-cta-slot benefits-cta-slot';
      slot.dataset.jcpOptional = `${lk}.cta_primary`;
      slot.dataset.jcpOptionalKind = 'cta';
      slot.dataset.jcpOptionalLabel = 'Button';
      wrapper.insertBefore(slot, wrapper.querySelector('.demo-cta-note'));
    }
    if (!slot.querySelector('.demo-cta-primary, .jcp-optional-restore') && isSplitToggleOn(block, 'show_cta')) {
      if (typeof window.JCP_REFRESH_COLLECTIONS === 'function') {
        window.JCP_REFRESH_COLLECTIONS();
      }
    }
  };

  const setBlockSplitToggle = (block, key, enabled) => {
    const liveBlock = getLiveBlock(block);
    liveBlock.props = liveBlock.props || {};
    liveBlock.props[key] = enabled;
    const lk = blockLegacyKey(liveBlock);
    if (lk) setPath(flatContent, `${lk}.${key}`, enabled);

    const root = ensureBlockRoot(findBlockRootEl(liveBlock));
    if (key === 'show_cta' && enabled && lk) {
      const ctaPath = `${lk}.cta_primary`;
      const cta = getPath(flatContent, ctaPath);
      if (!cta || !String(cta.label || '').trim()) {
        setPath(flatContent, ctaPath, {
          label: liveBlock.type === 'demo_preview' ? 'Launch Interactive Demo' : 'See it in action',
          url: '/demo',
        });
      }
      if (root) ensureSplitCtaSlot(root, liveBlock);
    }

    syncSplitTogglesToDom(liveBlock);

    if (enabled && (key === 'show_cta' || key === 'show_cta_note')) {
      if (typeof window.JCP_SYNC_COLLECTIONS_FROM_CONTENT === 'function') {
        window.JCP_SYNC_COLLECTIONS_FROM_CONTENT();
      }
      if (typeof window.JCP_REFRESH_COLLECTIONS === 'function') {
        window.JCP_REFRESH_COLLECTIONS();
      }
    }
    if (enabled && typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    recordChange();
  };

  const setBlockHeroToggle = (block, key, enabled) => {
    const liveBlock = getLiveBlock(block);
    liveBlock.props = liveBlock.props || {};
    liveBlock.props[key] = enabled;
    const lk = blockLegacyKey(liveBlock);
    if (lk) setPath(flatContent, `${lk}.${key}`, enabled);
    applyLayoutToDom();
    recordChange();
  };

  const setHeroMediaMode = (block, mode) => {
    const liveBlock = (pageDocument.blocks || []).find((entry) => entry.id === block.id) || block;
    liveBlock.props = liveBlock.props || {};
    const lk = blockLegacyKey(liveBlock) || 'hero';
    if (mode === 'hide') {
      liveBlock.props.show_visual = false;
      setPath(flatContent, `${lk}.show_visual`, false);
    } else {
      liveBlock.props.show_visual = true;
      liveBlock.props.media_position = mode;
      setPath(flatContent, `${lk}.show_visual`, true);
      setPath(flatContent, `${lk}.media_position`, mode);
    }
    applyLayoutToDom();
    applyMediaPositionToDom();
    renderBlockList();
    recordChange();
  };

  const refreshVisibilityChips = (structureItem, block) => {
    const liveBlock = getLiveBlock(block);
    if (!structureItem) return;
    structureItem.querySelectorAll('[data-block-field-toggle]').forEach((btn) => {
      const key = btn.dataset.blockFieldToggle;
      if (!key) return;
      let on = false;
      if (btn.dataset.splitToggle) {
        on = isSplitToggleOn(liveBlock, key);
      } else if (liveBlock.type === 'hero') {
        const config = (BLOCK_VISIBILITY_TOGGLES.hero || []).find((entry) => entry.key === key);
        on = isHeroFieldOn(liveBlock, key, config?.defaultOn !== false);
      } else {
        const config = (BLOCK_VISIBILITY_TOGGLES[liveBlock.type] || []).find((entry) => entry.key === key);
        on = isBlockFieldVisible(liveBlock, key, config?.defaultOn !== false);
      }
      btn.classList.toggle('is-on', on);
    });
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
      const isActive = block.id === activeBlockId;
      li.className = `jcp-block-structure__item${isActive ? ' is-active' : ''}`;
      li.dataset.index = String(index);
      li.dataset.blockId = block.id;
      const defaultLabel = blockLabel(block.type);
      const layoutHtml = buildLayoutControlsHtml(block);
      li.innerHTML = `
        <div class="jcp-block-structure__row">
          <span class="jcp-block-structure__handle" aria-hidden="true" title="Drag to reorder">⋮⋮</span>
          <div class="jcp-block-structure__meta">
            <span class="jcp-block-structure__type">${defaultLabel}</span>
            <input
              type="text"
              class="jcp-block-structure__label-input"
              aria-label="Section title on this page"
              title="Rename for this page only"
            >
          </div>
          ${layoutHtml ? '<button type="button" class="jcp-block-structure__settings" aria-label="Section settings" title="Settings">⚙</button>' : ''}
          <button type="button" class="jcp-block-structure__remove" data-index="${index}" aria-label="Remove section">×</button>
        </div>
        ${layoutHtml}
      `;
      const handle = li.querySelector('.jcp-block-structure__handle');
      const labelInput = li.querySelector('.jcp-block-structure__label-input');
      labelInput.value = blockDisplayLabel(block);
      labelInput.placeholder = defaultLabel;

      if (isActive) {
        li.querySelector('.jcp-block-structure__layout')?.classList.remove('is-collapsed');
      }

      li.querySelectorAll('.jcp-structure-tab').forEach((tabBtn) => {
        tabBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const tab = tabBtn.dataset.structureTab;
          const layoutRoot = li.querySelector('.jcp-block-structure__layout');
          if (!layoutRoot || !tab) return;
          structureTabByBlockId.set(block.id, tab);
          layoutRoot.querySelectorAll('.jcp-structure-tab').forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.structureTab === tab);
          });
          layoutRoot.querySelectorAll('.jcp-structure-panel').forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.structurePanel === tab);
          });
        });
      });

      const settingsBtn = li.querySelector('.jcp-block-structure__settings');
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

      if (settingsBtn) {
        settingsBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          const layout = li.querySelector('.jcp-block-structure__layout');
          const willExpand = layout?.classList.contains('is-collapsed');
          focusStructureBlock(block, { scrollPage: false, expand: willExpand });
          layout?.classList.toggle('is-collapsed', !willExpand);
        });
      }

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
          if (btn.dataset.sectionSurfacePreset) return;
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
      bindSectionSurfaceControls(li, block);
      li.querySelectorAll('[data-block-field-toggle]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const liveBlock = getLiveBlock(block);
          const key = btn.dataset.blockFieldToggle;
          if (btn.dataset.splitToggle) {
            const enabled = !isSplitToggleOn(liveBlock, key);
            setBlockSplitToggle(liveBlock, key, enabled);
            refreshVisibilityChips(li, liveBlock);
            return;
          }
          const config = (BLOCK_VISIBILITY_TOGGLES[liveBlock.type] || []).find((entry) => entry.key === key);
          const defaultOn = config?.defaultOn !== false;
          if (liveBlock.type === 'hero') {
            const enabled = !isHeroFieldOn(liveBlock, key, defaultOn);
            setBlockHeroToggle(liveBlock, key, enabled);
            refreshVisibilityChips(li, liveBlock);
            return;
          }
          const enabled = !isBlockFieldVisible(liveBlock, key, defaultOn);
          setBlockFieldVisible(liveBlock, key, enabled, config?.selector || '');
          refreshVisibilityChips(li, liveBlock);
        });
      });
      li.querySelectorAll('[data-hero-media-mode]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          setHeroMediaMode(block, btn.dataset.heroMediaMode);
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
    syncSplitTogglesToDom();
    syncBreadcrumbToggleUi();
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
    structureBtn.classList.add('is-active');
    renderBlockList();
    if (editing) refreshEditorChrome();
  };

  const closeStructure = () => {
    structureOpen = false;
    structurePanel.hidden = true;
    structurePanel.setAttribute('hidden', '');
    document.body.classList.remove('jcp-structure-open');
    structureBtn.classList.remove('is-active');
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
    const container = item?.closest('[data-jcp-array]');
    if (!item || !container) return false;
    const basePath = container.dataset.jcpArray;
    const path = el.getAttribute('data-jcp-path');
    if (!basePath || !path?.startsWith(`${basePath}.`)) return false;
    return /^\d+$/.test(path.slice(basePath.length + 1));
  };

  const isObjectArrayItemPath = (el) => {
    const path = el.getAttribute('data-jcp-path');
    if (!path) return false;
    return /^(?:core_mechanic\.\d+\.(?:value|label|detail)|hero\.meta_stats\.\d+\.(?:label|detail))$/.test(path);
  };

  const META_STAT_CSS_CLASSES = ['meta-stat-photo', 'meta-stat-channels', 'meta-stat-busywork'];

  const collectObjectArrayFromDom = (container, basePath, fields) => {
    const items = [...container.querySelectorAll(':scope > [data-jcp-array-item]')];
    const arr = items.map((item) => {
      const index = item.getAttribute('data-jcp-array-item');
      const prev = getPath(flatContent, `${basePath}.${index}`) || {};
      const readField = (field) => {
        const el = item.querySelector(`[data-jcp-path="${basePath}.${index}.${field}"]`);
        return (el?.textContent || '').trim();
      };
      const entry = { ...(typeof prev === 'object' && prev ? prev : {}) };
      fields.forEach((field) => {
        entry[field] = readField(field);
      });
      if (basePath === 'hero.meta_stats') {
        const cssClass = META_STAT_CSS_CLASSES.find((cls) => item.classList.contains(cls));
        if (cssClass) entry.css_class = cssClass;
      }
      return entry;
    });
    setPath(flatContent, basePath, arr);
  };

  const collectObjectArraysFromDom = () => {
    document.querySelectorAll('[data-jcp-array="core_mechanic"]').forEach((container) => {
      collectObjectArrayFromDom(container, 'core_mechanic', ['value', 'label', 'detail']);
    });
    document.querySelectorAll('[data-jcp-array="hero.meta_stats"]').forEach((container) => {
      collectObjectArrayFromDom(container, 'hero.meta_stats', ['label', 'detail']);
    });
  };

  const syncListBlockPropsFromFlat = () => {
    (pageDocument.blocks || []).forEach((block) => {
      if (!block) return;
      const key = blockLegacyKey(block);
      if (!key) return;
      if (block.type === 'core_mechanic' && Array.isArray(flatContent[key])) {
        block.props = JSON.parse(JSON.stringify(flatContent[key]));
      }
      if (block.type === 'hero' && Array.isArray(flatContent[key]?.meta_stats)) {
        block.props = block.props || {};
        block.props.meta_stats = JSON.parse(JSON.stringify(flatContent[key].meta_stats));
      }
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
      const host = container.querySelector(':scope > .conversion-points__columns') || container;
      const items = [...host.querySelectorAll(':scope > [data-jcp-array-item]')];
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
      if (isRichField(el) || /<a[\s>]/i.test(el.innerHTML || '')) {
        if (!isRichField(el)) el.setAttribute('data-jcp-rich', 'true');
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
    if (!dirty) statusEl.textContent = '';
    markExistingInlineLinks();
    bindEditableFields();
    applyCleanLinesToDom();
    refreshEditorChrome();
    requestAnimationFrame(refreshEditorChrome);
  };

  const disableEditing = () => {
    editing = false;
    document.body.classList.remove('jcp-inline-editing');
    toggleBtn.textContent = 'Edit page';
    toggleBtn.classList.remove('is-active');
    if (textLinkBtn) textLinkBtn.hidden = true;
    closeCtaLinkModal();
    closeTextLinkModal();
    closeIconPicker();
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
  breadcrumbToggleBtn?.addEventListener('click', (e) => {
    e.stopPropagation();
    setBreadcrumbVisible(!isBreadcrumbVisible());
    syncBreadcrumbToggleUi();
  });
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
    if (link.closest('.jcp-niche-edit-bar, .jcp-block-structure, .jcp-block-add-modal, .jcp-editor-modal')) return;
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
    openEditorModal(popover, { focusSelector: '#jcpNicheLinkUrl' });
  });

  popover.querySelector('.jcp-editor-modal__backdrop').addEventListener('click', closeCtaLinkModal);
  popover.querySelector('#jcpNicheLinkApply').addEventListener('click', () => {
    if (!activeLink) return;
    activeLink.setAttribute('href', popover.querySelector('#jcpNicheLinkUrl').value.trim());
    closeCtaLinkModal();
    recordChange();
  });

  popover.querySelector('#jcpNicheLinkCancel').addEventListener('click', closeCtaLinkModal);

  const normalizeInternalHref = (href) => {
    if (!href) return '';
    let h = String(href).trim();
    if (h === '' || h.startsWith('#') || h.startsWith('javascript:')) return '';
    if (!h.startsWith('/')) {
      try {
        const u = new URL(h, window.location.origin);
        h = u.pathname + u.search + u.hash;
      } catch (e) {
        return '';
      }
    }
    return h.startsWith('/') ? h : '';
  };

  const getCurrentInternalLinkCounts = () => {
    const counts = new Map();
    document.querySelectorAll('[data-jcp-path] a[href]').forEach((a) => {
      if (a.hasAttribute('data-jcp-href-path')) return;
      const raw = a.getAttribute('href');
      const href = normalizeInternalHref(raw);
      if (!href) return;
      counts.set(href, (counts.get(href) || 0) + 1);
    });
    return counts;
  };

  const LINK_STOP_WORDS = new Set([
    'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'have', 'in', 'is', 'it',
    'of', 'on', 'or', 'that', 'the', 'their', 'this', 'to', 'was', 'were', 'will', 'with', 'your',
    'our', 'we', 'you', 'more', 'every', 'into', 'how', 'what', 'when', 'who', 'why',
  ]);

  const tokenizeLinkText = (text) => {
    const raw = String(text || '').toLowerCase().replace(/<[^>]+>/g, ' ').replace(/[^a-z0-9\s-]/g, ' ');
    return [...new Set(raw.split(/\s+/).map((part) => part.trim()).filter((part) => part.length >= 3 && !LINK_STOP_WORDS.has(part)))];
  };

  const isValidInternalTarget = (href) => {
    const path = normalizeInternalHref(href);
    if (!path || path === '/' || path === linkIndex.current_path) return false;
    const lower = path.toLowerCase();
    if (lower.startsWith('/@') || lower.startsWith('/channel/') || lower.includes('/wp-admin')) return false;
    const segments = path.split('/').filter(Boolean);
    if (segments.length === 1 && /^@?[a-z0-9_-]{3,}$/i.test(segments[0])) {
      const allowed = new Set(['demo', 'pricing', 'blog', 'contact', 'features', 'industries', 'resources', 'directory']);
      if (!allowed.has(segments[0].replace(/^@/, ''))) return false;
    }
    return true;
  };

  const scoreLinkTarget = (page, anchorTokens, anchorPhrase, pageCounts) => {
    const keywords = new Set([
      ...(Array.isArray(page.keywords) ? page.keywords : []),
      ...tokenizeLinkText(page.label),
      ...tokenizeLinkText(page.focus_keyword),
      ...tokenizeLinkText(page.href.replace(/\//g, ' ')),
    ]);
    const focus = String(page.focus_keyword || '').toLowerCase();
    const label = String(page.label || '').toLowerCase();
    const phrase = String(anchorPhrase || '').toLowerCase().trim();
    const hrefLower = String(page.href || '').toLowerCase();
    const slug = hrefLower.split('/').filter(Boolean).pop() || '';
    const phraseSlug = phrase.replace(/\s+/g, '-');
    const phraseCompact = phrase.replace(/\s+/g, '');

    let relevance = 0;
    let exactMatch = false;

    if (phrase.length >= 3) {
      if (slug === phraseSlug || slug === phraseCompact || slug === phrase) {
        relevance += 120;
        exactMatch = true;
      }
      if (label === phrase) {
        relevance += 100;
        exactMatch = true;
      }
      if (hrefLower === `/${phraseSlug}` || hrefLower.endsWith(`/${phraseSlug}`)) {
        relevance += 90;
        exactMatch = true;
      }
      if (focus === phrase) relevance += 70;
      if (focus.includes(phrase)) relevance += 28;
      if (label.includes(phrase)) relevance += 32;
      if (hrefLower.includes(phraseSlug)) relevance += 24;
    }

    anchorTokens.forEach((token) => {
      if (slug === token) {
        relevance += 80;
        exactMatch = true;
      }
      if (keywords.has(token)) relevance += 14;
      if (focus.includes(token)) relevance += 10;
      if (label.includes(token)) relevance += 8;
      if (slug.includes(token)) relevance += 12;
    });

    const siteInlinks = Number(page.site_inlinks) || 0;
    const onPageCount = pageCounts.get(page.href) || 0;
    const gapScore = Math.max(0, 3 - siteInlinks) * 10 + (onPageCount === 0 ? 6 : 0);
    const hubBoost = !exactMatch && (page.hub === 'feature' || page.hub === 'trade') ? 4 : 0;

    return {
      href: page.href,
      label: page.label || page.href,
      hub: page.hub || 'page',
      count: onPageCount,
      siteInlinks,
      focus_keyword: page.focus_keyword || '',
      score: relevance + gapScore + hubBoost,
      relevance,
      gapScore,
      exactMatch,
      reason: '',
    };
  };

  const buildLinkSuggestionReason = (item, anchorPhrase) => {
    const bits = [];
    if (item.exactMatch && anchorPhrase) {
      bits.push('Best match for your selection');
    } else if (item.relevance >= 18 && anchorPhrase) {
      bits.push('Matches your selected text');
    } else if (item.focus_keyword && anchorPhrase && item.focus_keyword.toLowerCase().includes(anchorPhrase.toLowerCase())) {
      bits.push(`Aligns with focus keyword “${item.focus_keyword}”`);
    }
    if (item.siteInlinks === 0) {
      bits.push('No inbound internal links site-wide yet');
    } else if (item.siteInlinks <= 1) {
      bits.push('Underlinked across the site');
    }
    if (item.count > 0) {
      bits.push(`Already linked ${item.count}× on this page`);
    }
    if (!bits.length) {
      bits.push(item.hub === 'trade' ? 'Trade landing page' : item.hub === 'feature' ? 'Feature page' : 'Related site page');
    }
    return bits.slice(0, 2).join(' · ');
  };

  const getSuggestedInternalPages = (counts, anchorText = '') => {
    const anchorPhrase = String(anchorText || '').trim();
    const anchorTokens = tokenizeLinkText(anchorPhrase);
    const catalog = Array.isArray(linkIndex.pages) ? linkIndex.pages : [];
    const scored = catalog
      .filter((page) => page && isValidInternalTarget(page.href))
      .map((page) => scoreLinkTarget(page, anchorTokens, anchorPhrase, counts))
      .map((item) => ({ ...item, reason: buildLinkSuggestionReason(item, anchorPhrase) }))
      .sort((a, b) => {
        if (anchorPhrase) {
          if (Number(b.exactMatch) !== Number(a.exactMatch)) return Number(b.exactMatch) - Number(a.exactMatch);
          if (b.relevance !== a.relevance) return b.relevance - a.relevance;
        }
        return (b.score - a.score) || (a.siteInlinks - b.siteInlinks) || a.label.localeCompare(b.label);
      });

    const groups = [
      { key: 'match', title: 'Best match for your selection', items: [] },
      { key: 'gap', title: 'Pages that need more internal links', items: [] },
      { key: 'related', title: 'Other relevant pages', items: [] },
    ];

    scored.forEach((item) => {
      if (anchorPhrase && item.exactMatch && groups[0].items.length < 6) {
        groups[0].items.push(item);
        return;
      }
      if (anchorTokens.length && item.relevance < 8 && item.gapScore < 10) {
        return;
      }
      if (item.relevance >= 12 && groups[0].items.length < 6) {
        groups[0].items.push(item);
      } else if (item.gapScore >= 20 && groups[1].items.length < 6) {
        groups[1].items.push(item);
      } else if (item.relevance >= 6 && groups[2].items.length < 8) {
        groups[2].items.push(item);
      } else if (!anchorTokens.length && groups[2].items.length < 8) {
        groups[2].items.push(item);
      }
    });

    if (!groups[0].items.length && anchorPhrase && scored.length) {
      const fallback = scored.filter((item) => item.relevance >= 8 || item.exactMatch);
      groups[0].items = (fallback.length ? fallback : scored).slice(0, 5);
    } else if (!groups[0].items.length && scored.length) {
      groups[0].items = scored.slice(0, 5);
    }

    return { groups, flat: scored.slice(0, 20), anchorTokens };
  };

  const renderLinkSuggestionButton = (s) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jcp-niche-link-popover__suggestion-btn';
    btn.dataset.href = s.href;

    const copy = document.createElement('span');
    copy.className = 'jcp-niche-link-popover__suggestion-copy';

    const label = document.createElement('span');
    label.className = 'jcp-niche-link-popover__suggestion-label';
    label.textContent = s.label;
    copy.appendChild(label);

    if (s.reason) {
      const reason = document.createElement('span');
      reason.className = 'jcp-niche-link-popover__suggestion-reason';
      reason.textContent = s.reason;
      copy.appendChild(reason);
    }

    btn.appendChild(copy);

    const meta = document.createElement('span');
    meta.className = 'jcp-niche-link-popover__suggestion-meta';
    if (s.exactMatch) {
      const badge = document.createElement('span');
      badge.className = 'jcp-niche-link-popover__suggestion-badge jcp-niche-link-popover__suggestion-badge--match';
      badge.textContent = 'Best match';
      meta.appendChild(badge);
    } else if (s.siteInlinks === 0) {
      const badge = document.createElement('span');
      badge.className = 'jcp-niche-link-popover__suggestion-badge';
      badge.textContent = 'Needs links';
      meta.appendChild(badge);
    }
    if (s.count) {
      const c = document.createElement('span');
      c.className = 'jcp-niche-link-popover__suggestion-count';
      c.textContent = String(s.count);
      meta.appendChild(c);
    }
    if (meta.childNodes.length) btn.appendChild(meta);

    btn.addEventListener('click', () => {
      const urlInput = textLinkPopover.querySelector('#jcpNicheTextLinkUrl');
      if (urlInput) urlInput.value = s.href;
    });
    return btn;
  };

  const expandCollapsedRangeToWord = (range) => {
    try {
      if (!range || !range.collapsed) return range;
      const sc = range.startContainer;
      if (!sc || sc.nodeType !== Node.TEXT_NODE) return range;
      const text = sc.textContent || '';
      if (!text) return range;
      const offset = Math.max(0, Math.min(range.startOffset, text.length));
      if (offset >= text.length) return range;

      const isWord = (ch) => /[A-Za-z0-9]/.test(ch);
      let start = offset;
      let end = offset;
      while (start > 0 && isWord(text[start - 1])) start -= 1;
      while (end < text.length && isWord(text[end])) end += 1;
      if (start === end) return range;

      const r = document.createRange();
      r.setStart(sc, start);
      r.setEnd(sc, end);
      return r;
    } catch (e) {
      return range;
    }
  };

  const openTextLinkPopover = () => {
    const { range, field } = getLinkContext();
    activeRichField = field || null;
    statusEl.textContent = '';

    const counts = getCurrentInternalLinkCounts();
    const anchorText = range && !range.collapsed ? range.toString() : '';
    const { groups, flat, anchorTokens } = getSuggestedInternalPages(counts, anchorText);

    const hintEl = textLinkPopover.querySelector('#jcpNicheTextLinkHint');
    const seoEl = textLinkPopover.querySelector('#jcpNicheLinkSeo');
    const listEl = textLinkPopover.querySelector('#jcpNicheLinkSuggestions');

    const totalLinks = [...counts.values()].reduce((a, b) => a + b, 0);
    const uniqueLinks = counts.size;
    const underlinked = flat.filter((item) => item.siteInlinks <= 1).length;

    if (!field) {
      hintEl.textContent = 'Click inside a text paragraph first.';
    } else if (anchorText) {
      hintEl.textContent = `Link “${anchorText.slice(0, 48)}${anchorText.length > 48 ? '…' : ''}” — pick a page or paste a URL.`;
    } else {
      hintEl.textContent = 'Select the words you want to link, then choose a URL.';
    }

    seoEl.innerHTML = '';
    const seoTop = document.createElement('div');
    seoTop.innerHTML = `<strong>On this page:</strong> ${totalLinks} internal link${totalLinks === 1 ? '' : 's'} · ${uniqueLinks} unique destination${uniqueLinks === 1 ? '' : 's'}`;
    seoEl.appendChild(seoTop);

    const seoGap = document.createElement('div');
    seoGap.className = 'jcp-niche-link-popover__seo-top';
    if (underlinked > 0) {
      seoGap.textContent = `${underlinked} high-value page${underlinked === 1 ? '' : 's'} still underlinked site-wide — prioritize those below.`;
    } else if (anchorTokens.length) {
      seoGap.textContent = `Scored ${flat.length} pages against “${anchorTokens.slice(0, 4).join(', ')}”.`;
    } else {
      seoGap.textContent = 'Select anchor text to rank suggestions by topical relevance.';
    }
    seoEl.appendChild(seoGap);

    const topTargets = flat
      .filter((s) => s.count > 0)
      .slice(0, 3)
      .map((s) => `${s.label} (${s.count})`)
      .join(', ');
    if (topTargets) {
      const t = document.createElement('div');
      t.textContent = `Already linked here: ${topTargets}`;
      t.className = 'jcp-niche-link-popover__seo-note';
      seoEl.appendChild(t);
    }

    listEl.innerHTML = '';
    const rendered = groups.filter((group) => group.items.length);
    if (!rendered.length) {
      const empty = document.createElement('div');
      empty.className = 'jcp-niche-link-popover__empty';
      empty.textContent = 'No linkable pages found. Publish more JCP pages or paste a URL manually.';
      listEl.appendChild(empty);
    } else {
      rendered.forEach((group) => {
        const title = document.createElement('div');
        title.className = 'jcp-niche-link-popover__suggestions-title';
        title.textContent = group.title;
        listEl.appendChild(title);
        group.items.forEach((s) => listEl.appendChild(renderLinkSuggestionButton(s)));
      });
    }

    const urlInput = textLinkPopover.querySelector('#jcpNicheTextLinkUrl');
    if (urlInput) {
      const topMatch = groups[0]?.items?.[0] || flat[0];
      urlInput.value = topMatch?.href || '';
    }

    openEditorModal(textLinkPopover, { focusSelector: '#jcpNicheTextLinkUrl' });
  };

  if (textLinkBtn) {
    textLinkBtn.addEventListener('mousedown', (e) => {
      e.preventDefault();
      rememberLinkSelection();
    });
    textLinkBtn.addEventListener('click', openTextLinkPopover);
  }

  textLinkPopover.querySelector('#jcpNicheTextLinkApply').addEventListener('click', () => {
    const url = textLinkPopover.querySelector('#jcpNicheTextLinkUrl').value.trim();
    if (!url) return;

    const { range, field } = getLinkContext();
    const rich = field || activeRichField;
    if (!rich) {
      statusEl.textContent = 'Click inside a text paragraph first.';
      return;
    }

    let linkRange = range;
    if (!linkRange || linkRange.collapsed) {
      statusEl.textContent = 'Select the text you want to link first.';
      return;
    }

    if (!rich.contains(linkRange.commonAncestorContainer)) {
      statusEl.textContent = 'Selection must be inside the same paragraph.';
      return;
    }

    linkRange = expandCollapsedRangeToWord(linkRange);
    const sel = window.getSelection();
    if (sel) {
      sel.removeAllRanges();
      sel.addRange(linkRange);
    }

    const label = linkRange.toString() || url;
    const anchor = document.createElement('a');
    anchor.href = url;
    anchor.textContent = label;
    linkRange.deleteContents();
    linkRange.insertNode(anchor);

    if (!isRichField(rich)) {
      rich.setAttribute('data-jcp-rich', 'true');
    }

    pendingLinkRange = null;
    pendingLinkField = null;
    closeTextLinkModal();
    statusEl.textContent = '';
    recordChange();
  });

  textLinkPopover.querySelector('.jcp-editor-modal__backdrop').addEventListener('click', closeTextLinkModal);
  textLinkPopover.querySelector('#jcpNicheTextLinkCancel').addEventListener('click', closeTextLinkModal);

  document.addEventListener('selectionchange', () => {
    rememberLinkSelection();
  });

  document.addEventListener('keydown', (e) => {
    if (!editing) return;
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      rememberLinkSelection();
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
      if (!iconPopover.hidden) {
        closeIconPicker();
        return;
      }
      if (!textLinkPopover.hidden) {
        closeTextLinkModal();
        return;
      }
      if (!popover.hidden) {
        closeCtaLinkModal();
        return;
      }
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

  const bindBlockSelection = () => {
    const main = getMain();
    if (!main || main.dataset.jcpBlockSelectBound === '1') return;
    main.dataset.jcpBlockSelectBound = '1';
    main.addEventListener('click', (e) => {
      if (!editing) return;
      if (e.target.closest(
        '[data-jcp-path], [data-jcp-href-path], .jcp-collection-add, .jcp-collection-remove, '
        + '.jcp-optional-restore, .jcp-media-picker, .jcp-split-col-handle, .jcp-editor-modal, '
        + 'button:not([data-jcp-block-id]), input, textarea, select, summary, details'
      )) return;
      const root = e.target.closest('[data-jcp-block-id]');
      if (!root) return;
      const block = (pageDocument.blocks || []).find((entry) => entry.id === root.dataset.jcpBlockId);
      if (block) focusStructureBlock(block, { scrollPage: false, expand: true });
    });
  };

  initHistory();
  normalizePageDocumentBlocks();
  sanitizeFlatContentInPlace();
  indexBlockSections();
  applyCleanLinesToDom();
  applyLayoutToDom();
  applyMediaPositionToDom();
  markExistingInlineLinks();

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
  bindBlockSelection();

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
    bindBlockSelection();
    syncSplitTogglesToDom();
  });
})();
