const { chromium } = require('playwright');
const path = require('path');
const OUT = __dirname;
const URL =
  'http://127.0.0.1:8765/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&creative_concept=default&fbclid=F1';

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('wrote', name);
}

async function fit(page, label) {
  const m = await page.evaluate(() => {
    const shell = document.querySelector('.pg-shell');
    const chrome = document.querySelector('.pg-chrome');
    const bar = document.getElementById('pgBottomAction');
    const active = document.querySelector('.pg-state:not([hidden])');
    const stage = document.getElementById('pgStage');
    const ch = window.innerHeight;
    const barRect = bar && !bar.hidden ? bar.getBoundingClientRect() : null;
    const stageScroll = stage ? stage.scrollHeight - stage.clientHeight > 2 : false;
    const barBottomGap = barRect ? Math.abs(ch - barRect.bottom) : null;
    const topGap = chrome ? chrome.getBoundingClientRect().top : null;
    const panel = document.getElementById('pgDestPanel');
    const panelH = panel ? Math.round(panel.getBoundingClientRect().height) : null;
    return {
      ch,
      chromeH: chrome ? Math.round(chrome.getBoundingClientRect().height) : 0,
      chromeTop: topGap != null ? Math.round(topGap) : null,
      barVisible: !!(bar && !bar.hidden),
      barBottomGap,
      stageScroll,
      state: active && active.getAttribute('data-pg-state'),
      shellH: shell ? Math.round(shell.getBoundingClientRect().height) : 0,
      overflowY: stageScroll || (shell ? shell.scrollHeight > ch + 2 : false),
      panelH,
      hasWelcomeProduct: !!document.querySelector('.pg-welcome-product'),
      hasDarkFlow: !!document.querySelector('.pg-welcome-visual__flow'),
      destCycleTimers: false,
    };
  });
  console.log('FIT', label, JSON.stringify(m));
  return m;
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}

async function waitBottom(page, labelPart) {
  await page.waitForSelector('#pgBottomAction:not([hidden]) .pg-btn', { timeout: 5000 });
  if (labelPart) {
    await page.waitForFunction(
      (t) => {
        const b = document.querySelector('#pgBottomAction:not([hidden]) .pg-btn');
        return b && b.textContent.includes(t);
      },
      labelPart,
      { timeout: 5000 }
    );
  }
  await page.waitForTimeout(220);
}

async function clickBottom(page) {
  await page.locator('#pgBottomAction:not([hidden]) .pg-btn').click({ force: true });
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const results = {};

  async function runViewport(w, h, prefix, shots) {
    const ctx = await browser.newContext({
      viewport: { width: w, height: h },
      deviceScaleFactor: w <= 430 ? 2 : 1,
      isMobile: w <= 430,
      hasTouch: w <= 430,
    });
    const p = await ctx.newPage();
    await p.goto(URL, { waitUntil: 'networkidle' });
    await p.evaluate(() => {
      localStorage.clear();
      sessionStorage.clear();
    });
    await p.reload({ waitUntil: 'networkidle' });
    await p.waitForSelector('#pgWelcomeCta, #pgBottomAction .pg-btn');

    if (shots) await shot(p, `${prefix}-01-welcome.png`);
    results[`${prefix}-welcome`] = await fit(p, `${prefix}-welcome`);
    if (results[`${prefix}-welcome`].hasDarkFlow) {
      console.error('FAIL dark welcome flow still present');
    }

    await clickBottom(p);
    await p.waitForSelector('[data-pg-choices="trade"] .pg-choice');
    if (shots) await shot(p, `${prefix}-02-trade.png`);
    results[`${prefix}-trade`] = await fit(p, `${prefix}-trade`);

    await clickChoice(p, 'HVAC');
    await p.waitForSelector('[data-pg-choices="current_workflow"] .pg-choice');
    if (shots) await shot(p, `${prefix}-03-workflow-choices.png`);
    results[`${prefix}-workflow-choices`] = await fit(p, `${prefix}-workflow-choices`);

    await clickChoice(p, 'CompanyCam');
    await waitBottom(p, 'Continue');
    if (shots) await shot(p, `${prefix}-04-workflow-insight.png`);
    results[`${prefix}-workflow-insight`] = await fit(p, `${prefix}-workflow-insight`);
    await clickBottom(p);

    await p.waitForSelector('[data-pg-choices="jobs_per_week"] .pg-choice');
    if (shots) await shot(p, `${prefix}-05-jobs-choices.png`);
    await clickChoice(p, '6–10');
    await waitBottom(p, 'Continue');
    if (shots) await shot(p, `${prefix}-06-jobs-insight.png`);
    results[`${prefix}-jobs-insight`] = await fit(p, `${prefix}-jobs-insight`);
    await clickBottom(p);

    await p.waitForSelector('[data-pg-choices="public_proof_percentage"] .pg-choice');
    if (shots) await shot(p, `${prefix}-07-proof-choices.png`);
    await clickChoice(p, 'A few');
    await waitBottom(p, 'Proof Gap');
    if (shots) await shot(p, `${prefix}-08-proof-insight.png`);
    results[`${prefix}-proof-insight`] = await fit(p, `${prefix}-proof-insight`);
    await clickBottom(p);

    await waitBottom(p, 'One Job');
    if (shots) await shot(p, `${prefix}-09-result.png`);
    results[`${prefix}-result`] = await fit(p, `${prefix}-result`);
    await clickBottom(p);

    await p.waitForSelector('#pgEmail');
    await waitBottom(p, 'Transformation');
    if (shots) await shot(p, `${prefix}-10-email.png`);
    results[`${prefix}-email`] = await fit(p, `${prefix}-email`);

    await p.fill('#pgEmail', 'qa@gmail.com');
    await clickBottom(p);
    await waitBottom(p, 'Trial Plan');
    await p.waitForTimeout(280);
    if (shots) await shot(p, `${prefix}-11-reveal-website.png`);
    results[`${prefix}-reveal-website`] = await fit(p, `${prefix}-reveal-website`);

    const dests = ['google', 'social', 'reviews', 'directory'];
    for (const d of dests) {
      await p.locator(`.pg-dest-tab[data-dest="${d}"]`).click();
      await p.waitForTimeout(220);
      if (shots) await shot(p, `${prefix}-11-reveal-${d}.png`);
      results[`${prefix}-reveal-${d}`] = await fit(p, `${prefix}-reveal-${d}`);
    }

    // Confirm no auto-cycle: stay on directory for 1.2s and verify still directory
    await p.waitForTimeout(1200);
    const stillDir = await p.evaluate(() => {
      const t = document.querySelector('.pg-dest-tab.is-active');
      return t && t.getAttribute('data-dest') === 'directory';
    });
    console.log('NO_AUTOCYCLE', prefix, stillDir);
    if (!stillDir) results[`${prefix}-autocycle`] = { overflowY: true, fail: 'autocycle' };

    await clickBottom(p);
    await waitBottom(p, '14-Day');
    if (shots) await shot(p, `${prefix}-12-trial.png`);
    results[`${prefix}-trial`] = await fit(p, `${prefix}-trial`);

    await ctx.close();
  }

  await runViewport(390, 844, 'p3-390', true);
  await runViewport(375, 812, 'p3-375', true);
  await runViewport(430, 932, 'p3-430', true);

  const fails = Object.entries(results).filter(([, v]) => {
    if (v.fail) return true;
    if (v.hasDarkFlow) return true;
    if (v.overflowY) return true;
    if (v.chromeTop != null && v.chromeTop > 8) return true;
    if (v.barVisible && v.barBottomGap != null && v.barBottomGap > 24) return true;
    if (v.panelH != null && v.panelH > 420) return true;
    return false;
  });
  console.log(
    'SUMMARY_FAILS',
    fails.map(([k, v]) => `${k}:${JSON.stringify(v)}`).join(' | ') || 'none'
  );
  await browser.close();
  if (fails.length) process.exit(2);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
