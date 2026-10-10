/**
 * $1M LP screenshot pass — /job-proof-demo/ only.
 */
import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(__dirname, '../.superpowers/sdd/screenshots/million-lp');
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
  await page.waitForTimeout(350);
  await el.screenshot({ path: path.join(OUT, name) });
  console.log('saved', name);
}

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage();
try {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(800);
  await page.screenshot({ path: path.join(OUT, '01-desktop-full.png'), fullPage: true });
  console.log('saved 01-desktop-full.png');
  await shot(page, '02-desktop-hero.png');
  await sectionShot(page, '.jpd-cred-strip', '03-desktop-credibility.png');
  await sectionShot(page, '#workflow', '04-desktop-workflow.png');
  await sectionShot(page, '#ai-transform', '05-desktop-transform.png');
  await sectionShot(page, '#jpd-optin', '06-desktop-convert-form.png');
  await sectionShot(page, '#why-jcp', '07-desktop-trust.png');
  await sectionShot(page, '.jpd-final-cta', '08-desktop-final-cta.png');

  // Confirm no second form on demo route still works (smoke)
  await page.evaluate(() => {
    sessionStorage.setItem('jcp_jpd_opted_in', '1');
    localStorage.setItem(
      'demoUser',
      JSON.stringify({ email: 'qa@example.com', niche: 'hvac', nicheLabel: 'HVAC', source: 'job_proof_demo' })
    );
  });
  await page.goto(`${BASE}/job-proof-demo/demo/`, { waitUntil: 'domcontentloaded' });
  const forms = await page.locator('#jpdOptinForm').count();
  console.log('demo_route_form_count', forms);

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  await page.screenshot({ path: path.join(OUT, '09-390-full.png'), fullPage: true });
  console.log('saved 09-390-full.png');
  await shot(page, '10-390-hero.png');
  await sectionShot(page, '#jpd-optin', '11-390-form.png');
  await sectionShot(page, '#why-jcp', '12-390-testimonials.png');
  await sectionShot(page, '.jpd-final-cta', '13-390-final-cta.png');

  console.log('done', OUT);
} finally {
  await browser.close();
}
