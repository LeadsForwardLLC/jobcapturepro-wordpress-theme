(() => {
  const CHECK_SVG = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';

  const ARRAY_DEFAULTS = {
    'conversion.points': () => 'New point',
    'differentiation.bullets': () => 'New point',
    'what_it_is.team_already': () => 'New item',
    'what_it_is.turns_into': () => 'New item',
    'check_ins.job_types': () => 'New tag',
    'faq.items': () => ({ q: 'New question', a: 'Answer text.' }),
    'how_it_works.steps': () => ({ title: 'New step', lines: ['Step description'] }),
    'benefits.items': () => ({
      icon: 'badge-check',
      title: 'New benefit',
      body: 'Description',
      stat_value: 'Stat',
      stat_label: 'label',
    }),
    'check_ins.features': () => ({ title: 'Feature', body: 'Description' }),
    'problem.pain_points': () => ({ title: 'Pain point', body: 'Description' }),
    'who_its_for.audiences': () => ({
      title: 'Audience',
      body: 'Description',
      badge: 'Badge',
      stat_number: '100%',
      stat_label: 'Label',
    }),
  };

  const OPTIONAL_DEFAULTS = {
    'conversion.cta_primary': () => ({ label: 'Button label', url: '/demo' }),
    'benefits.cta_primary': () => ({ label: 'See it in the demo', url: '/demo' }),
    'benefits.cta_secondary': () => ({ label: 'Learn more', url: '/pricing' }),
    'hero.cta_primary': () => ({ label: 'View the live demo', url: '/demo' }),
    'hero.cta_secondary': () => ({ label: 'Learn how it works', url: '#how-it-works' }),
    'final_cta.cta_primary': () => ({ label: 'Get started', url: '/demo' }),
  };

  const OPTIONAL_TEMPLATES = {
    cta: (path, data) => {
      const cls = path.includes('conversion') ? 'btn btn-primary conversion-cta-btn'
        : path.includes('benefits') ? 'btn btn-primary'
          : path.includes('final_cta') ? 'btn btn-primary rankings-cta-btn'
            : 'btn btn-primary';
      return `<a href="${esc(data.url)}" class="${cls}" data-jcp-path="${path}.label" data-jcp-href-path="${path}.url">${esc(data.label)}</a>`;
    },
    link: (path, data) => `<a href="${esc(data.url)}" class="benefits-cta-link" data-jcp-path="${path}.label" data-jcp-href-path="${path}.url">${esc(data.label)}<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>`,
  };

  let api = null;
  const boundHandlers = [];

  const esc = (s) => String(s ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

  const getArray = (path) => {
    const val = api.getPath(api.flatContent, path);
    return Array.isArray(val) ? val : [];
  };

  const setArray = (path, arr) => {
    api.setPath(api.flatContent, path, arr);
  };

  const replacePathIndex = (path, basePath, index) => {
    if (!path || !path.startsWith(`${basePath}.`)) return path;
    const rest = path.slice(basePath.length + 1).split('.');
    rest[0] = String(index);
    return `${basePath}.${rest.join('.')}`;
  };

  const reindexContainer = (container, basePath) => {
    const items = [...container.querySelectorAll(':scope > [data-jcp-array-item]')];
    items.forEach((item, index) => {
      item.dataset.jcpArrayItem = String(index);
      item.querySelectorAll('[data-jcp-path]').forEach((el) => {
        el.setAttribute('data-jcp-path', replacePathIndex(el.getAttribute('data-jcp-path'), basePath, index));
      });
      item.querySelectorAll('[data-jcp-href-path]').forEach((el) => {
        el.setAttribute('data-jcp-href-path', replacePathIndex(el.getAttribute('data-jcp-href-path'), basePath, index));
      });
    });
  };

  const iconUrl = (name) => {
    const base = window.JCP_ASSET_BASE || '';
    return `${base}/shared/assets/icons/lucide/${name}.svg`;
  };

  const buildFactorCard = (basePath, index, data) => {
    const stat = data.stat_value
      ? `<div class="factor-stat">
          <span class="stat-value" data-jcp-path="${basePath}.${index}.stat_value">${esc(data.stat_value)}</span>
          <span class="stat-label" data-jcp-path="${basePath}.${index}.stat_label">${esc(data.stat_label || '')}</span>
        </div>`
      : '';
    return `
      <div class="ranking-factor-card" data-jcp-array-item="${index}">
        <div class="factor-icon-wrapper">
          <img src="${iconUrl(data.icon || 'badge-check')}" class="factor-icon" alt="" width="32" height="32" />
        </div>
        <h3 class="factor-title" data-jcp-path="${basePath}.${index}.title">${esc(data.title || '')}</h3>
        <div class="factor-description"><p data-jcp-path="${basePath}.${index}.body">${esc(data.body || '')}</p></div>
        ${stat}
      </div>`;
  };

  const buildTimelineStep = (basePath, index, data) => {
    const lines = Array.isArray(data.lines) ? data.lines : [data.body || data.description || 'Step description'];
    const linesHtml = lines.map((line, li) =>
      `<p class="step-description" data-jcp-path="${basePath}.${index}.lines.${li}">${esc(line)}</p>`
    ).join('');
    return `
      <div class="timeline-step" data-jcp-array-item="${index}">
        <div class="step-number">${index + 1}</div>
        <div class="step-content">
          <h4 class="step-title" data-jcp-path="${basePath}.${index}.title">${esc(data.title || '')}</h4>
          ${linesHtml}
        </div>
      </div>`;
  };

  const buildConversionPoint = (basePath, index, text) => `
    <div class="conversion-point" data-jcp-array-item="${index}">
      <div class="conversion-point-icon">${CHECK_SVG}</div>
      <div class="conversion-point-text">
        <strong data-jcp-path="${basePath}.${index}">${esc(text)}</strong>
      </div>
    </div>`;

  const buildChecklistItem = (basePath, index, text) => `<li data-jcp-array-item="${index}" data-jcp-path="${basePath}.${index}">${esc(text)}</li>`;

  const buildTagItem = (basePath, index, text) => `<li data-jcp-array-item="${index}" data-jcp-path="${basePath}.${index}">${esc(text)}</li>`;

  const buildFaqItem = (index, item) => {
    const qPath = `faq.items.${index}.q`;
    const aPath = `faq.items.${index}.a`;
    return `
      <details class="faq-item" id="faq-${index}" data-jcp-array-item="${index}">
        <summary data-jcp-path="${qPath}">${esc(item.q || '')}</summary>
        <p data-jcp-path="${aPath}">${esc(item.a || '')}</p>
      </details>`;
  };

  const cloneArrayItem = (container, basePath, index, data) => {
    const sample = container.querySelector('[data-jcp-array-item]');
    if (sample) {
      const clone = sample.cloneNode(true);
      clone.querySelectorAll('[data-jcp-path]').forEach((el) => { el.textContent = ''; });
      container.appendChild(clone);
      reindexContainer(container, basePath);
      applyDefaultsToItem(container, basePath, index, data);
      return clone;
    }

    if (basePath.endsWith('.points') || basePath.endsWith('.bullets')) {
      const html = buildConversionPoint(basePath, index, String(data));
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    if (basePath.includes('team_already') || basePath.includes('turns_into')) {
      const html = buildChecklistItem(basePath, index, String(data));
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    if (basePath.endsWith('.job_types')) {
      const html = buildTagItem(basePath, index, String(data));
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    if (basePath === 'faq.items') {
      const html = buildFaqItem(index, data);
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    if (basePath === 'how_it_works.steps') {
      const html = buildTimelineStep(basePath, index, data);
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    if (basePath.endsWith('.items') || basePath.endsWith('.features') || basePath.endsWith('.pain_points') || basePath.endsWith('.audiences')) {
      const html = buildFactorCard(basePath, index, data);
      container.insertAdjacentHTML('beforeend', html);
      return container.lastElementChild;
    }
    return null;
  };

  const applyDefaultsToItem = (container, basePath, index, data) => {
    const item = container.querySelector(`[data-jcp-array-item="${index}"]`);
    if (!item) return;
    if (typeof data === 'string') {
      const el = item.querySelector('[data-jcp-path]') || item;
      if (el) el.textContent = data;
      return;
    }
    if (basePath === 'faq.items') {
      const q = item.querySelector('[data-jcp-path$=".q"]');
      const a = item.querySelector('[data-jcp-path$=".a"]');
      if (q) q.textContent = data.q || '';
      if (a) a.textContent = data.a || '';
      return;
    }
    item.querySelectorAll('[data-jcp-path]').forEach((el) => {
      const path = el.getAttribute('data-jcp-path') || '';
      const key = path.split('.').pop();
      if (key && data[key] !== undefined) el.textContent = String(data[key]);
    });
  };

  const addArrayItem = (container, basePath) => {
    api.collectFromDom();
    const arr = getArray(basePath);
    const factory = ARRAY_DEFAULTS[basePath] || (() => 'New item');
    const index = arr.length;
    const data = factory(index);
    arr.push(data);
    setArray(basePath, arr);
    cloneArrayItem(container, basePath, index, data);
    refreshCollections();
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    api.recordChange();
  };

  const removeArrayItem = (container, basePath, index) => {
    api.collectFromDom();
    const arr = getArray(basePath);
    if (index < 0 || index >= arr.length) return;
    arr.splice(index, 1);
    setArray(basePath, arr);
    const item = container.querySelector(`[data-jcp-array-item="${index}"]`);
    if (item) item.remove();
    reindexContainer(container, basePath);
    refreshCollections();
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    api.recordChange();
  };

  const isOptionalEmpty = (path) => {
    const val = api.getPath(api.flatContent, path);
    if (!val || typeof val !== 'object') return true;
    return !String(val.label || '').trim();
  };

  const restoreOptional = (slot) => {
    const path = slot.dataset.jcpOptional;
    const kind = slot.dataset.jcpOptionalKind || 'cta';
    const factory = OPTIONAL_DEFAULTS[path] || (() => ({ label: 'Button', url: '#' }));
    const data = factory();
    api.setPath(api.flatContent, path, data);
    const tpl = OPTIONAL_TEMPLATES[kind] || OPTIONAL_TEMPLATES.cta;
    slot.innerHTML = tpl(path, data);
    slot.classList.remove('is-empty');
    refreshCollections();
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    api.recordChange();
  };

  const removeOptional = (slot) => {
    const path = slot.dataset.jcpOptional;
    api.collectFromDom();
    api.setPath(api.flatContent, path, { label: '', url: '' });
    slot.innerHTML = '';
    slot.classList.add('is-empty');
    ensureOptionalPlaceholder(slot);
    refreshCollections();
    api.recordChange();
  };

  const ensureOptionalPlaceholder = (slot) => {
    if (!slot.classList.contains('is-empty')) return;
    let ph = slot.querySelector('.jcp-optional-restore');
    if (!ph) {
      ph = document.createElement('button');
      ph.type = 'button';
      ph.className = 'jcp-optional-restore';
      ph.textContent = slot.dataset.jcpOptionalLabel || 'Add button';
      ph.addEventListener('click', (e) => {
        e.preventDefault();
        restoreOptional(slot);
      });
      slot.appendChild(ph);
    }
  };

  const injectRemoveButton = (item) => {
    if (item.querySelector('.jcp-collection-remove')) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jcp-collection-remove';
    btn.setAttribute('aria-label', 'Remove item');
    btn.title = 'Remove';
    btn.textContent = '×';
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      const container = item.closest('[data-jcp-array]');
      if (!container) return;
      const index = parseInt(item.dataset.jcpArrayItem, 10);
      removeArrayItem(container, container.dataset.jcpArray, index);
    });
    item.classList.add('jcp-collection-item');
    item.appendChild(btn);
  };

  const injectOptionalControls = (slot) => {
    const content = slot.querySelector('a, .btn');
    if (content && !slot.querySelector('.jcp-collection-remove')) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'jcp-collection-remove jcp-collection-remove--optional';
      btn.setAttribute('aria-label', 'Remove');
      btn.title = 'Remove';
      btn.textContent = '×';
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        removeOptional(slot);
      });
      slot.classList.add('jcp-optional-slot');
      slot.appendChild(btn);
    }
    if (isOptionalEmpty(slot.dataset.jcpOptional)) {
      slot.classList.add('is-empty');
      ensureOptionalPlaceholder(slot);
    } else {
      slot.classList.remove('is-empty');
      slot.querySelector('.jcp-optional-restore')?.remove();
    }
  };

  const injectAddButton = (container) => {
    let btn = container.querySelector(':scope > .jcp-collection-add');
    if (btn) btn.remove();
    btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'jcp-collection-add';
    const label = container.classList.contains('faq-grid') ? '+ Add question'
      : container.classList.contains('timeline-steps') ? '+ Add step'
        : container.classList.contains('ranking-factors-grid') ? '+ Add card'
          : '+ Add item';
    btn.textContent = label;
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      addArrayItem(container, container.dataset.jcpArray);
    });
    container.appendChild(btn);
  };

  const teardownCollections = () => {
    document.querySelectorAll('.jcp-collection-remove, .jcp-collection-add, .jcp-optional-restore').forEach((el) => el.remove());
    document.querySelectorAll('.jcp-collection-item, .jcp-optional-slot').forEach((el) => {
      el.classList.remove('jcp-collection-item', 'jcp-optional-slot', 'is-empty');
    });
    boundHandlers.forEach(({ el, fn }) => el.removeEventListener('click', fn));
    boundHandlers.length = 0;
  };

  const refreshCollections = () => {
    if (!api || !api.editing()) return;
    teardownCollections();

    document.querySelectorAll('[data-jcp-array]').forEach((container) => {
      container.querySelectorAll(':scope > [data-jcp-array-item]').forEach(injectRemoveButton);
      injectAddButton(container);
    });

    document.querySelectorAll('[data-jcp-optional]').forEach(injectOptionalControls);
  };

  const init = (editorApi) => {
    api = editorApi;
    window.JCP_REFRESH_COLLECTIONS = refreshCollections;
    window.JCP_TEARDOWN_COLLECTIONS = teardownCollections;
  };

  if (window.__JCP_EDITOR_API__) init(window.__JCP_EDITOR_API__);
  window.JCP_INIT_COLLECTION_EDITOR = init;
})();
