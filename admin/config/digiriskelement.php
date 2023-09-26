<?php
/* Copyright (C) 2021-2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    admin/config/digiriskelement.php
 * \ingroup digiriskdolibarr
 * \brief   Digiriskdolibarr digiriskelement page.
 */

// Load DigiriskDolibarr environment
if (file_exists('../digiriskdolibarr.main.inc.php')) {
	require_once __DIR__ . '/../digiriskdolibarr.main.inc.php';
} elseif (file_exists('../../digiriskdolibarr.main.inc.php')) {
	require_once __DIR__ . '/../../digiriskdolibarr.main.inc.php';
} else {
	die('Include of digiriskdolibarr main fails');
}

global $langs, $user, $db;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

require_once __DIR__ . '/../../lib/digiriskdolibarr.lib.php';
require_once __DIR__ . '/../../class/digiriskelement/groupment.class.php';
require_once __DIR__ . '/../../class/digiriskelement/workunit.class.php';

// Translations
saturne_load_langs(["admin"]);

// Parameters
$backtopage = GETPOST('backtopage', 'alpha');

// Security check - Protection if external user
$permissiontoread = $user->rights->digiriskdolibarr->adminpage->read;
saturne_check_access($permissiontoread);

/*
 * Actions
 */

if (GETPOST('action') == 'setmod') {
	if (GETPOST('type') == 'groupment') {
		dolibarr_set_const($db, "DIGIRISKDOLIBARR_GROUPMENT_ADDON", GETPOST('value'), 'chaine', 0, '', $conf->entity);
	} else if (GETPOST('type') == 'workunit') {
		dolibarr_set_const($db, "DIGIRISKDOLIBARR_WORKUNIT_ADDON", GETPOST('value'), 'chaine', 0, '', $conf->entity);
	}
}

if (GETPOST('action') == 'updateMask') {
	dolibarr_set_const($db, GETPOST('mask'), GETPOST('addon_value'), 'chaine', 0, '', $conf->entity);
}

/*
 * View
 */

$title    = $langs->trans("ModuleSetup", $moduleName);
$helpUrl = 'FR:Module_Digirisk#L.27onglet_.C3.89l.C3.A9ment_DigiRisk';

saturne_header(0,'', $title, $helpUrl);

// Subheader
$linkback = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans("BackToModuleList") . '</a>';

print load_fiche_titre($title, $linkback, 'title_setup');

// Configuration header
$head = digiriskdolibarr_admin_prepare_head();
print dol_get_fiche_head($head, 'digiriskelement', '', -1, "digiriskdolibarr@digiriskdolibarr");

$pictos = array(
	'groupment' => '<span class="ref" style="font-size: 10px; color: #fff; text-transform: uppercase; font-weight: 600; display: inline-block; background: #263C5C; padding: 0.2em 0.4em; line-height: 10px !important">GP</span> ',
	'workunit' => '<span class="ref" style="background: #0d8aff;  font-size: 10px; color: #fff; text-transform: uppercase; font-weight: 600; display: inline-block;; padding: 0.2em 0.4em; line-height: 10px !important">WU</span> '
);

/*
 *  Numbering module
 */

$objectModSubdir = 'digiriskelement';

$object = new Groupment($db);

print load_fiche_titre($pictos['groupment'] . $langs->trans('GroupmentManagement'), '', '');
print '<hr>';

require __DIR__ . '/../../../saturne/core/tpl/admin/object/object_numbering_module_view.tpl.php';

$object = new WorkUnit($db);

print load_fiche_titre($pictos['workunit'] . $langs->trans('WorkUnitManagement'), '', '');
print '<hr>';

require __DIR__ . '/../../../saturne/core/tpl/admin/object/object_numbering_module_view.tpl.php';

/*
 *  Deleted elements
 */

print load_fiche_titre('<i class="fas fa-trash"></i> ' . $langs->trans('DeletedElements'), '', '');
print '<hr>';

$constArray[$moduleNameLowerCase] = [
	'DeletedDigiriskElement' => [
		'name'        => 'DeletedDigiriskElement',
		'description' => 'ShowDeletedDigiriskElement',
		'code'        => 'DIGIRISKDOLIBARR_SHOW_HIDDEN_DIGIRISKELEMENT',
	],
];
require __DIR__ . '/../../../saturne/core/tpl/admin/object/object_const_view.tpl.php';

// Page end
print dol_get_fiche_end();
llxFooter();
$db->close();
