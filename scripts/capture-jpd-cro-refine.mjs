/**
 * CRO refine screenshot pass.
 * Usage: node scripts/capture-jpd-cro-refine.mjs
 */
import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(__dirname, '../.superpowers/sdd/screenshots/cro-refine');
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
  await page.waitForTimeout(250);
  await el.screenshot({ path: path.join(OUT, name) });
  console.log('saved', name);
}

async function seedOptIn(page, niche = 'hvac', label = 'HVAC') {
  await page.evaluate(
    ({ niche, label }) => {
      sessionStorage.setItem('jcp_jpd_opted_in', '1');
      sessionStorage.setItem(
        'jcp_jpd_demo_state',
        JSON.stringify({ optedIn: true, niche, nicheLabel: label })
      );
      localStorage.setItem(
        'demoUser',
        JSON.stringify({
          email: 'qa@example.com',
          niche,
          nicheLabel: label,
          source: 'job_proof_demo',
          firstName: 'Qa',
          businessName: '',
          goals: [],
        })
      );
      sessionStorage.removeItem('jcp_jpd_demo_done');
    },
    { niche, label }
  );
}

async function runDesktop(page) {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/job-proof-demo/?jpd_debug=1`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(700);
  await shot(page, '01-desktop-hero.png');
  await sectionShot(page, '#jpd-authority, .jpd-cred-strip', '02-desktop-credibility.png');
  await sectionShot(page, '#workflow', '03-desktop-workflow.png');
  await sectionShot(page, '#ai-transform', '04-desktop-transform.png');
  await sectionShot(page, '#jpd-optin', '05-desktop-form.png');
  await sectionShot(page, '#why-jcp, .jpd-trust', '06-desktop-founder-reviews.png');
  await sectionShot(page, '.jpd-final-cta, .jcp-niche-final', '07-desktop-final-cta.png');
  await page.screenshot({ path: path.join(OUT, '08-mobile-full-placeholder.png'), fullPage: true });

  await seedOptIn(page, 'hvac', 'HVAC');
  await page.goto(`${BASE}/job-proof-demo/demo/?niche=hvac&jpd_debug=1`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(600);
  await shot(page, '09-desktop-demo-opening.png');

  // Verify HVAC photo is not water heater asset
  const photoSrc = await page.locator('[data-jpd-job-photo]').first().getAttribute('src');
  const title = await page.locator('[data-jpd-job-title]').first().textContent();
  const heading = await page.locator('[data-jpd-full-heading]').textContent();
  console.log('hvac_check', { photoSrc, title, heading });
  if (!photoSrc || /job-proof/i.test(photoSrc)) {
    throw new Error('HVAC demo still using water-heater photo: ' + photoSrc);
  }
  if (!/HVAC/i.test(heading || '')) {
    throw new Error('Heading not cleaned to HVAC: ' + heading);
  }

  await page.waitForTimeout(1200);
  await shot(page, '10-desktop-demo-transform.png');
  await page.waitForSelector('#jpdFullResults:not([hidden])', { timeout: 10000 });
  await page.waitForTimeout(400);
  await sectionShot(page, '.jpd-output--website, .jpd-output[data-jpd-output="website"]', '11-desktop-website.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="google"]', '12-desktop-google.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="social"]', '13-desktop-social.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="directory"]', '14-desktop-directory.png');
  await sectionShot(page, '.jpd-output[data-jpd-output="review"]', '15-desktop-review.png');
  await sectionShot(page, '#jpdTrialBridge', '16-desktop-trial-cta.png');

  // Plumbing photo check
  await seedOptIn(page, 'plumbing', 'Plumbing');
  await page.goto(`${BASE}/job-proof-demo/demo/?niche=plumbing`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  const plumbingSrc = await page.locator('[data-jpd-job-photo]').first().getAttribute('src');
  console.log('plumbing_check', plumbingSrc);
  if (!plumbingSrc || !/job-proof/i.test(plumbingSrc)) {
    throw new Error('Plumbing demo missing water-heater photo: ' + plumbingSrc);
  }

  // No second form
  const forms = await page.locator('#jpdOptinForm').count();
  console.log('second_form_count', forms);

  await page.goto(`${BASE}/demo/`, { waitUntil: 'domcontentloaded' });
  await page.waitForTimeout(400);
  await shot(page, '17-desktop-organic-demo.png');
}

async function runMobile(page) {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  await page.screenshot({ path: path.join(OUT, '18-390-full-lp.png'), fullPage: true });
  await shot(page, '19-390-hero.png');

  await seedOptIn(page, 'hvac', 'HVAC');
  await page.goto(`${BASE}/job-proof-demo/demo/?niche=hvac`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  await shot(page, '20-390-demo-opening.png');
  await page.waitForSelector('#jpdFullResults:not([hidden])', { timeout: 10000 });
  await page.waitForTimeout(300);
  await sectionShot(page, '.jpd-outputs', '21-390-results.png');
  await sectionShot(page, '#jpdTrialBridge', '22-390-trial-cta.png');
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
