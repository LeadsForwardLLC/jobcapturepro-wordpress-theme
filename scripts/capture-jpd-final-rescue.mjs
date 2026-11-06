/**
 * Final-rescue funnel screenshot pass.
 * Usage: node scripts/capture-jpd-final-rescue.mjs
 */
import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(__dirname, '../.superpowers/sdd/screenshots/final-rescue');
const BASE = process.env.JPD_BASE || 'http://jobcapturepro.local:10010';

fs.mkdirSync(OUT, { recursive: true });

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('saved', name);
}

async function sectionShot(page, selector, name) {
  const el = page.locator(selector).first();
  await el.waitFor({ state: 'visible', timeout: 15000 });
  await el.scrollIntoViewIfNeeded();
  await page.waitForTimeout(300);
  await el.screenshot({ path: path.join(OUT, name) });
  console.log('saved', name);
}

async function seedOptIn(page) {
  await page.evaluate(() => {
    sessionStorage.setItem('jcp_jpd_opted_in', '1');
    sessionStorage.setItem(
      'jcp_jpd_demo_state',
      JSON.stringify({ optedIn: true, niche: 'hvac', nicheLabel: 'HVAC' })
    );
    localStorage.setItem(
      'demoUser',
      JSON.stringify({
        email: 'qa@example.com',
        niche: 'hvac',
        nicheLabel: 'HVAC',
        source: 'job_proof_demo',
        firstName: 'Qa',
        businessName: '',
        goals: [],
      })
    );
    sessionStorage.removeItem('jcp_jpd_demo_done');
  });
}

async function runDesktop(page) {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/job-proof-demo/?jpd_debug=1`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(600);

  await shot(page, '01-desktop-hero.png');
  await sectionShot(page, '#jpd-authority, .jcp-authority--scoreboard', '02-desktop-authority.png');
  await sectionShot(page, '#integrations', '03-desktop-integrations.png');
  await sectionShot(page, '#differentiator', '04-desktop-problem-solution.png');
  await sectionShot(page, '#ai-transform', '05-desktop-one-job-outputs.png');
  await sectionShot(page, '#jpd-optin', '06-desktop-optin.png');
  await sectionShot(page, '#why-jcp', '07-desktop-founder.png');
  await sectionShot(page, '#testimonials, .jcp-block-testimonials', '08-desktop-testimonials.png');
  await sectionShot(page, '.jcp-niche-final, .jpd-final-cta', '09-desktop-final-cta.png');

  await seedOptIn(page);
  await page.goto(`${BASE}/job-proof-demo/demo/?jpd_debug=1`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(900);
  await shot(page, '10-desktop-results-top.png');

  // Wait for results reveal
  await page.waitForSelector('#jpdFullResults:not([hidden])', { timeout: 12000 });
  await page.waitForTimeout(400);
  await sectionShot(page, '.jpd-outputs', '11-desktop-outputs-grid.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="website"]', '11b-desktop-website-plugin.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="directory"]', '11c-desktop-directory.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="review"]', '11d-desktop-review.png');
  await sectionShot(page, '#jpdTrialBridge, .jpd-trial-bridge', '12-desktop-results-final-cta.png');

  // Confirm no second form
  const secondForm = await page.locator('#jpdOptinForm, #jpdRunGate:not([hidden]) form').count();
  console.log('second_form_count', secondForm);

  // Organic /demo/ untouched smoke
  await page.goto(`${BASE}/demo/`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(500);
  await shot(page, '13-desktop-organic-demo.png');
}

async function runMobile(page) {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  await shot(page, '14-390-hero.png');
  await sectionShot(page, '#jpd-optin', '15-390-optin.png');
  await sectionShot(page, '#ai-transform', '16-390-outputs.png');

  await seedOptIn(page);
  await page.goto(`${BASE}/job-proof-demo/demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(900);
  await shot(page, '17-390-results-top.png');
  await page.waitForSelector('#jpdFullResults:not([hidden])', { timeout: 12000 });
  await page.waitForTimeout(400);
  await sectionShot(page, '.jpd-outputs', '18-390-outputs-grid.png');
  await sectionShot(page, '#jpdTrialBridge, .jpd-trial-bridge', '19-390-final-cta.png');
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
try {
  await runDesktop(page);
  await runMobile(page);
  console.log('done', OUT);
} finally {
  await browser.close();
}
