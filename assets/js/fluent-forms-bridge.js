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

  function hrefOpensFormModal(href) {
    if (!href) {
      return false;
    }
    if (href === '#apply' || href === '#jcp-form-modal') {
      return true;
    }
    try {
      var url = new URL(href, window.location.href);
      if (url.origin !== window.location.origin) {
        return false;
      }
      return url.hash === '#apply' || url.hash === '#jcp-form-modal';
    } catch (e) {
      return /#apply$/.test(href) || /#jcp-form-modal$/.test(href);
    }
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
    if (hrefOpensFormModal(href)) {
      var modal = getModal('jcp-form-modal');
      if (modal) {
        return modal;
      }
      // Inline form: let browser scroll to #apply.
      if ((href === '#apply' || /#apply$/.test(href)) && document.getElementById('apply')) {
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
    if (hrefOpensFormModal(href) && getModal('jcp-form-modal')) {
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

    // Never intercept anything inside a Fluent Form (next/prev/submit/Choices).
    if (event.target.closest('form.frm-fluent-form, .fluentform, .ff-el-group, .choices')) {
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
      || trigger.closest('.step-nav, .ff_step_nav_last, .ff_submit_btn_wrapper, .ff-inner_submit_container')
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
   * Multi-step Fluent keeps failed fields on earlier steps (display:none).
   * Fluent’s own “is in viewport” check treats 0×0 hidden rects as visible, so
   * Submit can look dead on desktop. Reveal the error step, then scroll.
   */
  function getStepIndex(step) {
    if (!step || !step.parentElement) {
      return -1;
    }
    var siblings = step.parentElement.querySelectorAll(
      ':scope > .fluentform-step, :scope > .ff-step, :scope > .ff-el-form-step'
    );
    if (!siblings.length) {
      siblings = step.parentElement.querySelectorAll('.fluentform-step, .ff-step, .ff-el-form-step');
    }
    return Array.prototype.indexOf.call(siblings, step);
  }

  function isActiveStep(step) {
    if (!step) {
      return false;
    }
    return (
      step.classList.contains('active')
      || step.classList.contains('ff-active')
      || step.classList.contains('ff_active')
    );
  }

  function revealErrorStep(error, formEl) {
    var step = error.closest('.fluentform-step, .ff-step, .ff-el-form-step');
    if (!step || isActiveStep(step) || !window.jQuery) {
      return Promise.resolve(error);
    }
    var index = getStepIndex(step);
    if (index < 0) {
      return Promise.resolve(error);
    }
    var form = formEl || step.closest('form.frm-fluent-form') || document.querySelector('form.frm-fluent-form');
    if (!form) {
      return Promise.resolve(error);
    }
    window.jQuery(form).trigger('update_slider', {
      goBackToStep: index,
      animDuration: 350,
      isScrollTop: true,
      actionType: 'prev'
    });
    return new Promise(function (resolve) {
      window.setTimeout(function () {
        var activeError = form.querySelector(
          '.fluentform-step.active .ff-el-is-error, .ff-step.active .ff-el-is-error, .ff-el-is-error'
        );
        resolve(activeError || error);
      }, 420);
    });
  }

  function stickyOffset() {
    var top = document.querySelector('.jcp-form-landing__top');
    var h = top ? top.getBoundingClientRect().height : 0;
    return Math.max(72, Math.round(h) + 16);
  }

  /** Keep Fluent multi-step auto-scroll clear of the Form Landing sticky bar. */
  function syncFluentScrollOffset() {
    window.ff_scroll_top_offset = stickyOffset();
  }

  function showValidationNotice(form, message) {
    if (!form) {
      return;
    }
    var host = form.closest('.jcp-fluent-bridge, .fluentform, .fluentform_wrapper_7') || form.parentElement;
    if (!host) {
      return;
    }
    var notice = host.querySelector('.jcp-ff-validation-notice');
    if (!notice) {
      notice = document.createElement('div');
      notice.className = 'jcp-ff-validation-notice';
      notice.setAttribute('role', 'alert');
      notice.setAttribute('aria-live', 'assertive');
      host.insertBefore(notice, host.firstChild);
    }
    notice.textContent = message || 'Please complete the highlighted required fields, then try again.';
    notice.classList.add('is-visible');
    window.clearTimeout(notice._jcpHide);
    notice._jcpHide = window.setTimeout(function () {
      notice.classList.remove('is-visible');
    }, 8000);
  }

  function scrollToFirstError(scope) {
    var root = scope && scope.querySelector ? scope : document;
    var form = null;
    if (scope && scope.nodeType === 1) {
      form = scope.matches && scope.matches('form') ? scope : scope.closest && scope.closest('form');
      if (!form && scope.querySelector) {
        form = scope.querySelector('form.frm-fluent-form');
      }
    }
    if (!form) {
      form = document.querySelector('form.frm-fluent-form');
    }
    var error = root.querySelector('.ff-el-is-error')
      || root.querySelector('.ff-errors-in-stack .error')
      || root.querySelector('.text-danger');
    if (!error) {
      showValidationNotice(form);
      return;
    }
    revealErrorStep(error, form).then(function (target) {
      showValidationNotice(form);
      if (!target || typeof target.scrollIntoView !== 'function') {
        return;
      }
      // Ignore 0×0 rects (still-hidden nodes).
      var rect = target.getBoundingClientRect();
      if (rect.width === 0 && rect.height === 0) {
        return;
      }
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
      // Re-adjust for sticky form-landing header covering the field.
      window.setTimeout(function () {
        var y = window.scrollY || window.pageYOffset || 0;
        window.scrollTo({ top: Math.max(0, y - stickyOffset()), behavior: 'smooth' });
      }, 50);
    });
  }

  function bindFluentHelpers() {
    if (!window.jQuery) {
      return;
    }
    var $ = window.jQuery;
    function onFail(e, payload) {
      var el = null;
      if (payload && payload.form && payload.form.length) {
        el = payload.form[0];
      } else if (payload && payload.length) {
        el = payload[0];
      } else if (e && e.target) {
        el = e.target;
      }
      // Fluent fires validation_failed before it paints .ff-el-is-error — wait a tick.
      window.setTimeout(function () {
        scrollToFirstError(el || document);
      }, 60);
    }
    $(document)
      .off('fluentform_submission_failed.jcpBridge fluentform_submitted_failed.jcpBridge fluentform_validation_failed.jcpBridge')
      .on(
        'fluentform_submission_failed.jcpBridge fluentform_submitted_failed.jcpBridge fluentform_validation_failed.jcpBridge',
        onFail
      );
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      syncFluentScrollOffset();
      bindFluentHelpers();
    });
  } else {
    syncFluentScrollOffset();
    bindFluentHelpers();
  }
  window.setTimeout(bindFluentHelpers, 500);
  window.addEventListener('resize', syncFluentScrollOffset);

  function openModalFromHash() {
    if (window.location.hash !== '#apply' && window.location.hash !== '#jcp-form-modal') {
      return;
    }
    var modal = getModal('jcp-form-modal');
    if (modal) {
      openModal(modal);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', openModalFromHash);
  } else {
    openModalFromHash();
  }
  window.addEventListener('hashchange', openModalFromHash);

  window.jcpFluentQuoteOpen = function (id) {
    openModal(getModal(id || 'jcp-form-modal'));
  };
  window.jcpFluentQuoteClose = function (id) {
    closeModal(getModal(id || 'jcp-form-modal'));
  };
})();
