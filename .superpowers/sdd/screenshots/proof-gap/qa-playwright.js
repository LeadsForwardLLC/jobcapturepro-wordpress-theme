const { chromium, devices } = require('playwright');
const path = require('path');

const OUT = path.join(__dirname);
const URL = 'http://127.0.0.1:8765/.superpowers/sdd/screenshots/proof-gap/qa-harness.html?utm_source=facebook&utm_medium=paid_social&utm_campaign=proof_gap_p2&fbclid=PG2&ttclid=TT2';

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('wrote', name);
}

async function clickChoice(page, text) {
  await page.locator('.pg-state:not([hidden]) .pg-choice', { hasText: text }).first().click();
}

async function waitInsight(page) {
  await page.waitForSelector('.pg-state:not([hidden]) .proof-gap-insight-card.is-visible', { timeout: 5000 });
  await page.waitForTimeout(280);
}

async function continueInsight(page) {
  await page.locator('.pg-state:not([hidden]) .proof-gap-insight-card__cta').click();
  await page.waitForTimeout(200);
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  const mobile = await browser.newContext({
    ...devices['iPhone 12'],
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 2,
  });
  const m = await mobile.newPage();
  await m.goto(URL, { waitUntil: 'networkidle' });
  await m.evaluate(() => {
    localStorage.clear();
    sessionStorage.clear();
  });
  await m.reload({ waitUntil: 'networkidle' });
  await m.waitForSelector('#pgWelcomeCta');
  await shot(m, 'p2-390-00-welcome.png');

  await m.click('#pgWelcomeCta');
  await m.waitForSelector('[data-pg-choices="trade"] .pg-choice');
  await shot(m, 'p2-390-01-trade.png');

  await clickChoice(m, 'HVAC');
  await m.waitForSelector('[data-pg-choices="current_workflow"] .pg-choice');
  await clickChoice(m, 'Housecall Pro');
  await waitInsight(m);
  await shot(m, 'p2-390-02-workflow-insight.png');
  await continueInsight(m);

  await clickChoice(m, '6–10');
  await waitInsight(m);
  await shot(m, 'p2-390-03-jobs-insight.png');
  await continueInsight(m);

  await clickChoice(m, 'Almost none');
  await waitInsight(m);
  await shot(m, 'p2-390-04-proof-insight.png');
  await continueInsight(m);

  await m.waitForSelector('#pgResultCta');
  await shot(m, 'p2-390-05-result-low.png');

  // High-proof result path via local state jump
  await m.evaluate(() => {
    const raw = JSON.parse(localStorage.getItem('jcp_proof_gap_state_v1'));
    raw.state.public_proof_percentage = '76_100';
    raw.state.proof_gap_band = '76_100';
    raw.state.current_state = 'proof_gap_result';
    localStorage.setItem('jcp_proof_gap_state_v1', JSON.stringify(raw));
  });
  await m.reload({ waitUntil: 'networkidle' });
  await m.waitForSelector('#pgResultCta');
  await shot(m, 'p2-390-05b-result-high.png');

  await m.click('#pgResultCta');
  await m.waitForSelector('#pgEmail');
  await shot(m, 'p2-390-06-email.png');

  await m.fill('#pgEmail', 'qa@gmail.com');
  await m.click('#pgEmailSubmit');
  await m.waitForSelector('#pgRevealContinue');
  await shot(m, 'p2-390-07-product.png');

  await m.click('#pgRevealContinue');
  await m.waitForSelector('#pgTrialCta');
  await shot(m, 'p2-390-08-trial.png');

  const href = await m.getAttribute('#pgTrialCta', 'href');
  console.log('trial_href', href);
  const demoUser = await m.evaluate(() => localStorage.getItem('demoUser'));
  console.log('demoUser_has_email', !!(demoUser && JSON.parse(demoUser).email));
  const dl = await m.evaluate(() => (window.dataLayer || []).map((e) => e.event).filter(Boolean));
  console.log('events', dl.join(','));
  const pii = await m.evaluate(() => JSON.stringify(window.dataLayer || []).includes('@'));
  console.log('pii_in_dl', pii);

  await mobile.close();

  const desk = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const d = await desk.newPage();
  await d.goto(URL + '&desk=1', { waitUntil: 'networkidle' });
  await d.evaluate(() => {
    localStorage.clear();
    sessionStorage.clear();
  });
  await d.reload({ waitUntil: 'networkidle' });
  await d.waitForSelector('#pgWelcomeCta');
  await shot(d, 'p2-desktop-welcome.png');
  await d.click('#pgWelcomeCta');
  await d.waitForSelector('[data-pg-choices="trade"] .pg-choice');
  await clickChoice(d, 'Plumbing');
  await d.waitForSelector('[data-pg-choices="current_workflow"] .pg-choice');
  await clickChoice(d, 'CompanyCam');
  await waitInsight(d);
  await shot(d, 'p2-desktop-workflow-insight.png');
  await continueInsight(d);
  await clickChoice(d, '11–20');
  await waitInsight(d);
  await continueInsight(d);
  await clickChoice(d, 'A few');
  await waitInsight(d);
  await continueInsight(d);
  await d.waitForSelector('#pgResultCta');
  await shot(d, 'p2-desktop-result.png');
  await d.click('#pgResultCta');
  await d.fill('#pgEmail', 'owner@outlook.com');
  await d.click('#pgEmailSubmit');
  await d.waitForSelector('#pgRevealContinue');
  await shot(d, 'p2-desktop-product.png');

  for (const w of [375, 430, 768, 1440]) {
    await d.setViewportSize({ width: w, height: w < 800 ? 812 : 900 });
    await d.waitForTimeout(80);
    const overflow = await d.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
    console.log('overflow_' + w, overflow);
  }

  await desk.close();
  await browser.close();
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
