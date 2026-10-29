/**
 * Ultimate funnel screenshot pass.
 * Usage: node scripts/capture-jpd-ultimate.mjs
 */
import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';
import fs from 'fs';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const OUT = path.resolve(__dirname, '../.superpowers/sdd/screenshots/ultimate');
const BASE = process.env.JPD_BASE || 'http://jobcapturepro.local:10010';

fs.mkdirSync(OUT, { recursive: true });

async function shot(page, name) {
  await page.screenshot({ path: path.join(OUT, name), fullPage: false });
  console.log('saved', name);
}

async function sectionShot(page, selector, name) {
  const el = page.locator(selector).first();
  await el.scrollIntoViewIfNeeded();
  await page.waitForTimeout(250);
  await el.screenshot({ path: path.join(OUT, name) });
  console.log('saved', name);
}

async function runDesktop(page) {
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(500);
  await shot(page, '01-hero-initial.png');
  await page.screenshot({ path: path.join(OUT, '00-desktop-full.png'), fullPage: true });

  await page.locator('[data-jpd-use-sample]').click();
  await page.waitForTimeout(7200);
  await shot(page, '02-hero-transformation.png');

  await sectionShot(page, '#integrations', '03-integrations.png');
  await sectionShot(page, '#ai-transform', '04-ai-transform.png');
  await sectionShot(page, '#jpd-optin', '05-demo-optin.png');
  await sectionShot(page, '#jpd-directory, .jcp-directory-preview, [id*="directory"]', '08-directory.png');
  await sectionShot(page, '#local-signals', '09-local-signals.png');
  await sectionShot(page, '#why-jcp', '11-founder.png');
  await sectionShot(page, '#testimonials, .jcp-testimonials, [id*="testimonial"]', '10-reviews.png');
  await sectionShot(page, '.jcp-niche-final', '12-final-cta.png');

  // Exit intent
  await page.evaluate(() => {
    sessionStorage.removeItem('jcp_jpd_exit_shown');
    sessionStorage.removeItem('jcp_jpd_exit_dismissed');
  });
  await page.evaluate(() => {
    if (window.JCPJobProofDemo && window.JCPJobProofDemo.openExit) {
      // Force engagement flags via storage + time
    }
  });
  // Directly open via evaluating exit helpers if exposed
  const opened = await page.evaluate(() => {
    try {
      sessionStorage.removeItem('jcp_jpd_exit_shown');
      sessionStorage.removeItem('jcp_jpd_exit_dismissed');
      if (window.JCPJobProofDemo && typeof window.JCPJobProofDemo.openExit === 'function') {
        // Bypass engagement by setting startedAt far in past isn't possible; call openExit and if blocked, force DOM
        const ok = window.JCPJobProofDemo.openExit('qa');
        if (ok) return true;
      }
      const root = document.getElementById('jpdExitRoot');
      if (!root) return false;
      root.querySelectorAll('[data-jpd-exit-panel]').forEach((p) => {
        p.hidden = p.getAttribute('data-jpd-exit-panel') !== 'optin';
      });
      root.hidden = false;
      root.classList.add('is-open');
      root.setAttribute('aria-hidden', 'false');
      document.body.classList.add('jcp-case-exit-open');
      return true;
    } catch (e) {
      return false;
    }
  });
  if (opened) {
    await page.waitForTimeout(300);
    await shot(page, '13-exit-intent.png');
  }

  // Personalized demo via storage handoff
  await page.evaluate(() => {
    sessionStorage.setItem('jcp_jpd_opted_in', '1');
    localStorage.setItem(
      'demoUser',
      JSON.stringify({
        email: 'qa@example.com',
        niche: 'plumbing',
        nicheLabel: 'Plumbing',
        firstName: 'Qa',
        businessName: '',
        goals: [],
      })
    );
    sessionStorage.removeItem('jcp_jpd_demo_done');
  });
  await page.goto(`${BASE}/job-proof-demo/demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(800);
  await shot(page, '06-personalized-demo.png');
  await page.waitForTimeout(7500);
  await shot(page, '07-result-cards.png');
}

async function runMobile(page) {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${BASE}/job-proof-demo/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(400);
  await shot(page, '14-390-hero.png');
  await page.screenshot({ path: path.join(OUT, '00-mobile-full.png'), fullPage: true });
  await page.locator('[data-jpd-use-sample]').click();
  await page.waitForTimeout(7200);
  await shot(page, '15-390-hero-transform.png');
  await sectionShot(page, '#jpd-optin', '16-390-optin.png');
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
