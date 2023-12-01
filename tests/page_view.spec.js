import { test, expect } from '@playwright/test';

const dolibarrUrl = 'http://localhost/dolibarr/htdocs/';
const digiriskUrl = dolibarrUrl + 'custom/digiriskdolibarr/';

test('has title', async ({ page }) => {
  await page.goto(digiriskUrl + 'digiriskdolibarrindex.php');

  // Expect a title "to contain" a substring.
  await expect(page).toHaveTitle(/Welcome to DigiriskDolibarr 9.14.0/);
});

test('get DU page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskstandard/digiriskstandard_card.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.refid');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('DU');
});

test('get RiskList page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskelement/risk_list.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('DigiriskElementRisksList');
});

test('get PreventionPlanList page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/preventionplan/preventionplan_list.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('PreventionPlanList');
});

test('get FirePermitList page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/firepermit/firepermit_list.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('FirePermitList');
});

test('get AccidentList page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/accident/accident_list.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('AccidentList');
});

test('get AccidentsCategoriesArea page', async ({ page }) => {
  await page.goto(dolibarrUrl + 'categories/index.php?type=accident&mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('AccidentsCategoriesArea');
});

test('get AccidentInvestigationList page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/accidentinvestigation/accidentinvestigation_list.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.titre.inline-block');

  await expect(locator).toBeVisible();
  await expect(locator).toContainText('AccidentInvestigationList');
});

test('get GPUT Organization page', async ({ page }) => {
  await page.goto(digiriskUrl + 'view/digiriskelement/digiriskelement_organization.php?mainmenu=digiriskdolibarr');

  const locator = page.locator('.title').first();

  await expect(locator).toBeVisible();
  //await expect(locator).toContainText('DU');
});
