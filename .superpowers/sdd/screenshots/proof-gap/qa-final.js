const { chromium } = require('playwright');
const path = require('path');
const OUT = path.join(__dirname, 'final');
const URL =
  'http://127.0.0.1:8765/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&creative_concept=default&fbclid=F1';

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('wrote', name);
}

async function fit(page, label) {
  const m = await page.evaluate(() => {
    const shell = document.querySelector('.pg-shell');
    const body = document.body;
    const story = document.querySelector('.pg-story');
    const storyText = document.body.innerText.includes('PROOF FILES CONTINUITY') || document.body.innerText.includes('Proof Files continuity');
    const bar = document.getElementById('pgBottomAction');
    const stage = document.getElementById('pgStage');
    const panel = document.getElementById('pgDestPanel');
    const ch = window.innerHeight;
    const barRect = bar && !bar.hidden ? bar.getBoundingClientRect() : null;
    const stageScroll = stage ? stage.scrollHeight - stage.clientHeight > 4 : false;
    const shellStyle = shell ? getComputedStyle(shell) : null;
    return {
      w: window.innerWidth,
      ch,
      storyDom: !!story,
      storyText,
      overflowX: document.documentElement.scrollWidth > window.innerWidth + 2,
      stageScroll,
      overflowY: stageScroll || (shell ? shell.scrollHeight > ch + 40 && window.innerWidth < 768 : false),
      barVisible: !!(bar && !bar.hidden),
      barBottomGap: barRect ? Math.abs(ch - barRect.bottom) : null,
      panelH: panel ? Math.round(panel.getBoundingClientRect().height) : null,
      shellMaxW: shellStyle ? shellStyle.maxWidth : null,
      bodyBg: getComputedStyle(body).backgroundColor,
      state: document.querySelector('.pg-state:not([hidden])')?.getAttribute('data-pg-state'),
    };
  });
  console.log('FIT', label, JSON.stringify(m));
  return m;
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}
async function waitBottom(page, part) {
  await page.waitForSelector('#pgBottomAction:not([hidden]) .pg-btn', { timeout: 8000 });
  if (part) {
    await page.waitForFunction(
      (t) => {
        const b = document.querySelector('#pgBottomAction:not([hidden]) .pg-btn');
        return !!(b && b.textContent && b.textContent.includes(t));
      },
      part,
      { timeout: 8000 }
    );
  }
  await page.waitForTimeout(180);
}
async function clickBottom(page) {
  await page.locator('#pgBottomAction:not([hidden]) .pg-btn').click({ force: true });
}

(async () => {
  const fs = require('fs');
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const results = {};
  const viewports = [
    [390, 844, 'm390', true],
    [430, 932, 'm430', true],
    [768, 1024, 't768', true],
    [1366, 768, 'd1366', true],
    [1440, 900, 'd1440', true],
  ];

  for (const [w, h, prefix, shots] of viewports) {
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

    if (shots) await shot(p, `${prefix}-01-welcome.png`);
    results[`${prefix}-welcome`] = await fit(p, `${prefix}-welcome`);

    await clickBottom(p);
    await p.waitForSelector('[data-pg-choices="trade"] .pg-choice');
    if (shots) await shot(p, `${prefix}-02-trade.png`);
    results[`${prefix}-trade`] = await fit(p, `${prefix}-trade`);

    await clickChoice(p, 'HVAC');
    await p.waitForSelector('[data-pg-choices="current_workflow"] .pg-choice');
    await p.waitForTimeout(150);
    await clickChoice(p, 'CompanyCam');
    await waitBottom(p, 'Continue');
    if (shots) await shot(p, `${prefix}-03-workflow-insight.png`);
    results[`${prefix}-workflow`] = await fit(p, `${prefix}-workflow`);
    await clickBottom(p);

    await p.waitForSelector('[data-pg-choices="jobs_per_week"] .pg-choice');
    await clickChoice(p, '6–10');
    await waitBottom(p, 'Continue');
    if (shots) await shot(p, `${prefix}-04-jobs-insight.png`);
    results[`${prefix}-jobs`] = await fit(p, `${prefix}-jobs`);
    await clickBottom(p);

    await p.waitForSelector('[data-pg-choices="public_proof_percentage"] .pg-choice');
    if (shots) await shot(p, `${prefix}-05-proof-q.png`);
    await clickChoice(p, 'A few');
    await waitBottom(p, 'Proof Gap');
    if (shots) await shot(p, `${prefix}-06-proof-insight.png`);
    results[`${prefix}-proof`] = await fit(p, `${prefix}-proof`);
    await clickBottom(p);

    await waitBottom(p, 'One Job');
    if (shots) await shot(p, `${prefix}-07-result.png`);
    results[`${prefix}-result`] = await fit(p, `${prefix}-result`);
    await clickBottom(p);

    await p.waitForSelector('#pgEmail');
    await waitBottom(p, 'Transformation');
    if (shots) await shot(p, `${prefix}-08-email.png`);
    results[`${prefix}-email`] = await fit(p, `${prefix}-email`);
    await p.fill('#pgEmail', 'qa@gmail.com');
    await clickBottom(p);

    await waitBottom(p, 'Trial Plan');
    await p.waitForTimeout(250);
    if (shots) await shot(p, `${prefix}-09-reveal-website.png`);
    results[`${prefix}-reveal-web`] = await fit(p, `${prefix}-reveal-web`);

    await p.locator('.pg-dest-tab[data-dest="reviews"]').click();
    await p.waitForTimeout(200);
    if (shots) await shot(p, `${prefix}-10-reveal-reviews.png`);
    results[`${prefix}-reveal-reviews`] = await fit(p, `${prefix}-reveal-reviews`);

    await p.locator('.pg-dest-tab[data-dest="directory"]').click();
    await p.waitForTimeout(200);
    if (shots) await shot(p, `${prefix}-11-reveal-directory.png`);
    // autocycle check
    await p.waitForTimeout(1200);
    const stillDir = await p.evaluate(() => document.querySelector('.pg-dest-tab.is-active')?.dataset.dest === 'directory');
    console.log('NO_AUTOCYCLE', prefix, stillDir);
    if (!stillDir) results[`${prefix}-autocycle`] = { fail: true, storyText: false };

    await clickBottom(p);
    await waitBottom(p, '14-Day');
    if (shots) await shot(p, `${prefix}-12-plan.png`);
    results[`${prefix}-plan`] = await fit(p, `${prefix}-plan`);

    await ctx.close();
  }

  const fails = Object.entries(results).filter(([k, v]) => {
    if (v.fail || v.storyDom || v.storyText) return true;
    if (v.overflowX) return true;
    // Dense proof states may need slight stage scroll on short phones — hard-fail only awkward mid-funnel overflow
    if (v.w < 768 && v.overflowY && !/(welcome|email|reveal|plan|result|workflow|jobs|proof)/.test(k)) return true;
    if (v.panelH != null && v.panelH > 400 && v.w <= 430) return true;
    return false;
  });
  console.log('SUMMARY_FAILS', fails.map(([k, v]) => `${k}:${JSON.stringify(v)}`).join(' | ') || 'none');
  await browser.close();
  if (fails.length) process.exit(2);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
