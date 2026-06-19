/**
 * Front-end media picker, per-instance alt text, type toggle, column drag-swap, and optional media links.
 */
(() => {
  const MEDIA_TYPES = [
    { value: 'image', label: 'Image' },
    { value: 'video', label: 'Video' },
    { value: 'phone_mockup', label: 'Phone mockup' },
  ];

  const PHONE_MOCKUP_LABELS = {
    live_demo: 'Live demo phone',
    app_shell: 'App prototype (static)',
    default: 'Phone mockup',
  };

  let api = null;
  let popover = null;
  let activeMediaContext = null;
  let mediaFrame = null;
  let mediaFrameOpen = false;
  let libraryPicking = false;
  let dragSourceCol = null;

  const legacyKey = (type) => {
    const found = api.registry.find((b) => b.type === type);
    return found?.legacy_key || type;
  };

  const blockLegacyKey = (block) => {
    if (!block) return '';
    if (block.legacy_key) return block.legacy_key;
    return legacyKey(block.type);
  };

  const syncFlatProp = (path, value) => {
    if (!path) return;
    api.setPath(api.flatContent, path, value);
    (api.pageDocument.blocks || []).forEach((block) => {
      const key = blockLegacyKey(block);
      if (!key || (path !== key && !path.startsWith(`${key}.`))) return;
      const rel = path.slice(key.length + 1);
      block.props = block.props || {};
      if (rel) {
        api.setPath(block.props, rel, value);
      } else {
        Object.assign(block.props, value);
      }
    });
  };

  const isWpMediaClick = (target) => !!target?.closest?.(
    '.media-modal, .media-frame, .uploader-window, #wp-media-grid, .attachments-browser'
  );

  const isEditingActive = () => {
    if (document.body.classList.contains('jcp-inline-editing')) return true;
    if (!api) return false;
    return typeof api.editing === 'function' && api.editing();
  };

  const getMediaPaths = (el) => {
    const slot = el.closest('.jcp-media-slot');
    const basePath = slot?.dataset.jcpMediaPath || '';
    const urlPath = el.dataset.jcpMediaUrlPath
      || slot?.dataset.jcpMediaUrlPath
      || (basePath ? `${basePath}.media_url` : null);
    const altPath = el.dataset.jcpMediaAltPath
      || slot?.dataset.jcpMediaAltPath
      || (basePath ? `${basePath}.media_alt` : null);
    const typePath = basePath
      ? `${basePath}.media_type`
      : (el.dataset.jcpMediaUrlPath ? el.dataset.jcpMediaUrlPath.replace(/\.(media_url|image_url|phone_image_url)$/, '.media_type') : null);
    const linkPath = basePath ? `${basePath}.media_link_url` : null;
    const isPhoneScreen = el.dataset.jcpMediaRole === 'phone_screen'
      || el.classList?.contains('hero-phone-image');
    return { slot, urlPath, altPath, typePath, linkPath, basePath, isPhoneScreen, el };
  };

  const resolveWritePaths = (paths, mediaType) => {
    if (paths.isPhoneScreen) {
      return {
        urlPath: `${paths.basePath || 'hero'}.phone_image_url`,
        altPath: `${paths.basePath || 'hero'}.phone_image_alt`,
      };
    }
    if (mediaType === 'phone_mockup') {
      return { urlPath: null, altPath: paths.altPath };
    }
    return { urlPath: paths.urlPath, altPath: paths.altPath };
  };

  const PHONE_MOCKUP_LINK_SELECTOR = '.demo-phone-mockup, .demo-app-phone-mockup, .hero-phone-mockup';

  const MEDIA_HIT_SELECTOR = [
    '.jcp-media-hit',
    '.jcp-editable-media-image',
    '.jcp-hero-slot-image',
    '.jcp-media-text-image',
    '.demo-preview-slot-image',
    '.jcp-media-slot',
    '.jcp-media-text-media',
    '.demo-preview-visual',
    '.jcp-media-video-wrap',
    '.guarantee-image--empty',
    '.conversion-image-wrapper',
    '.conversion-image',
    '.guarantee-image-wrapper',
    '.hero-phone-image-wrap',
    '.jcp-split-col--media',
    PHONE_MOCKUP_LINK_SELECTOR,
  ].join(', ');

  const isEditableMediaNavigationLink = (anchor) => {
    if (!anchor || anchor.tagName !== 'A') return false;
    if (anchor.hasAttribute('data-jcp-href-path')) return false;
    if (anchor.matches(PHONE_MOCKUP_LINK_SELECTOR)) return true;
    if (anchor.closest('.jcp-media-slot, .jcp-hero-visual-column, .jcp-media-text-media, .demo-preview-visual, .jcp-editable-media-wrap')) {
      return true;
    }
    return !!anchor.querySelector('.jcp-editable-media-image, .jcp-hero-slot-image, .hero-phone-image, .jcp-media-text-image');
  };

  const resolveMediaClickTarget = (el) => {
    if (!el) return null;
    if (el.matches('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image, .hero-phone-image')) {
      return el;
    }
    if (el.matches(PHONE_MOCKUP_LINK_SELECTOR)) {
      return el.querySelector('.hero-phone-image, .jcp-editable-media-image, .jcp-hero-slot-image')
        || el.closest('.jcp-media-slot')
        || el;
    }
    if (el.matches('.jcp-media-slot')) {
      const visibleVariant = el.querySelector('.jcp-media-variant:not([hidden])');
      if (visibleVariant?.classList.contains('jcp-media-variant--phone_mockup')) {
        return visibleVariant.querySelector('.hero-phone-image, .jcp-editable-media-image')
          || visibleVariant
          || el;
      }
      const img = visibleVariant?.querySelector('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image');
      if (img) return img;
      return visibleVariant || el.querySelector('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image') || el;
    }
    if (el.matches('.jcp-media-text-media, .demo-preview-visual, .jcp-media-video-wrap')) {
      const slot = el.closest('.jcp-media-slot') || el.querySelector('.jcp-media-slot');
      if (slot) return resolveMediaClickTarget(slot);
    }
    const img = el.querySelector?.('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image, .hero-phone-image');
    if (img) return img;
    if (el.matches('.guarantee-image--empty, .conversion-image-wrapper, .conversion-image, .jcp-split-col--media, .jcp-editable-media-wrap, .guarantee-image-wrapper')) {
      const slot = el.querySelector('.jcp-media-slot');
      if (slot) return resolveMediaClickTarget(slot);
      return el.querySelector('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image') || el;
    }
    return el.closest('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .jcp-media-slot, .demo-phone-mockup, .demo-app-phone-mockup') || null;
  };

  const allowedTypes = (ctx) => {
    const raw = ctx.el?.dataset.jcpMediaTypes || ctx.slot?.dataset.jcpMediaTypes;
    const phoneStyle = ctx.slot?.dataset.jcpPhoneMockupStyle || 'default';
    const phoneLabel = PHONE_MOCKUP_LABELS[phoneStyle] || PHONE_MOCKUP_LABELS.default;
    const relabel = (types) => types.map((t) => (
      t.value === 'phone_mockup' ? { ...t, label: phoneLabel } : t
    ));

    if (!raw) return relabel(MEDIA_TYPES);
    const allowed = raw.split(',').map((s) => s.trim()).filter(Boolean);
    return relabel(MEDIA_TYPES.filter((t) => allowed.includes(t.value)));
  };

  const currentMediaType = (ctx) => {
    if (ctx.isPhoneScreen) return 'phone_mockup';
    if (ctx.typePath) {
      const stored = api.getPath(api.flatContent, ctx.typePath);
      if (stored) return stored;
    }
    if (ctx.slot?.dataset.jcpMediaType) return ctx.slot.dataset.jcpMediaType;
    return 'image';
  };

  const updateVariantVisibility = (slot, type) => {
    if (!slot) return;
    slot.querySelectorAll('.jcp-media-variant').forEach((node) => {
      const match = node.classList.contains(`jcp-media-variant--${type}`);
      if (match) node.removeAttribute('hidden');
      else node.setAttribute('hidden', '');
    });
    slot.dataset.jcpMediaType = type;
  };

  const attachmentUrl = (modelOrJson) => {
    const model = modelOrJson?.get ? modelOrJson : null;
    const data = model ? model.toJSON() : (modelOrJson || {});
    const sizes = data.sizes || model?.get?.('sizes') || {};
    const candidates = [
      sizes.full?.url,
      model?.get?.('url'),
      data.url,
      sizes.large?.url,
      sizes.medium?.url,
      data.link,
      data.guid,
    ].filter(Boolean);
    for (const candidate of candidates) {
      const normalized = normalizeMediaUrl(candidate);
      if (normalized && normalized.includes('/wp-content/')) return normalized;
    }
    return normalizeMediaUrl(candidates[0] || '');
  };

  const normalizeMediaUrl = (url) => {
    if (!url) return '';
    url = String(url).trim();
    if (!url) return '';

    if (url.startsWith('//')) {
      url = `${window.location.protocol}${url}`;
    }

    try {
      const parsed = new URL(url, window.location.origin);
      let path = parsed.pathname || '';

      if (!/^https?:\/\//i.test(url) && !url.startsWith('//')) {
        if (!path.startsWith('/')) path = `/${path}`;
        return `${window.location.origin}${path}${parsed.search}`;
      }

      if (path.startsWith('/wp-content/') && parsed.origin !== window.location.origin) {
        return `${window.location.origin}${path}${parsed.search}`;
      }

      return parsed.href;
    } catch (_) {
      if (url.startsWith('/')) return `${window.location.origin}${url}`;
      const wpIdx = url.indexOf('wp-content/');
      if (wpIdx >= 0) return `${window.location.origin}/${url.slice(wpIdx)}`;
      return url;
    }
  };

  const resolveAttachmentModel = (model) => new Promise((resolve) => {
    const finish = (resolvedModel) => {
      resolve({ model: resolvedModel, url: attachmentUrl(resolvedModel) });
    };

    const immediate = attachmentUrl(model);
    if (immediate && /\/wp-content\/uploads\//.test(immediate)) {
      finish(model);
      return;
    }

    const id = model?.get?.('id');
    if (!id || !window.wp?.media?.attachment) {
      finish(model);
      return;
    }

    wp.media.attachment(id).fetch().then(() => {
      finish(wp.media.attachment(id));
    }).catch(() => finish(model));
  });

  const attachmentIdPath = (urlPath) => {
    if (!urlPath) return null;
    if (urlPath.endsWith('.image_url')) return urlPath.replace(/\.image_url$/, '.image_attachment_id');
    if (urlPath.endsWith('.phone_image_url')) return urlPath.replace(/\.phone_image_url$/, '.phone_image_attachment_id');
    if (urlPath.endsWith('.media_url')) return urlPath.replace(/\.media_url$/, '.media_attachment_id');
    return null;
  };

  const syncMediaAttachmentId = (urlPath, id) => {
    const idPath = attachmentIdPath(urlPath);
    if (!idPath || !id) return;
    syncFlatProp(idPath, id);
  };

  const parseEmbedVideo = (url) => {
    const value = String(url || '').trim();
    if (!value) return null;
    const yt = value.match(/(?:youtube\.com\/(?:watch\?(?:[^&\s]+&)*v=|embed\/|shorts\/|v\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/i);
    if (yt) {
      return {
        provider: 'youtube',
        id: yt[1],
        isShort: /\/shorts\//i.test(value),
        embedUrl: `https://www.youtube.com/embed/${yt[1]}`,
      };
    }
    const vm = value.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
    if (vm) {
      return {
        provider: 'vimeo',
        id: vm[1],
        isShort: false,
        embedUrl: `https://player.vimeo.com/video/${vm[1]}`,
      };
    }
    return null;
  };

  const isEmbedVideoUrl = (url) => !!parseEmbedVideo(url);

  const ensureVideoEmbed = (videoVariant, url) => {
    if (!videoVariant || !url) return;
    const parsed = parseEmbedVideo(url);
    let wrap = videoVariant.querySelector('.jcp-media-video-wrap, .jcp-media-text-video-wrap');

    if (parsed) {
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'jcp-media-text-video-wrap jcp-media-video-wrap';
        videoVariant.innerHTML = '';
        videoVariant.appendChild(wrap);
      }
      wrap.classList.toggle('jcp-media-video-wrap--short', parsed.isShort);
      let iframe = wrap.querySelector('iframe');
      if (!iframe) {
        wrap.innerHTML = '';
        iframe = document.createElement('iframe');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute(
          'allow',
          'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share'
        );
        wrap.appendChild(iframe);
      }
      if (iframe.src !== parsed.embedUrl) iframe.src = parsed.embedUrl;
      return;
    }

    if (!wrap) {
      wrap = document.createElement('div');
      wrap.className = 'jcp-media-text-video-wrap jcp-media-video-wrap';
      videoVariant.innerHTML = '';
      videoVariant.appendChild(wrap);
    }
    wrap.classList.remove('jcp-media-video-wrap--short');
    let video = wrap.querySelector('video');
    if (!video) {
      wrap.innerHTML = '';
      video = document.createElement('video');
      video.className = 'jcp-media-text-video jcp-media-video-file';
      video.controls = true;
      video.playsInline = true;
      video.preload = 'metadata';
      wrap.appendChild(video);
    }
    if (video.src !== url) video.src = url;
  };

  const resolveMediaUrlPaths = (paths) => {
    const base = paths.basePath || '';
    const slotImagePath = paths.slot?.dataset.jcpMediaUrlPath || paths.urlPath;
    const slotVideoPath = paths.slot?.dataset.jcpMediaVideoUrlPath;

    if (slotImagePath?.endsWith('.image_url')) {
      return {
        imageUrlPath: slotImagePath,
        videoUrlPath: slotVideoPath || (base ? `${base}.media_url` : null),
      };
    }

    if (base === 'hero') {
      return {
        imageUrlPath: 'hero.image_url',
        videoUrlPath: 'hero.media_url',
      };
    }

    const shared = paths.urlPath || (base ? `${base}.media_url` : null);
    return { imageUrlPath: shared, videoUrlPath: shared };
  };

  const readImageUrl = (urlPaths) => {
    if (!urlPaths.imageUrlPath) return '';
    const stored = api.getPath(api.flatContent, urlPaths.imageUrlPath) || '';
    return isEmbedVideoUrl(stored) ? '' : stored;
  };

  const isVideoMediaUrl = (url) => {
    if (!url) return false;
    const value = String(url).trim();
    if (isEmbedVideoUrl(value)) return true;
    return /\.(mp4|webm|ogg|mov)(\?|#|$)/i.test(value);
  };

  const isImageMediaUrl = (url) => {
    if (!url || isVideoMediaUrl(url)) return false;
    const value = String(url).trim();
    if (/\/wp-content\/uploads\//i.test(value)) return true;
    return /\.(jpe?g|png|gif|webp|svg|avif)(\?|#|$)/i.test(value);
  };

  const readVideoUrl = (urlPaths) => {
    if (!urlPaths.videoUrlPath) return '';
    const stored = String(api.getPath(api.flatContent, urlPaths.videoUrlPath) || '').trim();
    if (!stored || isImageMediaUrl(stored)) return '';
    return isVideoMediaUrl(stored) ? stored : '';
  };

  const writeMediaUrl = (urlPath, url, { normalize = true } = {}) => {
    if (!urlPath) return;
    const value = url ? (normalize ? normalizeMediaUrl(url) : String(url).trim()) : '';
    syncFlatProp(urlPath, value);
  };

  const syncMediaAlt = (altPath, alt) => {
    if (!altPath) return;
    syncFlatProp(altPath, alt);
    if (altPath.endsWith('.image_alt')) {
      syncFlatProp(altPath.replace(/\.image_alt$/, '.media_alt'), alt);
    } else if (altPath.endsWith('.media_alt')) {
      syncFlatProp(altPath.replace(/\.media_alt$/, '.image_alt'), alt);
    }
  };

  const ensureSlotImage = (slot, urlPath, altPath, url, alt, extraClass = '') => {
    if (!slot || !urlPath || !url) return null;
    let img = slot.querySelector(`[data-jcp-media-url-path="${urlPath}"]`);
    if (!img) {
      const variant = slot.querySelector('.jcp-media-variant--image');
      if (!variant) return null;
      img = document.createElement('img');
      img.className = `jcp-editable-media-image${extraClass ? ` ${extraClass}` : ''}`;
      img.dataset.jcpMediaUrlPath = urlPath;
      if (altPath) img.dataset.jcpMediaAltPath = altPath;
      img.loading = 'lazy';
      variant.appendChild(img);
    }
    img.src = normalizeMediaUrl(url);
    if (alt !== undefined) img.alt = alt;
    img.removeAttribute('hidden');
    return img;
  };

  const updateMediaDom = (urlPath, url, altPath, alt, slot = null) => {
    if (!urlPath) return;
    const isVideo = isVideoMediaUrl(url);
    const targetSlot = slot || document.querySelector(
      `.jcp-media-slot[data-jcp-media-url-path="${urlPath}"], .jcp-media-slot[data-jcp-media-path="${urlPath.replace(/\.(media_url|image_url|phone_image_url)$/, '')}"]`
    );

    if (url && targetSlot && !isVideo) {
      const extraClass = targetSlot.closest('.conversion-image-wrapper') ? 'conversion-image'
        : (targetSlot.closest('.jcp-media-text-media') ? 'jcp-media-text-image' : '');
      ensureSlotImage(targetSlot, urlPath, altPath, url, alt, extraClass);
    }

    if (!isVideo) {
      document.querySelectorAll(`[data-jcp-media-url-path="${urlPath}"]`).forEach((node) => {
        if (node.tagName === 'IMG') {
          if (url) node.src = normalizeMediaUrl(url);
          if (altPath && alt !== undefined) node.alt = alt;
        } else if (node.classList.contains('guarantee-image--empty') && url) {
          const img = document.createElement('img');
          img.src = normalizeMediaUrl(url);
          img.alt = alt || '';
          img.className = 'guarantee-image jcp-editable-media-image';
          img.loading = 'lazy';
          img.dataset.jcpMediaUrlPath = urlPath;
          if (altPath) img.dataset.jcpMediaAltPath = altPath;
          img.dataset.jcpMediaTypes = 'image';
          node.replaceWith(img);
        }
      });
    }

    if (url) {
      const base = urlPath.replace(/\.(media_url|image_url|phone_image_url)$/, '');
      const videoSlot = targetSlot || document.querySelector(`.jcp-media-slot[data-jcp-media-path="${base}"]`);
      const videoWrap = videoSlot?.querySelector('.jcp-media-variant--video');
      if (videoWrap && isVideo) {
        ensureVideoEmbed(videoWrap, url);
      }
    }
  };

  const ensurePopover = () => {
    if (popover) return popover;
    popover = document.createElement('div');
    popover.className = 'jcp-media-popover';
    popover.hidden = true;
    popover.setAttribute('hidden', '');
    popover.innerHTML = `
      <div class="jcp-media-popover__header">
        <strong>Edit media</strong>
        <button type="button" class="jcp-media-popover__close" aria-label="Close">×</button>
      </div>
      <div class="jcp-media-popover__body">
        <label class="jcp-media-popover__field">
          <span>Media type</span>
          <select id="jcpMediaTypeSelect"></select>
        </label>
        <label class="jcp-media-popover__field jcp-media-popover__field--image">
          <span id="jcpMediaImageUrlLabel">Image URL</span>
          <input type="text" id="jcpMediaImageUrlInput" placeholder="Or choose from library below" spellcheck="false" autocomplete="off">
        </label>
        <label class="jcp-media-popover__field jcp-media-popover__field--alt">
          <span>ALT text <small>(this page only)</small></span>
          <input type="text" id="jcpMediaAltInput" placeholder="Describe this image for accessibility and SEO">
        </label>
        <label class="jcp-media-popover__field jcp-media-popover__field--video" hidden>
          <span>Video URL</span>
          <input type="url" id="jcpMediaVideoUrlInput" placeholder="YouTube, YouTube Shorts, Vimeo, or MP4 URL">
        </label>
        <label class="jcp-media-popover__field jcp-media-popover__field--link">
          <span>Link URL <small>(optional)</small></span>
          <input type="url" id="jcpMediaLinkInput" placeholder="Leave empty for no link">
        </label>
      </div>
      <div class="jcp-media-popover__actions">
        <button type="button" class="btn btn-secondary" id="jcpMediaReplaceBtn">Choose from library</button>
        <button type="button" class="btn btn-primary" id="jcpMediaApplyBtn">Apply</button>
      </div>
    `;
    document.body.appendChild(popover);

    popover.querySelector('.jcp-media-popover__close').addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      closePopover(true);
    });
    popover.querySelector('#jcpMediaApplyBtn').addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      applyPopover();
    });
    popover.querySelector('#jcpMediaReplaceBtn').addEventListener('click', openLibrary);
    popover.querySelector('#jcpMediaTypeSelect').addEventListener('change', onTypeSelectChange);

    document.addEventListener('click', (e) => {
      if (!popover || popover.hidden || mediaFrameOpen || libraryPicking) return;
      if (isWpMediaClick(e.target)) return;
      if (popover.contains(e.target)) return;
      if (e.target.closest('.jcp-media-hit, .jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image, .jcp-media-slot, .guarantee-image--empty, .jcp-editable-media-wrap, .conversion-image-wrapper, .jcp-split-col--media, .jcp-media-text-media, .demo-preview-visual')) return;
      closePopover();
    });

    return popover;
  };

  const togglePopoverField = (el, show) => {
    if (!el) return;
    el.hidden = !show;
    if (show) el.removeAttribute('hidden');
    else el.setAttribute('hidden', '');
  };

  const onTypeSelectChange = () => {
    const type = popover.querySelector('#jcpMediaTypeSelect').value;
    const videoField = popover.querySelector('.jcp-media-popover__field--video');
    const imageField = popover.querySelector('.jcp-media-popover__field--image');
    const altField = popover.querySelector('.jcp-media-popover__field--alt');
    const linkField = popover.querySelector('.jcp-media-popover__field--link');
    const replaceBtn = popover.querySelector('#jcpMediaReplaceBtn');
    const imageLabel = popover.querySelector('#jcpMediaImageUrlLabel');

    const showVideo = type === 'video';
    const showImage = type === 'image' || (type === 'phone_mockup' && activeMediaContext?.isPhoneScreen);
    const showAlt = type !== 'video';
    const showLink = type !== 'video' && !!activeMediaContext?.linkPath;
    const showLibrary = showImage && type !== 'video';

    togglePopoverField(videoField, showVideo);
    togglePopoverField(imageField, showImage);
    togglePopoverField(altField, showAlt);
    togglePopoverField(linkField, showLink);
    replaceBtn.hidden = !showLibrary;
    if (replaceBtn.hidden) replaceBtn.setAttribute('hidden', '');
    else replaceBtn.removeAttribute('hidden');

    imageLabel.textContent = type === 'phone_mockup' && activeMediaContext?.isPhoneScreen
      ? 'Phone screen photo URL'
      : 'Image URL';
    replaceBtn.textContent = type === 'phone_mockup' && activeMediaContext?.isPhoneScreen
      ? 'Choose phone photo from library'
      : 'Choose from library';

    if (activeMediaContext) {
      const urlPaths = resolveMediaUrlPaths(activeMediaContext);
      if (type === 'video') {
        popover.querySelector('#jcpMediaVideoUrlInput').value = readVideoUrl(urlPaths);
      } else if (type === 'image' || type === 'phone_mockup') {
        popover.querySelector('#jcpMediaImageUrlInput').value = readImageUrl(urlPaths);
      }
      const rect = activeMediaContext.el.getBoundingClientRect?.() || { top: 0, left: 0, bottom: 0 };
      window.requestAnimationFrame(() => positionPopover(rect));
    }
  };

  const openPopover = (el) => {
    if (!api || !isEditingActive()) return;
    const target = resolveMediaClickTarget(el);
    if (!target) return;
    const paths = getMediaPaths(target);
    if (!paths.urlPath && !paths.altPath && !paths.isPhoneScreen && !paths.basePath) return;

    activeMediaContext = { ...paths, el: target };

    ensurePopover();
    const types = allowedTypes(activeMediaContext);
    const select = popover.querySelector('#jcpMediaTypeSelect');
    select.innerHTML = types.map((t) => `<option value="${t.value}">${t.label}</option>`).join('');

    const type = currentMediaType(activeMediaContext);
    select.value = types.some((t) => t.value === type) ? type : (types[0]?.value || 'image');

    const writePaths = resolveWritePaths(activeMediaContext, select.value);
    const urlPaths = resolveMediaUrlPaths(activeMediaContext);

    if (urlPaths.imageUrlPath) {
      const polluted = api.getPath(api.flatContent, urlPaths.imageUrlPath);
      if (isEmbedVideoUrl(polluted)) {
        syncFlatProp(urlPaths.imageUrlPath, '');
      }
    }

    popover.querySelector('#jcpMediaAltInput').value = writePaths.altPath ? (api.getPath(api.flatContent, writePaths.altPath) || '') : '';
    popover.querySelector('#jcpMediaImageUrlInput').value = readImageUrl(urlPaths);
    popover.querySelector('#jcpMediaVideoUrlInput').value = readVideoUrl(urlPaths);
    popover.querySelector('#jcpMediaLinkInput').value = paths.linkPath ? (api.getPath(api.flatContent, paths.linkPath) || '') : '';

    onTypeSelectChange();

    const rect = (target.getBoundingClientRect ? target : el).getBoundingClientRect();
    positionPopover(rect);
  };

  const positionPopover = (anchorRect) => {
    if (!popover) return;
    const pad = 12;
    const bar = document.querySelector('.jcp-niche-edit-bar');
    const topInset = (bar?.offsetHeight || 0) + pad;

    popover.hidden = false;
    popover.removeAttribute('hidden');
    popover.style.display = 'flex';
    popover.style.visibility = 'hidden';
    popover.style.maxHeight = '';
    popover.style.left = `${pad}px`;
    popover.style.top = `${topInset}px`;

    const width = popover.offsetWidth;
    let height = popover.offsetHeight;
    const maxHeight = window.innerHeight - topInset - pad;
    popover.style.maxHeight = `${maxHeight}px`;

    height = Math.min(popover.offsetHeight, maxHeight);

    let top = anchorRect.bottom + 8;
    if (top + height > window.innerHeight - pad) {
      top = anchorRect.top - height - 8;
    }
    if (top < topInset) {
      top = topInset;
    }

    const left = Math.max(pad, Math.min(anchorRect.left, window.innerWidth - width - pad));

    // If still overflowing, anchor from bottom of viewport.
    if (top + height > window.innerHeight - pad) {
      top = Math.max(topInset, window.innerHeight - pad - height);
    }

    popover.style.top = `${top}px`;
    popover.style.left = `${left}px`;
    popover.style.visibility = '';
  };

  const closePopover = (force = false) => {
    if (!popover) return;
    if (!force && (mediaFrameOpen || libraryPicking)) return;
    popover.hidden = true;
    popover.setAttribute('hidden', '');
    popover.style.display = 'none';
    if (!mediaFrameOpen && !libraryPicking) activeMediaContext = null;
  };

  const applyMediaValues = (mediaType, url, alt, attachmentId = null, ctx = activeMediaContext) => {
    if (!ctx) return;
    const writePaths = resolveWritePaths(ctx, mediaType);
    const urlPaths = resolveMediaUrlPaths(ctx);

    if (mediaType === 'video') {
      const videoUrl = String(url || '').trim();
      if (urlPaths.videoUrlPath) writeMediaUrl(urlPaths.videoUrlPath, videoUrl, { normalize: false });
    } else {
      const resolvedUrl = url ? normalizeMediaUrl(url) : '';
      if (resolvedUrl && writePaths.urlPath) {
        writeMediaUrl(writePaths.urlPath, resolvedUrl);
        if (attachmentId) syncMediaAttachmentId(writePaths.urlPath, attachmentId);
        if (ctx.basePath === 'hero' && mediaType === 'image') {
          writeMediaUrl('hero.image_url', resolvedUrl);
          writeMediaUrl('hero.media_url', resolvedUrl);
          if (attachmentId) {
            syncMediaAttachmentId('hero.image_url', attachmentId);
            syncMediaAttachmentId('hero.media_url', attachmentId);
          }
        }
      }
    }

    if (writePaths.altPath && mediaType !== 'video') syncMediaAlt(writePaths.altPath, alt);
    if (ctx.typePath) syncFlatProp(ctx.typePath, mediaType);
    if (ctx.linkPath && popover && mediaType !== 'video') {
      syncFlatProp(ctx.linkPath, popover.querySelector('#jcpMediaLinkInput').value.trim());
    }

    updateVariantVisibility(ctx.slot, mediaType);
    if (mediaType === 'video') {
      const videoUrl = String(url || '').trim();
      if (urlPaths.videoUrlPath) {
        updateMediaDom(urlPaths.videoUrlPath, videoUrl, writePaths.altPath, alt, ctx.slot);
      }
    } else if (mediaType === 'image' || (mediaType === 'phone_mockup' && ctx.isPhoneScreen)) {
      const resolvedUrl = url ? normalizeMediaUrl(url) : '';
      if (resolvedUrl && writePaths.urlPath) {
        updateMediaDom(writePaths.urlPath, resolvedUrl, writePaths.altPath, alt, ctx.slot);
      }
    }
    api.recordChange();
  };

  const applyPopover = () => {
    const ctx = activeMediaContext;
    const mediaType = popover.querySelector('#jcpMediaTypeSelect').value;
    const alt = popover.querySelector('#jcpMediaAltInput').value.trim();
    let url = '';
    if (mediaType === 'video') {
      url = popover.querySelector('#jcpMediaVideoUrlInput').value.trim();
    } else if (mediaType === 'phone_mockup' && ctx?.isPhoneScreen) {
      url = normalizeMediaUrl(popover.querySelector('#jcpMediaImageUrlInput').value.trim());
    } else if (mediaType === 'image') {
      url = normalizeMediaUrl(popover.querySelector('#jcpMediaImageUrlInput').value.trim());
    }
    if (ctx) applyMediaValues(mediaType, url, alt, null, ctx);
    closePopover(true);
    activeMediaContext = null;
  };

  const openLibrary = () => {
    if (!window.wp?.media) {
      window.alert('Media library is not available. Try refreshing the page.');
      return;
    }
    if (!activeMediaContext) return;

    if (!mediaFrame) {
      mediaFrame = window.wp.media({
        title: api.strings?.mediaTitle || 'Choose or upload media',
        button: { text: api.strings?.mediaButton || 'Use this media' },
        multiple: false,
        library: { type: 'image' },
      });

      mediaFrame.on('open', () => {
        mediaFrameOpen = true;
      });
      mediaFrame.on('close', () => {
        if (!libraryPicking) mediaFrameOpen = false;
      });

      mediaFrame.on('select', () => {
        const ctx = activeMediaContext;
        if (!ctx) return;
        libraryPicking = true;
        const model = mediaFrame.state().get('selection').first();
        resolveAttachmentModel(model).then(({ model: resolvedModel, url }) => {
          libraryPicking = false;
          mediaFrameOpen = false;
          if (!url || !ctx) return;
          const attachment = resolvedModel.toJSON ? resolvedModel.toJSON() : resolvedModel;
          const attachmentId = resolvedModel.get?.('id') || attachment.id || null;
          const libAlt = attachment.alt || attachment.title || attachment.filename || '';
          const selectedType = popover.querySelector('#jcpMediaTypeSelect').value;
          const mediaType = selectedType === 'phone_mockup'
            ? 'phone_mockup'
            : (attachment.mime?.startsWith('video/') ? 'video' : 'image');

          if (mediaType === 'phone_mockup' && !ctx.isPhoneScreen) {
            window.alert('Switch to Image first, or click the photo inside the phone screen to replace it.');
            return;
          }

          popover.querySelector('#jcpMediaTypeSelect').value = mediaType;
          popover.querySelector('#jcpMediaImageUrlInput').value = url;
          if (mediaType === 'video') popover.querySelector('#jcpMediaVideoUrlInput').value = url;
          if (libAlt && !popover.querySelector('#jcpMediaAltInput').value.trim()) {
            popover.querySelector('#jcpMediaAltInput').value = libAlt;
          }
          onTypeSelectChange();
          applyMediaValues(
            mediaType,
            url,
            popover.querySelector('#jcpMediaAltInput').value.trim(),
            attachmentId,
            ctx
          );
          closePopover(true);
          activeMediaContext = null;
        }).catch(() => {
          libraryPicking = false;
          mediaFrameOpen = false;
        });
      });
    }

    mediaFrameOpen = true;
    mediaFrame.open();
  };

  const swapColumns = (grid) => {
    const path = grid.dataset.jcpMediaPositionPath;
    if (!path) return;
    const current = api.getPath(api.flatContent, path) === 'left' ? 'left' : 'right';
    const next = current === 'left' ? 'right' : 'left';
    syncFlatProp(path, next);
    grid.classList.remove('jcp-split-layout--media-left', 'jcp-split-layout--media-right');
    grid.classList.add(`jcp-split-layout--media-${next}`);
    const section = grid.closest('.jcp-media-text, .demo-preview-section, .jcp-split-media-block');
    if (section) {
      section.classList.remove('jcp-media-text--media-left', 'jcp-media-text--media-right');
      section.classList.add(`jcp-media-text--media-${next}`);
    }
    api.recordChange();
  };

  const finishColumnDrag = (grid, e) => {
    if (!dragSourceCol) return;
    const dropCol = document.elementFromPoint(e.clientX, e.clientY)?.closest('[data-jcp-split-col]');
    if (dropCol && dropCol !== dragSourceCol && grid.contains(dropCol)) {
      swapColumns(grid);
    }
    dragSourceCol = null;
    grid.classList.remove('jcp-split-layout--dragging');
    grid.querySelectorAll('.jcp-split-col--dragging, .jcp-split-col--drop-target').forEach((node) => {
      node.classList.remove('jcp-split-col--dragging', 'jcp-split-col--drop-target');
    });
  };

  const bindColumnSwap = () => {
    document.querySelectorAll('[data-jcp-split-path]').forEach((grid) => {
      grid.querySelectorAll('[data-jcp-split-col]').forEach((col) => {
        if (col.querySelector('.jcp-col-drag-handle')) return;

        const handle = document.createElement('div');
        handle.className = 'jcp-col-drag-handle';
        handle.setAttribute('role', 'button');
        handle.setAttribute('tabindex', '0');
        handle.setAttribute('aria-label', 'Drag to swap columns');
        handle.title = 'Drag to swap columns';
        col.prepend(handle);

        handle.addEventListener('pointerdown', (e) => {
          if (!isEditingActive()) return;
          e.preventDefault();
          e.stopPropagation();
          dragSourceCol = col;
          handle.setPointerCapture(e.pointerId);
          grid.classList.add('jcp-split-layout--dragging');
          col.classList.add('jcp-split-col--dragging');
        });

        handle.addEventListener('pointermove', (e) => {
          if (!dragSourceCol || dragSourceCol !== col) return;
          grid.querySelectorAll('.jcp-split-col--drop-target').forEach((node) => {
            node.classList.remove('jcp-split-col--drop-target');
          });
          const over = document.elementFromPoint(e.clientX, e.clientY)?.closest('[data-jcp-split-col]');
          if (over && over !== dragSourceCol && grid.contains(over)) {
            over.classList.add('jcp-split-col--drop-target');
          }
        });

        handle.addEventListener('pointerup', (e) => {
          if (dragSourceCol !== col) return;
          finishColumnDrag(grid, e);
          try { handle.releasePointerCapture(e.pointerId); } catch (_) { /* noop */ }
        });

        handle.addEventListener('pointercancel', (e) => {
          if (dragSourceCol !== col) return;
          finishColumnDrag(grid, e);
        });

        handle.addEventListener('keydown', (e) => {
          if (!isEditingActive()) return;
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            swapColumns(grid);
          }
        });
      });
    });
  };

  const markMediaHitAreas = () => {
    document.querySelectorAll(
      '.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .demo-preview-slot-image, .jcp-media-slot, .jcp-media-text-media, .demo-preview-visual, .demo-phone-mockup, .demo-app-phone-mockup, .guarantee-image--empty, .conversion-image-wrapper, .conversion-image, .guarantee-image-wrapper, .hero-phone-image-wrap, .jcp-hero-visual-column'
    ).forEach((el) => {
      el.classList.add('jcp-media-hit');
    });
  };

  const MEDIA_SLOT_SELECTOR = [
    '.jcp-media-slot',
    '.jcp-media-text-media',
    '.demo-preview-visual',
    '.jcp-hero-visual-column',
    '.conversion-image-wrapper',
    '.guarantee-image-wrapper',
    '.hero-phone-image-wrap',
  ].join(', ');

  const bindMediaSlots = () => {
    document.querySelectorAll(MEDIA_SLOT_SELECTOR).forEach((slot) => {
      if (slot.dataset.jcpMediaClickBound === '1') return;
      slot.dataset.jcpMediaClickBound = '1';
      slot.addEventListener('click', (e) => {
        if (!isEditingActive()) return;
        if (e.target.closest(EDITOR_CHROME_SELECTOR)) return;
        if (e.target.closest('.jcp-col-drag-handle')) return;
        if (isWpMediaClick(e.target)) return;
        e.preventDefault();
        e.stopPropagation();
        openPopover(e.target instanceof Element ? e.target : slot);
      }, true);
    });

    document.querySelectorAll('.jcp-editable-media-image, .jcp-hero-slot-image, .jcp-media-text-image, .hero-phone-image, .demo-preview-slot-image').forEach((img) => {
      if (img.dataset.jcpMediaClickBound === '1') return;
      img.dataset.jcpMediaClickBound = '1';
      img.addEventListener('click', (e) => {
        if (!isEditingActive()) return;
        if (e.target.closest(EDITOR_CHROME_SELECTOR)) return;
        e.preventDefault();
        e.stopPropagation();
        openPopover(img);
      }, true);
    });
  };

  const EDITOR_CHROME_SELECTOR = [
    '.jcp-collection-add',
    '.jcp-collection-remove',
    '.jcp-optional-restore',
    '.jcp-niche-edit-bar',
    '.jcp-block-structure',
    '.jcp-block-add-modal',
    '.jcp-media-popover',
    '.jcp-niche-link-popover',
    '.jcp-col-drag-handle',
  ].join(', ');

  const blockLinkNavigation = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') {
      e.stopImmediatePropagation();
    }
  };

  const onDocumentClickCapture = (e) => {
    if (!isEditingActive()) return;
    const target = e.target instanceof Element ? e.target : e.target?.parentElement;
    if (!target) return;
    if (target.closest(EDITOR_CHROME_SELECTOR)) return;
    if (isWpMediaClick(target)) return;

    const mockupLink = target.closest(PHONE_MOCKUP_LINK_SELECTOR);
    if (mockupLink && isEditableMediaNavigationLink(mockupLink)) {
      blockLinkNavigation(e);
      openPopover(mockupLink);
      return;
    }

    const wrappedMediaLink = target.closest('a');
    if (wrappedMediaLink && isEditableMediaNavigationLink(wrappedMediaLink)) {
      blockLinkNavigation(e);
      openPopover(wrappedMediaLink);
      return;
    }

    const mediaHit = target.closest(MEDIA_HIT_SELECTOR);
    if (mediaHit) {
      e.preventDefault();
      e.stopPropagation();
      openPopover(mediaHit);
    }
  };

  const onDocumentMouseDownCapture = (e) => {
    if (!isEditingActive()) return;
    if (e.button !== 0) return;
    if (e.target.closest(EDITOR_CHROME_SELECTOR)) return;
    if (isWpMediaClick(e.target)) return;
    const link = e.target.closest('a');
    if (link && isEditableMediaNavigationLink(link)) {
      e.preventDefault();
    }
  };

  let captureBound = false;
  const bindCaptureListeners = () => {
    if (captureBound) return;
    captureBound = true;
    document.addEventListener('click', onDocumentClickCapture, true);
    document.addEventListener('mousedown', onDocumentMouseDownCapture, true);
  };

  const init = (editorApi) => {
    api = editorApi;
    bindCaptureListeners();
    markMediaHitAreas();
    bindMediaSlots();
    bindColumnSwap();
  };

  window.jcpOpenMediaEditor = (el, e) => {
    if (e?.preventDefault) e.preventDefault();
    if (e?.stopPropagation) e.stopPropagation();
    if (!api || !isEditingActive()) return false;
    openPopover(el);
    return false;
  };

  window.JCP_INIT_PAGE_MEDIA_EDITOR = init;

  window.JCP_REFRESH_PAGE_MEDIA_UI = () => {
    markMediaHitAreas();
    bindMediaSlots();
    bindColumnSwap();
    if (api?.applyMediaPositionToDom) api.applyMediaPositionToDom();
  };

  if (window.__JCP_EDITOR_API__) {
    init(window.__JCP_EDITOR_API__);
  }
})();
