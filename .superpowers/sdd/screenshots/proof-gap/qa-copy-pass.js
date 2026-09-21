/**
 * Capture every Proof Gap funnel step on mobile + desktop.
 * Output: copy-pass/ (PNG + index.html)
 *
 * Run from theme root or this folder:
 *   node qa-copy-pass.js
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn } = require('child_process');

const THEME = path.resolve(__dirname, '../../../..');
const OUT = path.join(__dirname, 'copy-pass');
const PORT = 8771;

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
  '11-reveal-website',
  '12-reveal-google',
  '13-reveal-social',
  '14-reveal-reviews',
  '15-reveal-directory',
  '16-trial',
];

const VIEWPORTS = [
  { width: 390, height: 844, prefix: 'mobile', label: 'Mobile 390×844' },
  { width: 1440, height: 900, prefix: 'desktop', label: 'Desktop 1440×900' },
];

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
  await page.waitForTimeout(180);
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
  await page.waitForSelector('#pgBottomAction:not([hidden])', { timeout: 10000 });
  if (part) {
    await page.waitForFunction(
      (t) => {
        const b = document.querySelector('#pgBottomAction:not([hidden])');
        return !!(b && b.textContent && b.textContent.includes(t));
      },
      part,
      { timeout: 10000 }
    );
  }
  await page.waitForTimeout(160);
}

async function prepPage(page) {
  await page.addInitScript(() => {
    window.JCPOnboardingHandoff = {
      decorateHref(href, extra) {
        const u = new URL(href, location.origin);
        Object.keys(extra || {}).forEach((k) => {
          if (extra[k]) u.searchParams.set(k, String(extra[k]));
        });
        try {
          const raw = localStorage.getItem('demoUser');
          const user = raw ? JSON.parse(raw) : null;
          if (user && user.email) u.searchParams.set('email', user.email);
        } catch (e) {}
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

async function runFunnel(page, prefix) {
  const long = new Set(['09-result', '10-email', '11-reveal-website', '12-reveal-google', '13-reveal-social', '14-reveal-reviews', '15-reveal-directory', '16-trial']);
  const take = (step) => shot(page, `${prefix}-${step}.png`, long.has(step));

  const url = `http://127.0.0.1:${PORT}/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&fbclid=COPYPASS`;
  await page.goto(url, { waitUntil: 'networkidle' });
  await page.waitForSelector('[data-pg-state="welcome"]:not([hidden])');
  await take('01-welcome');

  await clickBottom(page);
  await page.waitForSelector('[data-pg-state="trade"]:not([hidden])');
  await take('02-trade');

  await clickChoice(page, 'Electrical');
  await page.waitForSelector('[data-pg-state="current_workflow"]:not([hidden])');
  await take('03-workflow');

  await clickChoice(page, 'Housecall Pro');
  await waitBottom(page, 'Continue');
  await take('04-workflow-insight');
  await clickBottom(page);

  await page.waitForSelector('[data-pg-state="jobs_per_week"]:not([hidden])');
  await take('05-jobs');
  await clickChoice(page, '11–20');
  await waitBottom(page, 'Continue');
  await take('06-jobs-insight');
  await clickBottom(page);

  await page.waitForSelector('[data-pg-state="public_proof_percentage"]:not([hidden])');
  await take('07-visibility');
  await clickChoice(page, 'About half');
  await waitBottom(page, 'See What Customers See');
  await take('08-visibility-insight');
  await clickBottom(page);

  await page.waitForSelector('[data-pg-state="proof_gap_result"]:not([hidden])');
  await take('09-result');
  await clickBottom(page);

  await page.waitForSelector('[data-pg-state="email_capture"]:not([hidden])');
  await take('10-email');
  await page.fill('#pgEmail', `copy-pass+${prefix}@example.com`);
  await clickBottom(page);

  await page.waitForSelector('[data-pg-state="product_reveal"]:not([hidden])');
  await page.waitForTimeout(500);
  await take('11-reveal-website');

  for (const [dest, step] of [
    ['google', '12-reveal-google'],
    ['social', '13-reveal-social'],
    ['reviews', '14-reveal-reviews'],
    ['directory', '15-reveal-directory'],
  ]) {
    await page.click(`.pg-dest-tab[data-dest="${dest}"]`);
    await page.waitForTimeout(280);
    await take(step);
  }

  await clickBottom(page);
  await page.waitForSelector('[data-pg-state="trial_bridge"]:not([hidden])');
  await take('16-trial');
}

function writeIndex() {
  const rows = STEPS.map((step, i) => {
    const label = step.replace(/^\d+-/, '').replace(/-/g, ' ');
    return `
    <section class="step">
      <h2>${i + 1}. ${label}</h2>
      <div class="pair">
        <figure>
          <figcaption>Mobile</figcaption>
          <a href="mobile-${step}.png" target="_blank"><img src="mobile-${step}.png" alt="Mobile ${label}" loading="lazy" /></a>
        </figure>
        <figure>
          <figcaption>Desktop</figcaption>
          <a href="desktop-${step}.png" target="_blank"><img src="desktop-${step}.png" alt="Desktop ${label}" loading="lazy" /></a>
        </figure>
      </div>
    </section>`;
  }).join('\n');

  const html = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Proof Gap — Copy Pass Screenshots</title>
  <style>
    :root { color-scheme: light; font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif; }
    body { margin: 0; background: #f4f6f8; color: #0f172a; }
    header { position: sticky; top: 0; z-index: 2; background: #0f172a; color: #fff; padding: 1rem 1.25rem; }
    header p { margin: 0.35rem 0 0; opacity: 0.75; font-size: 0.9rem; }
    main { max-width: 1180px; margin: 0 auto; padding: 1.25rem; }
    .step { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
    .step h2 { margin: 0 0 0.75rem; font-size: 1.05rem; text-transform: capitalize; }
    .pair { display: grid; grid-template-columns: 1fr 1.6fr; gap: 0.85rem; align-items: start; }
    figure { margin: 0; }
    figcaption { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; color: #64748b; margin-bottom: 0.35rem; }
    img { width: 100%; height: auto; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff; display: block; }
    nav { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0.75rem 0 0; }
    nav a { color: #93c5fd; font-size: 0.82rem; }
    @media (max-width: 900px) { .pair { grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <header>
    <h1>Proof Gap — Copy Pass Screenshots</h1>
    <p>Path: Electrical · Housecall Pro · 11–20 jobs/week · About half reused · Full funnel</p>
    <p>Location: <code>.superpowers/sdd/screenshots/proof-gap/copy-pass/</code></p>
    <nav>
      ${STEPS.map((s, i) => `<a href="#s${i + 1}">${i + 1}</a>`).join('')}
    </nav>
  </header>
  <main>
    ${STEPS.map((step, i) => {
      const label = step.replace(/^\d+-/, '').replace(/-/g, ' ');
      return `
    <section class="step" id="s${i + 1}">
      <h2>${String(i + 1).padStart(2, '0')} · ${label}</h2>
      <div class="pair">
        <figure>
          <figcaption>Mobile 390×844</figcaption>
          <a href="mobile-${step}.png" target="_blank"><img src="mobile-${step}.png" alt="Mobile ${label}" loading="lazy" /></a>
        </figure>
        <figure>
          <figcaption>Desktop 1440×900</figcaption>
          <a href="desktop-${step}.png" target="_blank"><img src="desktop-${step}.png" alt="Desktop ${label}" loading="lazy" /></a>
        </figure>
      </div>
    </section>`;
    }).join('\n')}
  </main>
</body>
</html>`;

  fs.writeFileSync(path.join(OUT, 'index.html'), html);
  fs.writeFileSync(
    path.join(OUT, 'README.md'),
    `# Proof Gap — Copy Pass Screenshots

Easy find path in the theme:

\`\`\`
.superpowers/sdd/screenshots/proof-gap/copy-pass/
\`\`\`

Open \`index.html\` in a browser to flip through every step (mobile + desktop side by side).

## Captured path

1. Welcome
2. Trade (Electrical)
3. Workflow choices
4. Workflow insight (Housecall Pro)
5. Jobs/week choices
6. Jobs insight
7. Visibility question
8. Visibility insight (About half)
9. Result
10. Email
11–15. Product reveal (Website → Google → Social → Reviews → Directory)
16. Trial / final plan

## Viewports

- \`mobile-*.png\` — 390×844
- \`desktop-*.png\` — 1440×900

## Re-run

\`\`\`bash
cd .superpowers/sdd/screenshots/proof-gap
node qa-copy-pass.js
\`\`\`
`
  );
  console.log('wrote index.html + README.md');
}

(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  fs.mkdirSync(OUT, { recursive: true });

  const server = await startServer();
  const browser = await chromium.launch({ headless: true });

  try {
    for (const vp of VIEWPORTS) {
      const ctx = await browser.newContext({
        viewport: { width: vp.width, height: vp.height },
        deviceScaleFactor: vp.prefix === 'mobile' ? 2 : 1,
        isMobile: vp.prefix === 'mobile',
        hasTouch: vp.prefix === 'mobile',
      });
      const page = await ctx.newPage();
      await prepPage(page);
      console.log('---', vp.label);
      await runFunnel(page, vp.prefix);
      await ctx.close();
    }
    writeIndex();
    console.log('DONE', OUT);
  } finally {
    await browser.close();
    server.kill('SIGTERM');
  }
})().catch((err) => {
  console.error(err);
  process.exit(1);
});
