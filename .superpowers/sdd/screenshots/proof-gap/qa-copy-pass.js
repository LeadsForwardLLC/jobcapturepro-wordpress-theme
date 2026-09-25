/**
 * Capture every Proof Gap funnel step on mobile + desktop.
 * Prefers live site (accurate CSS/fonts); falls back to local harness.
 *
 * Run:
 *   node qa-copy-pass.js
 *   MOBILE_ONLY=1 node qa-copy-pass.js
 *   PROOF_GAP_URL=https://jobcapturepro.com/proof-gap/ node qa-copy-pass.js
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn } = require('child_process');

const THEME = path.resolve(__dirname, '../../../..');
const OUT = path.join(__dirname, 'copy-pass');
const OUT_VISIBLE = path.join(THEME, 'proof-gap-screenshots');
const PORT = 8771;
const LIVE_URL = process.env.PROOF_GAP_URL || 'https://jobcapturepro.com/proof-gap/';

const STEPS = [
  '01-welcome',
  '02-trade',
  '03-workflow',
  '04-workflow-insight',
  '05-jobs',
  '06-jobs-insight',
  '07-visibility',
  '08-visibility-insight',
  '09-result',
  '10-email',
  '11-app-sim-start',
  '12-app-sim-mid',
  '13-app-sim-done',
  '14-reveal-website',
  '15-reveal-google',
  '16-reveal-social',
  '17-reveal-reviews',
  '18-reveal-directory',
  '19-plan-build-start',
  '20-plan-build-mid',
  '21-plan-build-done',
  '22-trial',
];

const MOBILE_ONLY = process.env.MOBILE_ONLY === '1' || process.env.MOBILE_ONLY === 'true';
const ALL_VIEWPORTS = [
  { width: 390, height: 844, prefix: 'mobile', label: 'Mobile 390×844', dpr: 2, mobile: true },
  { width: 1440, height: 900, prefix: 'desktop', label: 'Desktop 1440×900', dpr: 1, mobile: false },
];
const VIEWPORTS = MOBILE_ONLY ? ALL_VIEWPORTS.filter((v) => v.mobile) : ALL_VIEWPORTS;

function startServer() {
  return new Promise((resolve, reject) => {
    const child = spawn('python3', ['-m', 'http.server', String(PORT), '--bind', '127.0.0.1'], {
      cwd: THEME,
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    const t = setTimeout(() => reject(new Error('server start timeout')), 8000);
    const check = () => {
      http
        .get(`http://127.0.0.1:${PORT}/.superpowers/sdd/screenshots/proof-gap/qa-harness.html`, (res) => {
          clearTimeout(t);
          resolve(child);
        })
        .on('error', () => setTimeout(check, 150));
    };
    setTimeout(check, 200);
  });
}

async function shot(page, name, fullPage) {
  const file = path.join(OUT, name);
  await page.waitForTimeout(120);
  await page.screenshot({ path: file, fullPage: !!fullPage });
  console.log('wrote', name);
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}

async function clickBottom(page) {
  await page.locator('#pgBottomAction:not([hidden]) .pg-btn, #pgBottomAction:not([hidden]) button').first().click({
    force: true,
  });
}

async function waitBottom(page, part) {
  await page.waitForSelector('#pgBottomAction:not([hidden])', { timeout: 12000 });
  if (part) {
    await page.waitForFunction(
      (t) => {
        const b = document.querySelector('#pgBottomAction:not([hidden])');
        return !!(b && b.textContent && b.textContent.includes(t));
      },
      part,
      { timeout: 12000 }
    );
  }
  await page.waitForTimeout(140);
}

async function waitState(page, id) {
  await page.waitForSelector(`[data-pg-state="${id}"]:not([hidden])`, { timeout: 20000 });
}

async function prepPage(page) {
  await page.addInitScript(() => {
    try {
      localStorage.removeItem('jcp_proof_gap_state_v1');
      localStorage.removeItem('jcp_pg_lead_event_id');
    } catch (e) {}
    window.JCPOnboardingHandoff = {
      decorateHref(href, extra) {
        const u = new URL(href, location.origin);
        Object.keys(extra || {}).forEach((k) => {
          if (extra[k]) u.searchParams.set(k, String(extra[k]));
        });
        return u.toString();
      },
    };
    window.JCP_ONBOARDING = { url: 'https://app.jobcapturepro.com/onboarding?sessionId=qa&step=1' };
  });
  await page.route('**/wp-json/**', async (route) => {
    const url = route.request().url();
    if (url.includes('proof-gap-survey-submit')) {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({ success: true, captured: true, handoff_token: 'qa-token' }),
      });
      return;
    }
    await route.fulfill({ status: 200, contentType: 'application/json', body: JSON.stringify({ ok: true }) });
  });
}

async function runFunnel(page, prefix, startUrl) {
  const long = new Set([
    '09-result',
    '10-email',
    '14-reveal-website',
    '15-reveal-google',
    '16-reveal-social',
    '17-reveal-reviews',
    '18-reveal-directory',
    '22-trial',
  ]);
  const take = (step) => shot(page, `${prefix}-${step}.png`, long.has(step));

  await page.goto(startUrl, { waitUntil: 'networkidle' });
  await waitState(page, 'welcome');
  await take('01-welcome');

  await clickBottom(page);
  await waitState(page, 'trade');
  await take('02-trade');

  await clickChoice(page, 'HVAC');
  await waitState(page, 'current_workflow');
  await take('03-workflow');

  await clickChoice(page, 'Housecall Pro');
  await waitBottom(page, 'Continue');
  await take('04-workflow-insight');
  await clickBottom(page);

  await waitState(page, 'jobs_per_week');
  await take('05-jobs');
  await clickChoice(page, '11–20');
  await waitBottom(page, 'Continue');
  await take('06-jobs-insight');
  await clickBottom(page);

  await waitState(page, 'public_proof_percentage');
  await take('07-visibility');
  await clickChoice(page, 'About half');
  await waitBottom(page, 'Show Me What');
  await take('08-visibility-insight');
  await clickBottom(page);

  await waitState(page, 'proof_gap_result');
  await take('09-result');
  await clickBottom(page);

  await waitState(page, 'email_capture');
  await take('10-email');
  await page.fill('#pgEmail', `copy-pass+${prefix}@example.com`);
  await clickBottom(page);

  // App sim — capture start / mid / near-complete before auto-advance
  await waitState(page, 'app_sim');
  await take('11-app-sim-start');
  await page.waitForTimeout(1800);
  await take('12-app-sim-mid');
  await page.waitForFunction(
    () => {
      const ticks = document.querySelectorAll('#pgAppSimTicks li.is-done, #pgAppSimTicks li.is-on, #pgAppSimTicks .is-done');
      return ticks.length >= 4 || document.body.classList.contains('pg-is-app-sim') === false;
    },
    { timeout: 8000 }
  ).catch(() => {});
  await take('13-app-sim-done');

  await waitState(page, 'product_reveal');
  await page.waitForTimeout(450);
  await take('14-reveal-website');

  for (const [dest, step] of [
    ['google', '15-reveal-google'],
    ['social', '16-reveal-social'],
    ['reviews', '17-reveal-reviews'],
    ['directory', '18-reveal-directory'],
  ]) {
    await page.click(`.pg-dest-tab[data-dest="${dest}"]`);
    await page.waitForTimeout(280);
    await take(step);
  }

  await clickBottom(page);

  await waitState(page, 'plan_build');
  await take('19-plan-build-start');
  await page.waitForTimeout(1400);
  await take('20-plan-build-mid');
  await page.waitForFunction(
    () => {
      const done = document.querySelectorAll('#pgPlanBuildSteps .is-done, #pgPlanBuildSteps .pg-plan-build__step.is-done');
      return done.length >= 3 || !document.body.classList.contains('pg-is-plan-build');
    },
    { timeout: 6000 }
  ).catch(() => {});
  await take('21-plan-build-done');

  await waitState(page, 'trial_bridge');
  await take('22-trial');
}

function writeIndex() {
  const mobileOnly = VIEWPORTS.every((v) => v.mobile);
  const title = mobileOnly ? 'Proof Gap — Mobile Funnel Screenshots' : 'Proof Gap — Full Funnel Screenshots';
  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>${title}</title>
  <style>
    :root { color-scheme: light; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif; }
    body { margin: 0; background: #f4f6f8; color: #0f172a; }
    header { position: sticky; top: 0; z-index: 2; background: #0f172a; color: #fff; padding: 1rem 1.25rem; }
    header p { margin: 0.35rem 0 0; opacity: 0.75; font-size: 0.9rem; }
    main { max-width: ${mobileOnly ? '460px' : '1180px'}; margin: 0 auto; padding: 1.25rem; }
    .step { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
    .step h2 { margin: 0 0 0.75rem; font-size: 1.05rem; text-transform: capitalize; }
    .pair { display: grid; grid-template-columns: ${mobileOnly ? '1fr' : '1fr 1.6fr'}; gap: 0.85rem; align-items: start; }
    figure { margin: 0; }
    figcaption { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #64748b; margin-bottom: 0.35rem; }
    img { width: 100%; height: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; display: block; }
    nav { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0.75rem 0 0; }
    nav a { color: #93c5fd; font-size: 0.82rem; }
  </style>
</head>
<body>
  <header>
    <h1>${title}</h1>
    <p>Path: HVAC · Housecall Pro · 11–20 jobs/week · About half · Includes app_sim + plan_build</p>
    <p>Open from theme root: <code>proof-gap-screenshots/index.html</code></p>
    <nav>
      ${STEPS.map((s, i) => `<a href="#s${i + 1}">${i + 1}</a>`).join('')}
    </nav>
  </header>
  <main>
    ${STEPS.map((step, i) => {
      const label = step.replace(/^\d+-/, '').replace(/-/g, ' ');
      const figures = VIEWPORTS.map(
        (vp) => `
        <figure>
          <figcaption>${vp.label}</figcaption>
          <a href="${vp.prefix}-${step}.png" target="_blank"><img src="${vp.prefix}-${step}.png" alt="${vp.prefix} ${label}" loading="lazy" /></a>
        </figure>`
      ).join('');
      return `
    <section class="step" id="s${i + 1}">
      <h2>${String(i + 1).padStart(2, '0')} · ${label}</h2>
      <div class="pair">${figures}
      </div>
    </section>`;
    }).join('\n')}
  </main>
</body>
</html>`;

  const viewportLines = VIEWPORTS.map((v) => `- \`${v.prefix}-*.png\` — ${v.width}×${v.height}`).join('\n');
  const readme = `# ${title}

**Easy find:** \`proof-gap-screenshots/\` at the theme root → open \`index.html\`.

## Captured path

1. Welcome
2. Trade (HVAC)
3. Workflow choices
4. Workflow insight (Housecall Pro)
5. Jobs/week choices
6. Jobs insight
7. Visibility question
8. Visibility insight (About half)
9. Result
10. Email
11–13. App sim (start / mid / done)
14–18. Product reveal tabs (Website → Directory)
19–21. Plan build (start / mid / done)
22. Trial / final plan

## Viewports

${viewportLines}

## Re-run

\`\`\`bash
cd .superpowers/sdd/screenshots/proof-gap
${mobileOnly ? 'MOBILE_ONLY=1 node qa-copy-pass.js' : 'node qa-copy-pass.js'}
\`\`\`
`;
  fs.writeFileSync(path.join(OUT, 'index.html'), html);
  fs.writeFileSync(path.join(OUT, 'README.md'), readme);
  console.log('wrote index.html + README.md');
}

(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  fs.mkdirSync(OUT, { recursive: true });

  // Live site blocks headless browsers (403). Always use local theme harness.
  let server = await startServer();
  const startUrl = `http://127.0.0.1:${PORT}/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&fbclid=COPYPASS&_=${Date.now()}`;
  console.log('Using local harness', startUrl);

  const browser = await chromium.launch({ headless: true });

  try {
    for (const vp of VIEWPORTS) {
      const ctx = await browser.newContext({
        viewport: { width: vp.width, height: vp.height },
        deviceScaleFactor: vp.dpr,
        isMobile: vp.mobile,
        hasTouch: vp.mobile,
      });
      const page = await ctx.newPage();
      await prepPage(page);
      console.log('---', vp.label);
      await runFunnel(page, vp.prefix, startUrl);
      await ctx.close();
    }
    writeIndex();
    fs.rmSync(OUT_VISIBLE, { recursive: true, force: true });
    fs.cpSync(OUT, OUT_VISIBLE, { recursive: true });
    console.log('DONE', OUT);
    console.log('VISIBLE', OUT_VISIBLE);
  } finally {
    await browser.close();
    if (server) server.kill('SIGTERM');
  }
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
