import { test as setup, expect } from '@playwright/test';

const authFile = 'playwright/.auth/user.json';

setup('authenticate', async ({ page }) => {
  await page.goto('http://localhost/dolibarr/htdocs/index.php');
  await page.locator('#username').fill('adminadmin');
  await page.locator('#password').fill('adminadmin');
  await page.getByRole('button').click();
  await page.goto('http://localhost/dolibarr/htdocs/custom/digiriskdolibarr/digiriskdolibarrindex.php');

  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/Welcome to DigiriskDolibarr 9.14.0/);

  // End of authentication steps.

  await page.context().storageState({ path: authFile });
});
