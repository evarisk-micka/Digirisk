import { test, expect } from '@playwright/test';

const dolibarrUrl = 'http://localhost/dolibarr/';
const digiriskUrl = dolibarrUrl + 'htdocs/custom/digiriskdolibarr/';

test('has title', async ({ page }) => {
  await page.goto(url + 'digiriskdolibarrindex.php');

  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/Welcome to DigiriskDolibarr 9.14.0/);
});

test('get DU page', async ({ page }) => {
  await page.goto(url + 'view/digiriskstandard/digiriskstandard_card.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.refid');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('DU');
});
