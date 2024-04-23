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
    'Referer': 'https://www.propertyfinder.ae/en/rent/properties-for-rent.html',
    'Origin': 'https://www.propertyfinder.ae'
  });

  // await page.setUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537');
  await page.setJavaScriptEnabled(true);

  // Set screen size
  await page.setViewport({width: 1080, height: 1024});

  // Navigate the page to a URL
  await page.goto(url);

  // console.log the page content
  // console.log(await page.content());

  // console.log the current url 
  // console.log(page.url());
  
  const hrefValue = await page.evaluate(() => {
    return document.querySelector('.property-page__legal-list-content').innerHTML;
  });
  
  console.log(hrefValue);

  await browser.close();
})();
