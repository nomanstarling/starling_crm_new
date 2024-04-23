import puppeteer from 'puppeteer';

(async () => {

  const port = process.argv[2] || 81;
  const browser = await puppeteer.launch({headless: "new", ignoreHTTPSErrors: true, args: ['--no-sandbox', '--disable-setuid-sandbox'], executablePath: '/usr/bin/chromium-browser'});
  // const browser = await puppeteer.launch({headless: "new", ignoreHTTPSErrors: true, args: ['--no-sandbox', '--disable-setuid-sandbox'], executablePath: '/opt/homebrew/bin/chromium'});
  const page = await browser.newPage();
  await page.setExtraHTTPHeaders({
    'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/94.0.4585.0 Safari/537.36'
  });
  await page.setViewport({width: 1080, height: 1024});
  await page.goto('https://starling.doomdns.com:' + port + '/enLogin.htm');
  await page.waitForSelector('#form1');
  await page.type('#loginname', 'admin');
  await page.type('#loginpass', 'Starling@123!');
  await page.click('#login_button');
  await page.waitForSelector('frameset');
  await page.goto('https://starling.doomdns.com:' + port + '/enCallCDR.htm');
  await page.waitForSelector('.table_data');
  let rows = await page.$$('.table_data tr');
  if (rows.length == 1)
  {
    const year = new Date().getFullYear();
    const month = new Date().getMonth() + 1;
    const day = new Date().getDate();

    await page.select('#StartYear', year.toString());
    await page.select('#StartMonth', month.toString());
    await page.select('#StartDay', day.toString());

    await page.waitForSelector('input[name="filter"]');
    await page.click('input[name="filter"]');
    await page.waitForSelector('.table_data');
    rows = await page.$$('.table_data tr');
  }

  const data = [];
  for (const row of rows) {
    const columns = await row.$$('td');
    const rowContent = [];
    for (const column of columns) {
      const text = await page.evaluate(element => element.textContent, column);
      rowContent.push(text);
    }
    data.push(rowContent);
  }

  console.log(data);
  await browser.close();
})();
