/**
 * Fluent Forms bridge — modal open/close only.
 * Does not relocate step buttons.
 *
 * @package JCP_Core
 */
(function () {
  'use strict';

  var OPEN_CLASS = 'is-open';
  var BODY_CLASS = 'jcp-fluent-modal-open';
  var lastFocus = null;

  function getModal(id) {
    if (id) {
      var byId = document.getElementById(id);
      if (byId && byId.classList.contains('jcp-fluent-quote-modal')) {
        return byId;
      }
    }
    return document.querySelector('.jcp-fluent-quote-modal');
  }

  function openModal(modal) {
    if (!modal) {
      return;
    }
    lastFocus = document.activeElement;
    modal.classList.add(OPEN_CLASS);
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add(BODY_CLASS);
    var closeBtn = modal.querySelector('[data-jcp-form-close]');
    if (closeBtn && typeof closeBtn.focus === 'function') {
      closeBtn.focus();
    }
  }

  function closeModal(modal) {
    if (!modal) {
      return;
    }
    modal.classList.remove(OPEN_CLASS);
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove(BODY_CLASS);
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
    lastFocus = null;
  }

  function resolveModalFromTrigger(el) {
    var target = el.getAttribute('data-jcp-form-target') || '';
    if (target.indexOf('#') === 0) {
      target = target.slice(1);
    }
    if (target) {
      return getModal(target);
    }
    var href = el.getAttribute('href') || '';
    if (href === '#apply' || href === '#jcp-form-modal') {
      var modal = getModal(href === '#apply' ? 'jcp-form-modal' : href.slice(1));
      if (modal) {
        return modal;
      }
      // Inline form: let browser scroll to #apply.
      if (href === '#apply' && document.getElementById('apply')) {
        return null;
      }
    }
    return getModal();
  }

  function isFormTrigger(el) {
    if (!el || el.nodeType !== 1) {
      return false;
    }
    if (el.hasAttribute('data-jcp-form-trigger') || el.hasAttribute('data-quote-trigger')) {
      return true;
    }
    var href = el.getAttribute('href') || '';
    if (href === '#jcp-form-modal') {
      return true;
    }
    if (href === '#apply' && getModal('jcp-form-modal')) {
      return true;
    }
    return false;
  }

  document.addEventListener('click', function (event) {
    var closeEl = event.target.closest('[data-jcp-form-close]');
    if (closeEl) {
      event.preventDefault();
      closeModal(closeEl.closest('.jcp-fluent-quote-modal'));
      return;
    }

    var trigger = event.target.closest('a, button');
    if (!trigger || !isFormTrigger(trigger)) {
      return;
    }
    var modal = resolveModalFromTrigger(trigger);
    if (!modal) {
      return;
    }
    event.preventDefault();
    openModal(modal);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key !== 'Escape') {
      return;
    }
    var open = document.querySelector('.jcp-fluent-quote-modal.is-open');
    if (open) {
      closeModal(open);
    }
  });

  window.jcpFluentQuoteOpen = function (id) {
    openModal(getModal(id || 'jcp-form-modal'));
  };
  window.jcpFluentQuoteClose = function (id) {
    closeModal(getModal(id || 'jcp-form-modal'));
  };
})();
