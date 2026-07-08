(() => {
  const CHECK_SVG = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';

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
    core_mechanic: (index = 0) => ({
      value: '0',
      label: 'stat',
      detail: 'Description',
      icon: STAT_BADGE_ICONS[index % STAT_BADGE_ICONS.length],
      css_class: STAT_BADGE_CLASSES[index % STAT_BADGE_CLASSES.length],
    }),
  };

  const OBJECT_ARRAY_PATHS = new Set(['core_mechanic']);

  const isStringArrayPath = (basePath) => {
    if (OBJECT_ARRAY_PATHS.has(basePath)) return false;
    if (ARRAY_DEFAULTS[basePath] && typeof ARRAY_DEFAULTS[basePath]() === 'string') return true;
    return /\.(?:lines|team_already|turns_into|job_types|bullets|points)$/.test(basePath);
  };

  const STAT_BADGE_CLASSES = ['meta-stat-photo', 'meta-stat-channels', 'meta-stat-busywork'];
  const STAT_BADGE_ICONS = ['camera', 'map', 'clock'];

  const arrayDefaultFactory = (basePath) => {
    if (ARRAY_DEFAULTS[basePath]) return ARRAY_DEFAULTS[basePath];
    if (/\.lines$/.test(basePath)) return () => 'New point';
    if (isStringArrayPath(basePath)) return () => 'New item';
    return () => 'New item';
  };

  const addButtonLabel = (container) => {
    if (container.classList.contains('faq-grid')) return '+ Add question';
    if (container.classList.contains('timeline-steps')) return '+ Add step';
    if (container.classList.contains('jcp-step-checklist')) return '+ Add point';
    if (container.classList.contains('jcp-niche-checklist')) return '+ Add item';
    if (container.classList.contains('jcp-niche-tags')) return '+ Add tag';
    if (container.classList.contains('conversion-points')) return '+ Add point';
    if (container.classList.contains('jcp-core-mechanic-meta')) return '+ Add stat';
    if (container.classList.contains('ranking-factors-grid') || container.classList.contains('guarantees-grid')) return '+ Add card';
    return '+ Add item';
  };

  const OPTIONAL_DEFAULTS = {
    'conversion.cta_primary': () => ({ label: 'Button label', url: '/demo' }),
    'what_it_is.cta_primary': () => ({ label: 'Learn more', url: '/demo' }),
    'what_it_is.cta_secondary': () => ({ label: 'See how it works', url: '#how-it-works' }),
    'how_it_works.cta_primary': () => ({ label: 'See it in action', url: '/demo' }),
    'how_it_works.cta_secondary': () => ({ label: 'View pricing', url: '/pricing' }),
    'check_ins.cta_primary': () => ({ label: 'See it in action', url: '/demo' }),
    'problem.cta_primary': () => ({ label: 'Fix this with JobCapturePro', url: '/demo' }),
    'benefits.cta_primary': () => ({ label: 'See it in the demo', url: '/demo' }),
    'benefits.cta_secondary': () => ({ label: 'Learn more', url: '/pricing' }),
    'differentiation.cta_primary': () => ({ label: 'Get started', url: '/demo' }),
    'who_its_for.cta_primary': () => ({ label: 'Start free trial', url: '/demo' }),
    'faq.cta_primary': () => ({ label: 'Still have questions? Book a demo', url: '/demo' }),
    'hero.cta_primary': () => ({ label: 'View the live demo', url: '/demo' }),
    'hero.cta_secondary': () => ({ label: 'Learn how it works', url: '#how-it-works' }),
    'final_cta.cta_primary': () => ({ label: 'Get started', url: '/demo' }),
    'media_text.cta_primary': () => ({ label: 'See it in action', url: '/demo' }),
    'media_text_check_ins.cta_primary': () => ({ label: 'See it in action', url: '/demo' }),
    'media_text_problem.cta_primary': () => ({ label: 'See it in action', url: '/demo' }),
    'demo_preview.cta_primary': () => ({ label: 'View the live demo', url: '/demo' }),
  };

  const OPTIONAL_TEMPLATES = {
    cta: (path, data) => {
      const isDemo = /\.(cta_primary)$/.test(path) && (path.includes('media_text') || path.includes('demo_preview'));
      const cls = path.includes('conversion')
        ? 'btn btn-primary conversion-cta-btn'
        : path.includes('benefits')
          ? 'btn btn-primary'
          : path.includes('final_cta')
            ? 'btn btn-primary rankings-cta-btn'
            : isDemo
              ? 'btn btn-primary demo-cta-primary'
              : 'btn btn-primary';
      const chevron = isDemo
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>'
        : '';
      return `<a href="${esc(data.url)}" class="${cls}" data-jcp-path="${path}.label" data-jcp-href-path="${path}.url"><span>${esc(data.label)}</span>${chevron}</a>`;
    },
    link: (path, data) => `<a href="${esc(data.url)}" class="benefits-cta-link" data-jcp-path="${path}.label" data-jcp-href-path="${path}.url">${esc(data.label)}<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>`,
  };

  let api = null;

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
    const linesPath = `${basePath}.${index}.lines`;
    const linesHtml = lines.map((line, li) =>
      `<li data-jcp-array-item="${li}">
        <span class="jcp-step-checklist__icon" aria-hidden="true">${CHECK_SVG}</span>
        <span class="jcp-step-checklist__text" data-jcp-path="${linesPath}.${li}">${esc(cleanStepLineText(line))}</span>
      </li>`
    ).join('');
    return `
      <div class="timeline-step" data-jcp-array-item="${index}">
        <div class="step-number">${index + 1}</div>
        <div class="step-content">
          <h4 class="step-title" data-jcp-path="${basePath}.${index}.title">${esc(data.title || '')}</h4>
          <ul class="jcp-step-checklist jcp-niche-checklist" data-jcp-array="${linesPath}">${linesHtml}</ul>
        </div>
      </div>`;
  };

  const buildStatBadge = (basePath, index, data) => {
    const path = `${basePath}.${index}`;
    const value = String(data?.value ?? '');
    const label = String(data?.label ?? '');
    const detail = String(data?.detail ?? '');
    const cssClass = data?.css_class || STAT_BADGE_CLASSES[index % STAT_BADGE_CLASSES.length];
    const icon = data?.icon || STAT_BADGE_ICONS[index % STAT_BADGE_ICONS.length];
    const labelHtml = value || label
      ? `<span data-jcp-path="${path}.value">${esc(value)}</span>${label ? `<span data-jcp-path="${path}.label">${esc(` ${label}`)}</span>` : ''}`
      : esc(`${value} ${label}`.trim());
    const detailHtml = detail
      ? `<span class="meta-detail" data-jcp-path="${path}.detail">${esc(detail)}</span>`
      : '';
    return `
      <div class="meta-item jcp-collection-item ${cssClass}" data-jcp-array-item="${index}">
        <div class="meta-label">
          <img src="${iconUrl(icon)}" class="meta-icon" alt="" width="20" height="20" />
          <strong>${labelHtml}</strong>
        </div>
        ${detailHtml}
      </div>`;
  };

  const buildConversionPoint = (basePath, index, text) => `
    <div class="conversion-point" data-jcp-array-item="${index}">
      <div class="conversion-point-icon">${CHECK_SVG}</div>
      <div class="conversion-point-text">
        <strong data-jcp-path="${basePath}.${index}">${esc(text)}</strong>
      </div>
    </div>`;

  const buildChecklistItem = (basePath, index, text) => `<li data-jcp-array-item="${index}"><span class="jcp-step-checklist__icon" aria-hidden="true">${CHECK_SVG}</span><span class="jcp-step-checklist__text" data-jcp-path="${basePath}.${index}">${esc(cleanStepLineText(text))}</span></li>`;

  const buildListItem = (basePath, index, text) => `<li data-jcp-array-item="${index}"><span class="jcp-checklist-item__text" data-jcp-path="${basePath}.${index}">${esc(cleanStepLineText(text))}</span></li>`;

  const buildTagItem = (basePath, index, text) => `<li data-jcp-array-item="${index}"><span class="jcp-checklist-item__text" data-jcp-path="${basePath}.${index}">${esc(cleanStepLineText(text))}</span></li>`;

  const buildFaqItem = (index, item) => {
    const qPath = `faq.items.${index}.q`;
    const aPath = `faq.items.${index}.a`;
    return `
      <details class="faq-item" id="faq-${index}" data-jcp-array-item="${index}">
        <summary data-jcp-path="${qPath}">${esc(item.q || '')}</summary>
        <p data-jcp-path="${aPath}">${esc(item.a || '')}</p>
      </details>`;
  };

  const buildGuaranteeCard = (basePath, index, data) => {
    const path = `${basePath}.${index}`;
    const imageBlock = data.image_url
      ? `<img src="${esc(data.image_url)}" alt="${esc(data.image_alt || '')}" class="guarantee-image jcp-editable-media-image" loading="lazy" data-jcp-media-url-path="${path}.image_url" data-jcp-media-alt-path="${path}.image_alt" data-jcp-media-types="image" />`
      : `<div class="guarantee-image guarantee-image--empty" data-jcp-media-url-path="${path}.image_url" data-jcp-media-alt-path="${path}.image_alt" data-jcp-media-types="image"></div>`;
    const badge = data.badge
      ? `<div class="guarantee-badge" data-jcp-path="${path}.badge">${esc(data.badge)}</div>`
      : '';
    const stat = data.stat_number
      ? `<div class="guarantee-stat"><span class="stat-number" data-jcp-path="${path}.stat_number">${esc(data.stat_number)}</span><span class="stat-label" data-jcp-path="${path}.stat_label">${esc(data.stat_label || '')}</span></div>`
      : '';
    const faqTarget = data.faq_target ? ` data-faq-target="${esc(data.faq_target)}"` : '';
    return `
      <a href="#faq" class="guarantee-item" data-jcp-array-item="${index}"${faqTarget}>
        <div class="guarantee-image-wrapper jcp-editable-media-wrap">${imageBlock}${badge}</div>
        <div class="guarantee-content">
          <strong data-jcp-path="${path}.title">${esc(data.title || '')}</strong>
          <p data-jcp-path="${path}.body">${esc(data.body || '')}</p>
          ${stat}
        </div>
      </a>`;
  };

  const buildItemHtml = (basePath, index, data, container) => {
    if (typeof data === 'string') {
      if (basePath.endsWith('.lines')) return buildChecklistItem(basePath, index, data);
      if (basePath.endsWith('.points') || basePath.endsWith('.bullets')) return buildConversionPoint(basePath, index, data);
      if (basePath.includes('team_already') || basePath.includes('turns_into')) return buildListItem(basePath, index, data);
      if (basePath.endsWith('.job_types')) return buildTagItem(basePath, index, data);
      return '';
    }
    if (basePath === 'faq.items') return buildFaqItem(index, data);
    if (basePath === 'core_mechanic') return buildStatBadge(basePath, index, data);
    if (basePath === 'how_it_works.steps') return buildTimelineStep(basePath, index, data);
    if (basePath === 'who_its_for.audiences' && container.classList.contains('guarantees-grid')) {
      return buildGuaranteeCard(basePath, index, data);
    }
    if (basePath.endsWith('.items') || basePath.endsWith('.features') || basePath.endsWith('.pain_points') || basePath.endsWith('.audiences')) {
      return buildFactorCard(basePath, index, data);
    }
    return '';
  };

  const formatStepNumber = (position) => {
    const numeric = api?.getPath?.(api.flatContent, 'how_it_works.numeric_steps');
    const n = position + 1;
    return numeric ? String(n) : String(n).padStart(2, '0');
  };

  const updateTimelineStepNumbers = (container) => {
    const basePath = 'how_it_works.steps';
    const containers = container
      ? [container]
      : [...document.querySelectorAll(`.timeline-steps[data-jcp-array="${basePath}"]`)];
    containers.forEach((stepsContainer) => {
      const steps = stepsContainer.querySelectorAll(':scope > .timeline-step');
      steps.forEach((step, index) => {
        step.dataset.jcpArrayItem = String(index);
        const numEl = step.querySelector(':scope > .step-number');
        if (numEl) numEl.textContent = formatStepNumber(index);
        step.querySelectorAll('[data-jcp-path]').forEach((el) => {
          const path = el.getAttribute('data-jcp-path');
          if (!path || !path.startsWith(`${basePath}.`)) return;
          const rest = path.slice(basePath.length + 1).split('.');
          rest[0] = String(index);
          el.setAttribute('data-jcp-path', `${basePath}.${rest.join('.')}`);
        });
      });
    });
  };

  const arrayPathFrom = (el) => el.getAttribute('data-jcp-array') || el.dataset.jcpArray || '';

  const arrayItemsHost = (container) => {
    if (!container) return null;
    let host = container.querySelector(':scope > .conversion-points__columns');
    if (!host && container.classList.contains('conversion-points--columns')) {
      host = document.createElement('div');
      host.className = 'conversion-points__columns';
      const perCol = parseInt(container.getAttribute('data-jcp-per-column') || '5', 10);
      host.style.setProperty('--jcp-points-per-column', String(perCol));
      const addBtn = container.querySelector(':scope > .jcp-collection-add');
      if (addBtn) container.insertBefore(host, addBtn);
      else container.appendChild(host);
    }
    return host || container;
  };

  const arrayItemsIn = (container) => {
    const host = arrayItemsHost(container);
    if (!host) return [];
    return [...host.querySelectorAll(':scope > [data-jcp-array-item]')];
  };

  const rebuildArrayContainer = (container) => {
    const basePath = arrayPathFrom(container);
    if (!basePath || !api) return;
    const arr = getArray(basePath);
    const host = arrayItemsHost(container);
    host.querySelectorAll(':scope > [data-jcp-array-item]').forEach((el) => el.remove());
    container.querySelectorAll(':scope > .jcp-collection-add').forEach((el) => el.remove());
    const temp = document.createElement('div');
    arr.forEach((item, index) => {
      const html = buildItemHtml(basePath, index, item, container);
      if (!html) return;
      temp.innerHTML = html.trim();
      if (temp.firstElementChild) host.appendChild(temp.firstElementChild);
    });
    if (basePath === 'how_it_works.steps') updateTimelineStepNumbers(container);
  };

  const syncOptionalSlotsFromContent = () => {
    if (!api) return;
    document.querySelectorAll('[data-jcp-optional]').forEach((slot) => {
      const path = slot.dataset.jcpOptional;
      if (!path) return;
      const kind = slot.dataset.jcpOptionalKind || 'cta';
      const data = api.getPath(api.flatContent, path);
      const hasLabel = data && typeof data === 'object' && String(data.label || '').trim() !== '';
      if (!hasLabel) {
        slot.innerHTML = '';
        slot.classList.add('is-empty');
      } else {
        const tpl = OPTIONAL_TEMPLATES[kind] || OPTIONAL_TEMPLATES.cta;
        slot.innerHTML = tpl(path, data);
        slot.classList.remove('is-empty');
      }
    });
  };

  const syncCollectionsFromContent = () => {
    if (!api) return;
    arrayContainers().forEach(rebuildArrayContainer);
    syncOptionalSlotsFromContent();
    updateTimelineStepNumbers();
  };

  const addArrayItem = (container, basePath) => {
    if (!api || typeof api.collectFromDom !== 'function') return;
    api.collectFromDom();
    const arr = getArray(basePath);
    const factory = arrayDefaultFactory(basePath);
    const data = factory(arr.length);
    arr.push(data);
    setArray(basePath, arr);
    rebuildArrayContainer(container);
    refreshCollections();
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    if (typeof window.JCP_REFRESH_PAGE_MEDIA_UI === 'function') {
      window.JCP_REFRESH_PAGE_MEDIA_UI();
    }
    api.recordChange();
  };

  const removeArrayItem = (container, basePath, index) => {
    if (!api || typeof api.collectFromDom !== 'function') return;
    api.collectFromDom();
    const arr = getArray(basePath);
    if (index < 0 || index >= arr.length) return;
    arr.splice(index, 1);
    setArray(basePath, arr);
    rebuildArrayContainer(container);
    refreshCollections();
    if (typeof window.JCP_REFRESH_INLINE_EDITABLE === 'function') {
      window.JCP_REFRESH_INLINE_EDITABLE();
    }
    if (typeof window.JCP_REFRESH_PAGE_MEDIA_UI === 'function') {
      window.JCP_REFRESH_PAGE_MEDIA_UI();
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
    if (!api || typeof api.collectFromDom !== 'function') return;
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
    if (!slot.querySelector('.jcp-optional-restore')) {
      const ph = document.createElement('button');
      ph.type = 'button';
      ph.className = 'jcp-optional-restore';
      ph.textContent = slot.dataset.jcpOptionalLabel || 'Add button';
      slot.appendChild(ph);
    }
    slot.querySelectorAll('.jcp-optional-restore').forEach(bindOptionalRestore);
  };

  const bindRemoveButton = (btn, container) => {
    if (!btn || btn.dataset.jcpActionBound === '1') return;
    btn.dataset.jcpActionBound = '1';
    btn.type = 'button';
    btn.setAttribute('contenteditable', 'false');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!isEditingActive()) return;
      if (btn.classList.contains('jcp-collection-remove--optional')) {
        const slot = btn.closest('[data-jcp-optional]');
        if (slot) removeOptional(slot);
        return;
      }
      const item = btn.closest('[data-jcp-array-item]');
      const itemContainer = item?.closest('[data-jcp-array]') || container;
      const basePath = itemContainer ? arrayPathFrom(itemContainer) : '';
      if (!item || !itemContainer || !basePath) return;
      const index = parseInt(item.getAttribute('data-jcp-array-item') || item.dataset.jcpArrayItem, 10);
      if (!Number.isNaN(index)) removeArrayItem(itemContainer, basePath, index);
    });
  };

  const bindAddButton = (btn, container) => {
    if (!btn || btn.dataset.jcpActionBound === '1') return;
    const basePath = arrayPathFrom(container);
    if (!basePath) return;
    btn.dataset.jcpActionBound = '1';
    btn.type = 'button';
    btn.setAttribute('contenteditable', 'false');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!isEditingActive()) return;
      addArrayItem(container, basePath);
    });
  };

  const bindOptionalRestore = (btn) => {
    if (!btn || btn.dataset.jcpActionBound === '1') return;
    btn.dataset.jcpActionBound = '1';
    btn.type = 'button';
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (!isEditingActive()) return;
      const slot = btn.closest('[data-jcp-optional]');
      if (slot) restoreOptional(slot);
    });
  };

  const bindCollectionControls = (container) => {
    if (!container) return;
    container.querySelectorAll(':scope > .jcp-collection-add').forEach((btn) => bindAddButton(btn, container));
    arrayItemsIn(container).forEach((item) => {
      item.classList.add('jcp-collection-item');
      const removeBtn = item.querySelector(':scope > .jcp-collection-remove')
        || item.querySelector('.jcp-collection-remove');
      if (removeBtn) bindRemoveButton(removeBtn, container);
    });
  };

  const injectRemoveButton = (item) => {
    item.classList.add('jcp-collection-item');
    let btn = item.querySelector(':scope > .jcp-collection-remove')
      || item.querySelector('.jcp-collection-remove');
    if (!btn) {
      btn = document.createElement('button');
      btn.className = 'jcp-collection-remove';
      if (item.tagName === 'LI') btn.classList.add('jcp-collection-remove--list-item');
      btn.setAttribute('aria-label', 'Remove item');
      btn.title = 'Remove';
    btn.textContent = '×';
    item.appendChild(btn);
    }
    const container = item.closest('[data-jcp-array]');
    bindRemoveButton(btn, container);
  };

  const injectOptionalControls = (slot) => {
    const content = slot.querySelector('a, .btn');
    let removeBtn = slot.querySelector('.jcp-collection-remove');
    if (content && !removeBtn) {
      removeBtn = document.createElement('button');
      removeBtn.className = 'jcp-collection-remove jcp-collection-remove--optional';
      removeBtn.setAttribute('aria-label', 'Remove');
      removeBtn.title = 'Remove';
      removeBtn.textContent = '×';
      slot.classList.add('jcp-optional-slot');
      slot.appendChild(removeBtn);
    }
    if (removeBtn) bindRemoveButton(removeBtn, null);
    if (isOptionalEmpty(slot.dataset.jcpOptional)) {
      slot.classList.add('is-empty');
      ensureOptionalPlaceholder(slot);
      slot.querySelectorAll('.jcp-optional-restore').forEach(bindOptionalRestore);
    } else {
      slot.classList.remove('is-empty');
      slot.querySelector('.jcp-optional-restore')?.remove();
    }
  };

  const injectAddButton = (container) => {
    let btn = container.querySelector(':scope > .jcp-collection-add');
    if (!btn) {
      btn = document.createElement('button');
      btn.className = 'jcp-collection-add';
      btn.textContent = addButtonLabel(container);
      container.appendChild(btn);
    }
    bindAddButton(btn, container);
  };

  const arrayContainers = () => [...document.querySelectorAll('[data-jcp-array]')].sort((a, b) => {
    const depth = (path) => (path ? path.split('.').length : 0);
    return depth(arrayPathFrom(a)) - depth(arrayPathFrom(b));
  });

  const teardownCollections = () => {
    document.querySelectorAll('.jcp-collection-remove, .jcp-collection-add, .jcp-optional-restore').forEach((el) => el.remove());
    document.querySelectorAll('.jcp-collection-item, .jcp-optional-slot').forEach((el) => {
      el.classList.remove('jcp-collection-item', 'jcp-optional-slot', 'is-empty');
    });
  };

  const isEditingActive = () => {
    if (document.body.classList.contains('jcp-inline-editing')) return true;
    if (!api) return false;
    return typeof api.editing === 'function' && api.editing();
  };

  const refreshCollections = () => {
    if (!isEditingActive()) return;

    arrayContainers().forEach((container) => {
      arrayItemsIn(container).forEach(injectRemoveButton);
      injectAddButton(container);
      bindCollectionControls(container);
    });

    document.querySelectorAll('[data-jcp-optional]').forEach(injectOptionalControls);
    updateTimelineStepNumbers();
  };

  const init = (editorApi) => {
    api = editorApi;
    window.JCP_REFRESH_COLLECTIONS = refreshCollections;
    window.JCP_TEARDOWN_COLLECTIONS = teardownCollections;
    window.JCP_SYNC_COLLECTIONS_FROM_CONTENT = syncCollectionsFromContent;

    if (isEditingActive()) refreshCollections();
  };

  window.jcpCollectionAddClick = (btn, e) => {
    if (e?.preventDefault) e.preventDefault();
    if (e?.stopPropagation) e.stopPropagation();
    if (!api || !isEditingActive()) return false;
    const container = btn?.closest?.('[data-jcp-array]');
    const basePath = container ? arrayPathFrom(container) : '';
    if (container && basePath) addArrayItem(container, basePath);
    return false;
  };

  window.jcpCollectionRemoveClick = (btn, e) => {
    if (e?.preventDefault) e.preventDefault();
    if (e?.stopPropagation) e.stopPropagation();
    if (!api || !isEditingActive()) return false;
    if (btn?.classList?.contains('jcp-collection-remove--optional')) {
      const slot = btn.closest('[data-jcp-optional]');
      if (slot) removeOptional(slot);
      return false;
    }
    const item = btn?.closest?.('[data-jcp-array-item]');
    const container = item?.closest?.('[data-jcp-array]');
    const basePath = container ? arrayPathFrom(container) : '';
    if (item && container && basePath) {
      const index = parseInt(item.getAttribute('data-jcp-array-item') || item.dataset.jcpArrayItem, 10);
      if (!Number.isNaN(index)) removeArrayItem(container, basePath, index);
    }
    return false;
  };

  if (window.__JCP_EDITOR_API__) init(window.__JCP_EDITOR_API__);
  window.JCP_INIT_COLLECTION_EDITOR = init;
})();
