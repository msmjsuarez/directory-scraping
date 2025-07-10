const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const csvWriter = require('csv-write-stream');

(async () => {
  const browser = await puppeteer.launch({ headless: true });
  const page = await browser.newPage();

  const url = 'https://www.ontariosignassociation.com/member-directory';
  await page.goto(url, { waitUntil: 'networkidle2' });

  await page.waitForSelector('#membersTable');
  await page.waitForSelector('select[onchange*="pagerChanged"]');

  const allMembers = new Map(); // use Map to avoid duplicates

  const scrapeCurrentPage = async () => {
    const members = await page.evaluate(() => {
      const rows = Array.from(document.querySelectorAll('#membersTable tr.normal'));
      return rows.map(row => {
        const link = row.querySelector('td.memberDirectoryColumn1 a');
        const company = link?.textContent?.trim().replace(/\(\d+\)$/, '').trim();
        const profileUrl = link?.href || '';
        return { company_name: company, profile_url: profileUrl };
      });
    });

    for (const member of members) {
      allMembers.set(member.profile_url, member); // prevent duplicates
    }
  };

  // Get all page options
  const pageOptions = await page.$$eval(
    'select[onchange*="pagerChanged"] option',
    options => options.map(o => ({ value: o.value, label: o.textContent.trim() }))
  );

  for (const { value, label } of pageOptions) {
    console.log(`Scraping page: ${label}`);

    // Only change page if not the default one
    if (value !== '0') {
      await Promise.all([
        page.select('select[onchange*="pagerChanged"]', value),
        page.waitForFunction(
          (expectedLabel) => {
            const selected = document.querySelector('select[onchange*="pagerChanged"] option:checked');
            return selected && selected.textContent.trim() === expectedLabel;
          },
          {},
          label
        ),
        page.waitForSelector('#membersTable')
      ]);
      await new Promise(resolve => setTimeout(resolve, 500));
    }

    await scrapeCurrentPage();
  }

  await browser.close();

  // Output to CSV
  const writer = csvWriter({ headers: ['company_name', 'profile_url'] });
  const outputPath = path.join(__dirname, 'member_profiles.csv');
  writer.pipe(fs.createWriteStream(outputPath));
  for (const member of allMembers.values()) {
    writer.write(member);
  }
  writer.end();

  console.log(`Scraped ${allMembers.size} unique member profile links.`);
  console.log(`Saved to ${outputPath}`);
})();
