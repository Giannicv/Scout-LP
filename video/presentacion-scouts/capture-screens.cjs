const path = require('node:path');
const fs = require('node:fs');

const cache = path.join(process.env.LOCALAPPDATA, 'npm-cache', '_npx', '685f4bf2b09fe4e2', 'node_modules', 'puppeteer-core');
const puppeteer = require(cache);
const base = 'http://127.0.0.1:8000';
const out = path.join(__dirname, 'assets', 'screens');
fs.mkdirSync(out, { recursive: true });

(async () => {
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox'],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 1600, height: 900, deviceScaleFactor: 1 });
  async function shot(name, url) {
    const response = await page.goto(base + url, { waitUntil: 'networkidle2', timeout: 30000 });
    await page.screenshot({ path: path.join(out, name + '.png') });
    console.log(name, response.status(), page.url(), await page.title());
  }
  await page.goto(base + '/login', { waitUntil: 'networkidle2' });
  await page.locator('input[name=email]').fill('miembro2@scoutlapaz.bo');
  await page.locator('input[name=password]').fill('Scout2026!');
  await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle2' }), page.click('form button')]);
  await shot('miembro-eventos', '/panel/eventos');
  const eventHref = await page.evaluate(() => Array.from(document.querySelectorAll('a')).find((link) => link.textContent.includes('Ver detalles'))?.href);
  if (!eventHref) throw new Error('No se encontró el enlace al detalle de un evento.');
  await shot('miembro-evento-detalle', new URL(eventHref).pathname);
  await shot('miembro-biblioteca', '/panel/biblioteca');
  const district = await browser.createBrowserContext();
  const admin = await district.newPage();
  await admin.setViewport({ width: 1600, height: 900, deviceScaleFactor: 1 });
  await admin.goto(base + '/login', { waitUntil: 'networkidle2' });
  await admin.locator('input[name=email]').fill('distrito@scoutlapaz.bo');
  await admin.locator('input[name=password]').fill('Scout2026!');
  await Promise.all([admin.waitForNavigation({ waitUntil: 'networkidle2' }), admin.click('form button')]);
  for (const [name, url] of [['distrito-resumen', '/panel'], ['distrito-eventos', '/panel/eventos'], ['distrito-evento-crear', '/panel/eventos/create'], ['distrito-miembros', '/panel/miembros']]) {
    const response = await admin.goto(base + url, { waitUntil: 'networkidle2' });
    await admin.screenshot({ path: path.join(out, name + '.png') });
    console.log(name, response.status(), admin.url(), await admin.title());
  }
  await browser.close();
})().catch((error) => { console.error(error); process.exit(1); });
