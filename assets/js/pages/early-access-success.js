/**
 * Early Access Success page: record demo → early access conversion when
 * the user reached this page with a demo_session in the URL (same session as demo).
 * One conversion per session; server prevents double counting.
 */
(function() {
  var config = window.JCP_DEMO_CONVERSION;
  if (!config || !config.rest_url) return;

  var params = new URLSearchParams(window.location.search);
  var sessionId = params.get('demo_session');
  if (!sessionId || sessionId.length > 64) return;

  fetch(config.rest_url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      session_id: sessionId,
      event_type: 'demo_converted'
    })
  }).catch(function() {});
})();
