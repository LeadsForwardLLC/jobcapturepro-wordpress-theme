const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const http = require('http');
const { spawn } = require('child_process');

const THEME = path.resolve(__dirname, '../../../..');
const OUT = path.join(__dirname, 'polish');
const PORT = 8766;

function startServer() {
  return new Promise((resolve, reject) => {
    const child = spawn('python3', ['-m', 'http.server', String(PORT)], {
      cwd: THEME,
      stdio: ['ignore', 'pipe', 'pipe'],
    });
    const t = setTimeout(() => reject(new Error('server start timeout')), 5000);
    child.stdout.on('data', () => {});
    child.stderr.on('data', () => {});
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

async function shot(page, name) {
  await page.waitForTimeout(220);
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('wrote', name);
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}

async function clickBottom(page) {
  await page.locator('#pgBottomAction:not([hidden]) .pg-btn, #pgBottomAction:not([hidden]) button').first().click({ force: true });
}

async function waitBottom(page, part) {
  await page.waitForSelector('#pgBottomAction:not([hidden])', { timeout: 8000 });
  if (part) {
    await page.waitForFunction(
      (t) => {
        const b = document.querySelector('#pgBottomAction:not([hidden])');
        return !!(b && b.textContent && b.textContent.includes(t));
      },
      part,
      { timeout: 8000 }
    );
  }
  await page.waitForTimeout(160);
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const server = await startServer();
  const browser = await chromium.launch({ headless: true });
  const viewports = [
    [375, 812, 'm375'],
    [390, 844, 'm390'],
    [430, 932, 'm430'],
  ];

  for (const [w, h, prefix] of viewports) {
    const ctx = await browser.newContext({ viewport: { width: w, height: h } });
    const page = await ctx.newPage();
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

    const url = `http://127.0.0.1:${PORT}/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&fbclid=QA1`;
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.waitForSelector('[data-pg-state="welcome"]:not([hidden])');
    await shot(page, `${prefix}-01-welcome.png`);

    await clickBottom(page);
    await page.waitForSelector('[data-pg-state="trade"]:not([hidden])');
    await shot(page, `${prefix}-02-trade.png`);

    await clickChoice(page, 'Electrical');
    await page.waitForSelector('[data-pg-state="current_workflow"]:not([hidden])');
    await shot(page, `${prefix}-03-workflow.png`);

    await clickChoice(page, 'Housecall Pro');
    await waitBottom(page, 'Continue');
    await shot(page, `${prefix}-04-workflow-insight.png`);
    await clickBottom(page);

    await page.waitForSelector('[data-pg-state="jobs_per_week"]:not([hidden])');
    await shot(page, `${prefix}-05-jobs.png`);
    await clickChoice(page, '11–20');
    await waitBottom(page, 'Continue');
    await shot(page, `${prefix}-06-jobs-insight.png`);
    await clickBottom(page);

    await page.waitForSelector('[data-pg-state="public_proof_percentage"]:not([hidden])');
    await shot(page, `${prefix}-07-proof.png`);
    await clickChoice(page, 'Nearly every job');
    await waitBottom(page, 'See My Proof Gap');
    await shot(page, `${prefix}-08-proof-insight.png`);
    await clickBottom(page);

    await page.waitForSelector('[data-pg-state="proof_gap_result"]:not([hidden])');
    await shot(page, `${prefix}-09-result.png`);
    await clickBottom(page);

    await page.waitForSelector('[data-pg-state="email_capture"]:not([hidden])');
    await shot(page, `${prefix}-10-email.png`);
    await page.fill('#pgEmail', `qa+${prefix}@example.com`);
    await clickBottom(page);

    await page.waitForSelector('[data-pg-state="product_reveal"]:not([hidden])');
    await page.waitForTimeout(500);
    await shot(page, `${prefix}-11-reveal-website.png`);

    await page.click('.pg-dest-tab[data-dest="google"]');
    await page.waitForTimeout(250);
    await shot(page, `${prefix}-12-reveal-google.png`);

    await page.click('.pg-dest-tab[data-dest="social"]');
    await page.waitForTimeout(250);
    await shot(page, `${prefix}-13-reveal-social.png`);

    await page.click('.pg-dest-tab[data-dest="reviews"]');
    await page.waitForTimeout(250);
    await shot(page, `${prefix}-14-reveal-reviews.png`);

    await page.click('.pg-dest-tab[data-dest="directory"]');
    await page.waitForTimeout(250);
    await shot(page, `${prefix}-15-reveal-directory.png`);

    // Handoff check
    await clickBottom(page);
    await page.waitForSelector('[data-pg-state="trial_bridge"]:not([hidden])');
    await shot(page, `${prefix}-16-trial.png`);
    const href = await page.getAttribute('#pgTrialCta', 'href');
    const demoUser = await page.evaluate(() => localStorage.getItem('demoUser'));
    console.log('HANDOFF', prefix, { href, demoUser });

    await ctx.close();
  }

  // Desktop sample
  {
    const ctx = await browser.newContext({ viewport: { width: 1366, height: 768 } });
    const page = await ctx.newPage();
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
    await page.goto(`http://127.0.0.1:${PORT}/.superpowers/sdd/screenshots/proof-gap/qa-harness.html`, {
      waitUntil: 'networkidle',
    });
    await shot(page, 'd1366-01-welcome.png');
    await clickBottom(page);
    await clickChoice(page, 'Electrical');
    await page.waitForSelector('[data-pg-state="current_workflow"]:not([hidden])');
    await clickChoice(page, 'Housecall Pro');
    await waitBottom(page, 'Continue');
    await clickBottom(page);
    await clickChoice(page, '11–20');
    await waitBottom(page, 'Continue');
    await clickBottom(page);
    await clickChoice(page, 'Nearly every job');
    await waitBottom(page, 'See My Proof Gap');
    await clickBottom(page);
    await shot(page, 'd1366-09-result.png');
    await clickBottom(page);
    await page.fill('#pgEmail', 'qa+desktop@example.com');
    await clickBottom(page);
    await page.waitForSelector('[data-pg-state="product_reveal"]:not([hidden])');
    await page.waitForTimeout(600);
    await shot(page, 'd1366-11-reveal.png');
    await page.click('.pg-dest-tab[data-dest="reviews"]');
    await page.waitForTimeout(250);
    await shot(page, 'd1366-14-reviews.png');
    await clickBottom(page);
    await shot(page, 'd1366-16-trial.png');
    await ctx.close();
  }

  await browser.close();
  server.kill('SIGTERM');
  console.log('DONE');
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
