const { chromium } = require('playwright');
const path = require('path');
const OUT = __dirname;
const URL = 'http://127.0.0.1:8765/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&creative_concept=default&fbclid=F1';

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('wrote', name);
}

async function fit(page, label) {
  const m = await page.evaluate(() => {
    const active = document.querySelector('.pg-state:not([hidden])');
    const inner = active && active.querySelector('.pg-state__inner');
    const ch = window.innerHeight;
    const rect = inner ? inner.getBoundingClientRect() : null;
    const chrome = document.querySelector('.pg-chrome');
    const chromeH = chrome ? chrome.getBoundingClientRect().height : 0;
    const used = rect ? Math.ceil(rect.bottom) : 0;
    const overflowY = used > ch + 2;
    const choicesHidden = !!(active && active.querySelector('.pg-choices[hidden]'));
    const summaryVisible = !!(active && active.querySelector('.pg-answer-summary:not([hidden])'));
    return {
      used,
      ch,
      chromeH,
      overflowY,
      choicesHidden,
      summaryVisible,
      state: active && active.getAttribute('data-pg-state'),
    };
  });
  console.log('FIT', label, JSON.stringify(m));
  return m;
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}

async function waitInsight(page) {
  await page.waitForSelector('.proof-gap-insight-card.is-visible', { timeout: 5000 });
  await page.waitForTimeout(280);
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
    await p.waitForSelector('#pgWelcomeCta');
    if (shots) await shot(p, `${prefix}-welcome.png`);
    results[`${prefix}-welcome`] = await fit(p, `${prefix}-welcome`);

    await p.click('#pgWelcomeCta');
    await p.waitForSelector('[data-pg-choices="trade"] .pg-choice');
    if (shots) await shot(p, `${prefix}-trade.png`);
    results[`${prefix}-trade`] = await fit(p, `${prefix}-trade`);

    await clickChoice(p, 'Electrical');
    await p.waitForSelector('[data-pg-choices="current_workflow"] .pg-choice');
    if (shots) await shot(p, `${prefix}-workflow-before.png`);
    results[`${prefix}-workflow-before`] = await fit(p, `${prefix}-workflow-before`);

    await clickChoice(p, 'CompanyCam');
    await waitInsight(p);
    if (shots) await shot(p, `${prefix}-workflow-insight.png`);
    results[`${prefix}-workflow-insight`] = await fit(p, `${prefix}-workflow-insight`);
    await p.click('.proof-gap-insight-card__cta', { force: true });

    await clickChoice(p, '6–10');
    await waitInsight(p);
    if (shots) await shot(p, `${prefix}-jobs-insight.png`);
    results[`${prefix}-jobs-insight`] = await fit(p, `${prefix}-jobs-insight`);
    await p.click('.proof-gap-insight-card__cta', { force: true });

    if (shots) await shot(p, `${prefix}-proof-before.png`);
    results[`${prefix}-proof-before`] = await fit(p, `${prefix}-proof-before`);
    await clickChoice(p, 'A few');
    await waitInsight(p);
    if (shots) await shot(p, `${prefix}-proof-insight.png`);
    results[`${prefix}-proof-insight`] = await fit(p, `${prefix}-proof-insight`);
    await p.click('.proof-gap-insight-card__cta', { force: true });

    await p.waitForSelector('#pgResultCta');
    if (shots) await shot(p, `${prefix}-result.png`);
    results[`${prefix}-result`] = await fit(p, `${prefix}-result`);

    await p.click('#pgResultCta');
    await p.waitForSelector('#pgEmail');
    if (shots) await shot(p, `${prefix}-email.png`);
    results[`${prefix}-email`] = await fit(p, `${prefix}-email`);

    await p.fill('#pgEmail', 'qa@gmail.com');
    await p.click('#pgEmailSubmit');
    await p.waitForSelector('#pgRevealContinue');
    await p.waitForTimeout(200);
    if (shots) await shot(p, `${prefix}-product-website.png`);
    results[`${prefix}-product`] = await fit(p, `${prefix}-product`);

    const media = await p.evaluate(() => ({
      photoHidden: document.getElementById('pgJobPhoto')?.hidden,
      neutralHidden: document.getElementById('pgJobNeutral')?.hidden,
      title: document.getElementById('pgJobTitle')?.textContent,
      src: document.getElementById('pgJobPhoto')?.getAttribute('src') || '',
    }));
    console.log('TRADE_MEDIA', JSON.stringify(media));

    await p.click('.pg-dest-tab[data-dest="directory"]');
    await p.waitForTimeout(150);
    if (shots) await shot(p, `${prefix}-product-directory.png`);

    await p.click('#pgRevealContinue');
    await p.waitForSelector('#pgTrialCta');
    if (shots) await shot(p, `${prefix}-trial.png`);
    results[`${prefix}-trial`] = await fit(p, `${prefix}-trial`);

    await ctx.close();
  }

  await runViewport(390, 844, 'final-390', true);
  await runViewport(375, 812, 'final-375', true);
  await runViewport(430, 932, 'final-430', false);

  for (const [w, h, name] of [
    [768, 1024, '768'],
    [1440, 900, '1440'],
  ]) {
    const ctx = await browser.newContext({ viewport: { width: w, height: h } });
    const p = await ctx.newPage();
    await p.goto(URL, { waitUntil: 'networkidle' });
    await p.evaluate(() => localStorage.clear());
    await p.reload({ waitUntil: 'networkidle' });
    results[`${name}-welcome`] = await fit(p, `${name}-welcome`);
    await ctx.close();
  }

  const overflows = Object.entries(results)
    .filter(([, v]) => v.overflowY)
    .map(([k, v]) => `${k}(+${v.used - v.ch})`);
  console.log('SUMMARY_OVERFLOW', overflows.join(',') || 'none');
  await browser.close();
  if (overflows.length) process.exit(2);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
