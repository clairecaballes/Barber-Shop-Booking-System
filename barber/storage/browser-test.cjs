const puppeteer = require('puppeteer-core');

(async () => {
  const browser = await puppeteer.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: 'new',
    args: ['--no-sandbox', '--disable-dev-shm-usage'],
  });
  const page = await browser.newPage();
  page.setDefaultTimeout(20000);
  const errors = [];
  page.on('pageerror', e => errors.push('pageerror: ' + e.message));
  page.on('console', m => { if (m.type() === 'error') errors.push('console: ' + m.text()); });
  page.on('response', r => { if (r.url().includes('/quick-bookings')) console.log('RESP', r.status(), r.url()); });
  const base = 'http://127.0.0.1:8011';
  const sleep = ms => new Promise(r => setTimeout(r, ms));

  await page.goto(base + '/login', { waitUntil: 'domcontentloaded' });
  await sleep(800);
  await page.waitForSelector('#email', { visible: true });
  await page.type('#email', 'owner@barbershop.test');
  await page.type('#password', 'password');
  await Promise.all([page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => 'no-nav'),
                     page.click('button[type=submit]')]);
  await sleep(1000);
  console.log('after login url:', page.url());

  const resp = await page.goto(base + '/quick-bookings', { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(e => ({ failed: e.message }));
  console.log('goto result:', resp && resp.failed ? resp.failed : resp.status());
  await sleep(1500);
  const info = await page.evaluate(() => ({
    url: location.href,
    title: document.title,
    hasInput: !!document.querySelector('#customer_name'),
    bodyLen: document.body ? document.body.innerHTML.length : 0,
    bodyStart: document.body ? document.body.innerHTML.slice(0, 300) : '(no body)',
  }));
  console.log(JSON.stringify(info, null, 2));
  console.log('ERRORS:', JSON.stringify(errors, null, 2));
  await browser.close();
})().catch(e => { console.error('FATAL', e.message); process.exit(1); });
