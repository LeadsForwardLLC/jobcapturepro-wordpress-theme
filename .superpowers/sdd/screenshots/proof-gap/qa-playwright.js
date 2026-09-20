const { chromium, devices } = require('playwright');
const path = require('path');
const fs = require('fs');

const OUT = path.join(__dirname);
const URL =
  'http://127.0.0.1:8765/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&utm_medium=paid_social&utm_campaign=proof_gap_qa&fbclid=PG_QA_FBCLID&ttclid=PG_QA_TTCLID';

async function shot(page, name) {
  const file = path.join(OUT, name);
  await page.screenshot({ path: file, fullPage: false });
  console.log('wrote', name);
}

async function clickChoice(page, label) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: label }).first().click();
  await page.waitForTimeout(320);
}

(async () => {
  const browser = await chromium.launch({ headless: true });

  // Mobile 390
  const mobile = await browser.newContext({
    ...devices['iPhone 12'],
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2,
  });
  const m = await mobile.newPage();
  await m.goto(URL, { waitUntil: 'networkidle' });
  await m.evaluate(() => {
    localStorage.removeItem('jcp_proof_gap_state_v1');
    sessionStorage.clear();
  });
  await m.reload({ waitUntil: 'networkidle' });
  await m.waitForSelector('.pg-choice');
  await shot(m, '01-390-trade.png');

  await clickChoice(m, 'HVAC');
  await clickChoice(m, 'Housecall Pro');
  await clickChoice(m, '6–10');
  await clickChoice(m, '11–25%');
  await m.waitForSelector('#pgResultCta');
  await shot(m, '02-390-result.png');

  await m.click('#pgResultCta');
  await m.waitForSelector('#pgEmail');
  await shot(m, '03-390-email.png');

  await m.fill('#pgEmail', 'bad');
  await m.click('#pgEmailSubmit');
  await m.waitForSelector('#pgEmailError:not([hidden])');
  await m.fill('#pgEmail', 'qa+proofgap@example.com');
  await m.click('#pgEmailSubmit');
  await m.waitForSelector('#pgRevealContinue');
  await shot(m, '04-390-product.png');

  await m.click('#pgRevealContinue');
  await m.waitForSelector('#pgTrialCta');
  await shot(m, '05-390-trial.png');

  // Resume check
  const href = await m.getAttribute('#pgTrialCta', 'href');
  console.log('trial_href_sample', href && href.slice(0, 180));
  const dl = await m.evaluate(() => (window.dataLayer || []).map((e) => e.event).filter(Boolean));
  console.log('datalayer_events', dl.join(','));
  const state = await m.evaluate(() => JSON.parse(localStorage.getItem('jcp_proof_gap_state_v1') || '{}').state);
  console.log('state_keys', state && Object.keys(state));
  console.log('email_in_state', state && ('email' in state));
  const pii = await m.evaluate(() => JSON.stringify(window.dataLayer || []).includes('@'));
  console.log('pii_in_dl', pii);

  await mobile.close();

  // Desktop 1280
  const desk = await browser.newContext({ viewport: { width: 1280, height: 900 }, deviceScaleFactor: 1 });
  const d = await desk.newPage();
  await d.goto(URL + '&desk=1', { waitUntil: 'networkidle' });
  await d.evaluate(() => {
    localStorage.removeItem('jcp_proof_gap_state_v1');
    sessionStorage.clear();
  });
  await d.reload({ waitUntil: 'networkidle' });
  await d.waitForSelector('.pg-choice');
  await shot(d, '06-desktop-trade.png');
  await clickChoice(d, 'Plumbing');
  await clickChoice(d, 'CompanyCam');
  await clickChoice(d, '11–20');
  await clickChoice(d, '0–10%');
  await d.waitForSelector('#pgResultCta');
  await shot(d, '07-desktop-result.png');

  // Extra widths smoke
  for (const w of [375, 430, 768, 1440]) {
    await d.setViewportSize({ width: w, height: w < 800 ? 812 : 900 });
    await d.waitForTimeout(100);
    const overflow = await d.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    console.log('overflow_' + w, overflow);
  }

  await desk.close();
  await browser.close();
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
