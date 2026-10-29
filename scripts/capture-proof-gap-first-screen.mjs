/**
 * Capture before (production) and after (local preview) first-screen screenshots.
 *
 * Usage: node scripts/capture-proof-gap-first-screen.mjs
 */
import { chromium, devices } from 'playwright';
import { mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { createServer } from 'node:http';
import { readFileSync, existsSync } from 'node:fs';
import { extname } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const themeRoot = join(__dirname, '..');
const outRoot = join(themeRoot, 'docs/qa/proof-gap-first-screen-2026-10-29');
const beforeDir = join(outRoot, 'before');
const afterDir = join(outRoot, 'after');

mkdirSync(beforeDir, { recursive: true });
mkdirSync(afterDir, { recursive: true });

const mime = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.png': 'image/png',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml',
};

function startPreviewServer() {
  const server = createServer((req, res) => {
    const urlPath = decodeURIComponent((req.url || '/').split('?')[0]);
    const rel = urlPath === '/' ? '/docs/qa/proof-gap-first-screen-2026-10-29/preview.html' : urlPath;
    const filePath = join(themeRoot, rel.replace(/^\//, ''));
    if (!filePath.startsWith(themeRoot) || !existsSync(filePath)) {
      res.writeHead(404);
      res.end('Not found');
      return;
    }
    const body = readFileSync(filePath);
    res.writeHead(200, { 'Content-Type': mime[extname(filePath)] || 'application/octet-stream' });
    res.end(body);
  });
  return new Promise((resolve) => {
    server.listen(0, '127.0.0.1', () => {
      const { port } = server.address();
      resolve({ server, base: `http://127.0.0.1:${port}` });
    });
  });
}

async function shot(page, path) {
  await page.waitForTimeout(400);
  await page.screenshot({ path, fullPage: false });
}

async function capturePair(browser, label, url, outDir) {
  const desktop = await browser.newPage({ viewport: { width: 1280, height: 900 } });
  await desktop.goto(url, { waitUntil: 'networkidle' });
  await shot(desktop, join(outDir, `${label}-desktop.png`));
  await desktop.close();

  const mobile = await browser.newPage({ ...devices['iPhone 13'] });
  await mobile.goto(url, { waitUntil: 'networkidle' });
  await shot(mobile, join(outDir, `${label}-mobile.png`));
  await mobile.close();
}

const { server, base } = await startPreviewServer();
const browser = await chromium.launch({ headless: true });

try {
  await capturePair(
    browser,
    'control',
    'https://jobcapturepro.com/proof-gap/?pg_variant=control',
    beforeDir
  );
  await capturePair(
    browser,
    'direct_question',
    'https://jobcapturepro.com/proof-gap/?pg_variant=direct_question',
    beforeDir
  );

  await capturePair(
    browser,
    'control',
    `${base}/docs/qa/proof-gap-first-screen-2026-10-29/preview.html?pg_variant=control`,
    afterDir
  );
  await capturePair(
    browser,
    'direct_question',
    `${base}/docs/qa/proof-gap-first-screen-2026-10-29/preview.html?pg_variant=direct_question`,
    afterDir
  );

  console.log('Wrote screenshots to', outRoot);
} finally {
  await browser.close();
  server.close();
}
