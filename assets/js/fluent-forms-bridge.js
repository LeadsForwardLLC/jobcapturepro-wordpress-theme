/**
 * Fluent Forms bridge — modal open/close + light submit/error helpers.
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
    // Never intercept Fluent Forms next/prev/submit controls.
    if (
      trigger.classList.contains('ff-btn')
      || trigger.classList.contains('ff-btn-submit')
      || trigger.classList.contains('ff-btn-next')
      || trigger.classList.contains('ff-btn-prev')
      || trigger.closest('.step-nav, .ff_step_nav_last, .ff_submit_btn_wrapper')
    ) {
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

  /**
   * When Fluent validation fails, scroll the first visible error into view.
   * Multi-step forms can otherwise fail “silently” if the error sits in a collapsed step.
   */
  function scrollToFirstError(scope) {
    var root = scope && scope.querySelector ? scope : document;
    var error = root.querySelector(
      '.ff-el-is-error .text-danger, .ff-el-is-error, .ff-errors-in-stack .error, .text-danger'
    );
    if (!error || typeof error.scrollIntoView !== 'function') {
      return;
    }
    window.setTimeout(function () {
      error.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 50);
  }

  function bindFluentHelpers() {
    if (!window.jQuery) {
      return;
    }
    var $ = window.jQuery;
    $(document)
      .off('fluentform_submission_failed.jcpBridge')
      .on('fluentform_submission_failed.jcpBridge', function (e, form) {
        var el = (form && form.length) ? form[0] : (e && e.target);
        scrollToFirstError(el || document);
      });
    $(document)
      .off('fluentform_submitted_failed.jcpBridge fluentform_validation_failed.jcpBridge')
      .on('fluentform_submitted_failed.jcpBridge fluentform_validation_failed.jcpBridge', function (e) {
        scrollToFirstError((e && e.target) || document);
      });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindFluentHelpers);
  } else {
    bindFluentHelpers();
  }
  window.setTimeout(bindFluentHelpers, 500);

  window.jcpFluentQuoteOpen = function (id) {
    openModal(getModal(id || 'jcp-form-modal'));
  };
  window.jcpFluentQuoteClose = function (id) {
    closeModal(getModal(id || 'jcp-form-modal'));
  };
})();
