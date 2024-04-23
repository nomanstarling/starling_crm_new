import puppeteer from 'puppeteer';

(async () => {
  // Launch the browser and open a new blank page
  const browser = await puppeteer.launch({headless: "new", args: ['--no-sandbox', '--disable-setuid-sandbox'], executablePath: '/usr/bin/chromium-browser'});
  // const browser = await puppeteer.launch({headless: "new", args: ['--no-sandbox', '--disable-setuid-sandbox'], executablePath: '/opt/homebrew/bin/chromium'});
  const page = await browser.newPage();

  let myArgs = process.argv.slice(2);

  // if there are no args, bail
  if (myArgs.length === 0) {
    console.log('No arguments passed');
    await browser.close();
    return;
  }

  let url = myArgs[0];

  // if the url ends in a !, remove it
  if (url.endsWith('!')) {
    url = url.slice(0, -1);
  }

  // set page referrer
  await page.setExtraHTTPHeaders({
    'Referer': 'https://www.propertyfinder.ae/en/buy/apartment-for-sale-dubai-dubai-marina-dubai-marina-towers-7190.html',
    'Origin': 'https://www.propertyfinder.ae',
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4585.0 Safari/537.36'
  });

  // Navigate the page to a URL
  await page.goto(url);

  // Set screen size
  await page.setViewport({width: 1080, height: 1024});

  // now get the href value from #main_block > div > a
  const hrefValue = await page.evaluate(() => {
    return document.querySelector('#main_block > div > a').href;
  });

  // get #main_block > div > h3 value
  const h3Value = await page.evaluate(() => {
    const element = document.querySelector('#main_block > div > h3');
    return element ? element.innerText : 'PF Whatsapp';
  });
  
  // output the href value
  console.log('My name is ' + h3Value);
  console.log(hrefValue);

  await browser.close();
})();
