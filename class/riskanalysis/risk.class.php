<?php
/* Copyright (C) 2021-2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/riskanalysis/risk.class.php
 * \ingroup     digiriskdolibarr
 * \brief       This file is a CRUD class file for Risk (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

require_once __DIR__ . '/../../lib/digiriskdolibarr_function.lib.php';
require_once __DIR__ . '/../digiriskelement.class.php';
require_once __DIR__ . '/../../../saturne/class/task/saturnetask.class.php';
require_once __DIR__ . '/riskassessment.class.php';

// Load Saturne libraries.
require_once __DIR__ . '/../../../saturne/class/saturneobject.class.php';

/**
 * Class for Risk
 */
class Risk extends SaturneObject
{
	/**
	 * @var string Module name.
	 */
	public $module = 'digiriskdolibarr';

	/**
	 * @var string Element type of object.
	 */
	public $element = 'risk';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'digiriskdolibarr_risk';

	/**
	 * @var int Does this object support multicompany module ?
	 * 0 = No test on entity, 1 = Test with field entity, 'field@table' = Test with link by field@table.
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int Does object support extrafields ? 0 = No, 1 = Yes.
	 */
	public int $isextrafieldmanaged = 1;

    public const STATUS_DELETED   = -1;
    public const STATUS_DRAFT     = 0;
    public const STATUS_VALIDATED = 1;
    public const STATUS_LOCKED    = 2;
    public const STATUS_ARCHIVED  = 3;

	/**
	 * @var string String with name of icon for risk. Must be the part after the 'object_' into object_risk.png
	 */
	public $picto = 'fontawesome_fa-exclamation-triangle_fas_#d35968';

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields = array(
		'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => '1', 'position' => 10, 'notnull' => 1, 'visible' => 0, 'noteditable' => '1', 'index' => 1, 'comment' => "Id"),
		'fk_element' => array('type' => 'integer', 'label' => 'ParentElement', 'enabled' => '1', 'position' => 9, 'notnull' => 1, 'visible' => 1, 'csslist' => 'minwidth200 maxwidth300 widthcentpercentminusxx'),
		'ref' => array('type' => 'varchar(128)', 'label' => 'Ref', 'enabled' => '1', 'position' => 20, 'notnull' => 1, 'visible' => 4, 'noteditable' => '1', 'index' => 1, 'searchall' => 1, 'showoncombobox' => '1', 'comment' => "Reference of object"),
		'ref_ext' => array('type' => 'varchar(128)', 'label' => 'RefExt', 'enabled' => '1', 'position' => 30, 'notnull' => 0, 'visible' => 0,),
		'entity' => array('type' => 'integer', 'label' => 'Entity', 'enabled' => '1', 'position' => 8, 'notnull' => 1, 'visible' => 0,),
		'date_creation' => array('type' => 'datetime', 'label' => 'DateCreation', 'enabled' => '1', 'position' => 50, 'notnull' => 1, 'visible' => 0,),
		'tms' => array('type' => 'timestamp', 'label' => 'DateModification', 'enabled' => '1', 'position' => 60, 'notnull' => 0, 'visible' => 0,),
		'import_key' => array('type' => 'varchar(14)', 'label' => 'ImportId', 'enabled' => '1', 'position' => 70, 'notnull' => -1, 'visible' => 0,),
		'status' => array('type' => 'smallint', 'label' => 'Status', 'enabled' => '1', 'position' => 80, 'notnull' => 0, 'visible' => 0,),
		'category' => array('type' => 'varchar(255)', 'label' => 'RiskCategory', 'enabled' => '1', 'position' => 21, 'notnull' => 0, 'visible' => 1,),
		'description' => array('type' => 'text', 'label' => 'Description', 'enabled' => '1', 'position' => 23, 'notnull' => 0, 'visible' => -1,),
		'type' => array('type' => 'varchar(255)', 'label' => 'Type', 'enabled' => '1', 'position' => 24, 'notnull' => 1, 'visible' => 0, 'default' => '(PROV)'),
		'fk_user_creat' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserAuthor', 'enabled' => '1', 'position' => 110, 'notnull' => 1, 'visible' => 0, 'foreignkey' => 'user.rowid',),
		'fk_user_modif' => array('type' => 'integer:User:user/class/user.class.php', 'label' => 'UserModif', 'enabled' => '1', 'position' => 120, 'notnull' => -1, 'visible' => 0,),
		'fk_projet' => array('type' => 'integer:Project:projet/class/project.class.php', 'label' => 'Projet', 'enabled' => '1', 'position' => 140, 'notnull' => 1, 'visible' => 0,),
	);

	public $rowid;
	public $ref;
	public $ref_ext;
	public $entity;
	public $date_creation;
	public $tms;
	public $import_key;
	public $status;
	public $category;
	public $description;
    public $type = 'risk';
	public $fk_user_creat;
	public $fk_user_modif;
	public $fk_element;
	public $fk_projet;
	public $lastEvaluation;
	public $appliedOn;

	/**
	 * Constructor.
	 *
	 * @param DoliDb $db Database handler.
	 */
	public function __construct(DoliDB $db)
	{
		parent::__construct($db, $this->module, $this->element);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $parent_id Id parent object
	 * @return array|int         <0 if KO, 0 if not found, >0 if OK
	 * @throws Exception
	 */
	public function fetchFromParent(int $parent_id)
	{
		$filter = array('customsql' => 'fk_element=' . $this->db->escape($parent_id));
		return $this->fetchAll('', '', 0, 0, $filter, 'AND');
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int $parent_id Id parent object
	 * @param bool $get_children_data Get children risks data
	 * @param bool $get_parents_data Get parents risks data
	 * @param bool $get_shared_data Get parents risks data
     * @param  array     $moreParams More params(Object/user/etc)
	 * @return array|int         <0 if KO, 0 if not found, >0 if OK
	 * @throws Exception
	 */
	public function fetchRisksOrderedByCotation($parent_id, $get_children_data = false, $get_parents_data = false, $get_shared_data = false, $moreParams = [])
	{
		$object  = new DigiriskElement($this->db);
		$objects = $object->getActiveDigiriskElements();

		$risk     = new Risk($this->db);
		$riskList = $risk->fetchAll('', '', 0, 0, ['customsql' => 'status = ' . self::STATUS_VALIDATED . $moreParams['filterRisk']], 'AND', $get_shared_data ? 1 : 0);

		$riskAssessment     = new RiskAssessment($this->db);
		$riskAssessmentList = $riskAssessment->fetchAll('', '', 0, 0, ['customsql' => 'status = ' . RiskAssessment::STATUS_VALIDATED . $moreParams['filterRiskAssessment']], 'AND', $get_shared_data ? 1 : 0);

		if (is_array($riskAssessmentList) && !empty($riskAssessmentList)) {
			foreach ($riskAssessmentList as $riskAssessmentSingle) {
				$riskAssessmentsOrderedByRisk[$riskAssessmentSingle->fk_risk] = $riskAssessmentSingle;
			}
		}

		if (is_array($riskList) && !empty($riskList)) {
			foreach ($riskList as $riskSingle) {
				$riskSingle->lastEvaluation = $riskAssessmentsOrderedByRisk[$riskSingle->id];
				$riskSingle->appliedOn = $riskSingle->fk_element;
				$risksOrderedByDigiriskElement[$riskSingle->fk_element][] = $riskSingle;
			}
		}

		$risks = [];

		//For groupment & workunit documents with given id
		if ($parent_id > 0) {
			$risksOfDigiriskElement = $risksOrderedByDigiriskElement[$parent_id];
			// RISKS de l'élément parent.
			if (is_array($risksOfDigiriskElement) && !empty($risksOfDigiriskElement)) {
				foreach ($risksOfDigiriskElement as $riskOfDigiriskElement) {
					$riskOfDigiriskElement->appliedOn = $parent_id;
					$risks[] = $riskOfDigiriskElement;
				}
			}
		}


		//For risks listing of risk assessment document & risks listings
		if ( $get_children_data ) {
			if (is_array($objects) && !empty($objects)) {
				$elementsChildren = recurse_tree($parent_id, 0, $objects);
			} else {
				return -1;
			}

			if ( is_array($elementsChildren) && ! empty($elementsChildren) ) {
				// Super function iterations flat.
				$it = new RecursiveIteratorIterator(new RecursiveArrayIterator($elementsChildren));
				$element = array();
				foreach ($it as $key => $v) {
					$element[$key][$v] = $v;
				}

				$children_ids = $element['id'];

				// RISKS parent children.
				if ( !empty($children_ids)) {
					foreach ($children_ids as $child_id) {
						if (is_array($risksOrderedByDigiriskElement[$child_id]) && !empty($risksOrderedByDigiriskElement[$child_id])) {
							$risks = array_merge($risks, $risksOrderedByDigiriskElement[$child_id]);
						}
					}
				}
			}
		}

//		for groupment & workunit document & risk assessment document if get inherited risks conf is activated
		if ( $get_parents_data ) {
			if ($parent_id > 0) {
				$parent_element_id = $objects[$parent_id]->fk_parent;
				while ($parent_element_id > 0) {
					if (is_array($risksOrderedByDigiriskElement[$parent_element_id]) && !empty($risksOrderedByDigiriskElement[$parent_element_id])) {
						foreach($risksOrderedByDigiriskElement[$parent_element_id] as $riskOfParentDigiriskElement) {
							$riskOfParentDigiriskElement->appliedOn = $parent_id;
							$risks[] = $riskOfParentDigiriskElement;
						}
					}
					$parent_element_id = $objects[$parent_element_id]->fk_parent;
				}
			} else {
				//For inherited risks in risk assessment document
				if (is_array($objects) && !empty($objects)) {
					foreach ($objects as $digiriskElement) {
						$parent_element_id = $digiriskElement->fk_parent;
						while ($parent_element_id > 0) {
							if (is_array($risksOrderedByDigiriskElement[$parent_element_id]) && !empty($risksOrderedByDigiriskElement[$parent_element_id])) {
								foreach($risksOrderedByDigiriskElement[$parent_element_id] as $riskOfParentDigiriskElement) {
									$tempRiskOfParentDigiriskElement = new Risk($this->db);
									$tempRiskOfParentDigiriskElement->setVarsFromFetchObj($riskOfParentDigiriskElement);

									$tempRiskOfParentDigiriskElement->lastEvaluation = $riskOfParentDigiriskElement->lastEvaluation;
									$tempRiskOfParentDigiriskElement->appliedOn = $digiriskElement->id;
									$tempRiskOfParentDigiriskElement->id = $riskOfParentDigiriskElement->id;

									$appliedOnIds[$riskOfParentDigiriskElement->id][] = $digiriskElement->id;

									$risks[] = $tempRiskOfParentDigiriskElement;
								}
							}
							$parentDigiriskElement = $objects[$parent_element_id];
							$parent_element_id = $parentDigiriskElement->fk_parent;
						}
					}
				}
			}
		}

		//For all documents
		if ( $get_shared_data ) {
			if ($parent_id == 0) {
				$digiriskElementsOfEntity = $objects;
				if (is_array($digiriskElementsOfEntity) && !empty($digiriskElementsOfEntity)) {
					foreach ($digiriskElementsOfEntity as $digiriskElementOfEntity) {
						$digiriskElementOfEntity->fetchObjectLinked(null, '', $digiriskElementOfEntity->id, 'digiriskdolibarr_digiriskelement', 'AND', 1, 'sourcetype', 0);
						if (!empty($digiriskElementOfEntity->linkedObjectsIds['digiriskdolibarr_risk'])) {
							foreach ($digiriskElementOfEntity->linkedObjectsIds['digiriskdolibarr_risk'] as $sharedRiskId) {
								$sharedRisk = $riskList[$sharedRiskId];
								if (is_object($sharedRisk)) {
									$sharedRisk->appliedOn = $digiriskElementOfEntity->id;
									$risks[] = $sharedRisk;
								}
							}
						}
					}
				}
			} else {
				if (array_key_exists($parent_id, $objects)) {
					$parentElement = $objects[$parent_id];
					$parentElement->fetchObjectLinked(null, '', $parent_id, 'digiriskdolibarr_digiriskelement', 'AND', 1, 'sourcetype', 0);
					if (!empty($parentElement->linkedObjectsIds['digiriskdolibarr_risk'])) {
						foreach ($parentElement->linkedObjectsIds['digiriskdolibarr_risk'] as $sharedRiskId) {
							$sharedRisk = $riskList[$sharedRiskId];
							if (is_object($sharedRisk)) {
								$sharedRisk->appliedOn = $parent_id;
								$risks[] = $sharedRisk;
							}
						}
					}
				}
			}
		}

		if ( ! empty($risks) && is_array($risks)) {
			usort($risks, function ($first, $second) {
				return $first->lastEvaluation->cotation < $second->lastEvaluation->cotation;
			});
			return $risks;
		} else {
			return -1;
		}
	}

	/**
	 * Get risk categories json in /digiriskdolibarr/js/json/
	 *
	 * @return	array $risk_categories
	 */
	public function getDangerCategories()
	{
		$json_categories = file_get_contents(DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/js/json/dangerCategories.json');
        $jsonArray       = json_decode($json_categories, true);
		return $jsonArray[0][$this->type];
	}

	/**
	 * Get danger category picto path
	 *
	 * @param $object
	 * @return    string $category['thumbnail_name']     path to danger category picto, -1 if don't exist
	 */
	public function getDangerCategory($object)
	{
		$risk_categories = $this->getDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $object->category) {
				return $category['thumbnail_name'];
			}
		}

		return -1;
	}

	/**
	 * Get danger category picto name
	 *
	 * @param $object
	 * @return    string $category['name']     name to danger category picto, -1 if don't exist
	 */
	public function getDangerCategoryName($object)
	{
		$risk_categories = $this->getDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $object->category) {
				return $category['name'];
			}
		}

		return -1;
	}

	/**
	 * Get danger category picto name
	 *
	 * @param $name
	 * @return    string $category['name']     name to danger category picto, -1 if don't exist
	 */
	public function getDangerCategoryPositionByName($name)
	{
		$risk_categories = $this->getDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['name'] == $name || $category['nameDigiriskWordPress'] == $name) {
				return $category['position'];
			}
		}

		return -1;
	}

	/**
	 * Get danger category picto path
	 *
	 * @param int $position
	 * @return    string $category['thumbnail_name']     path to danger category picto, -1 if don't exist
	 */
	public function getDangerCategoryByPosition($position)
	{
		$risk_categories = $this->getDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $position) {
				return $category['thumbnail_name'];
			}
		}

		return -1;
	}

	/**
	 * Get danger category picto path
	 *
	 * @param int $position
	 * @return    string $category['thumbnail_name']     path to danger category picto, -1 if don't exist
	 */
	public function getDangerCategoryNameByPosition($position)
	{
		$risk_categories = $this->getDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $position) {
				return $category['name'];
			}
		}

		return -1;
	}

	/**
	 * Get fire permit risk categories json in /digiriskdolibarr/js/json/
	 *
	 * @return	array $risk_categories
	 */
	public function getFirePermitDangerCategories()
	{
		$json_categories = file_get_contents(DOL_DOCUMENT_ROOT . '/custom/digiriskdolibarr/js/json/firePermitDangerCategories.json');
		return json_decode($json_categories, true);
	}

	/**
	 * Get fire permit danger category picto path
	 *
	 * @param $object
	 * @return    string $category['thumbnail_name']     path to fire permit danger category picto, -1 if don't exist
	 */
	public function getFirePermitDangerCategory($object)
	{
		$risk_categories = $this->getFirePermitDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $object->category) {
				return $category['thumbnail_name'];
			}
		}

		return -1;
	}

	/**
	 * Get fire permit danger category picto name
	 *
	 * @param $object
	 * @return    string $category['name']     name to fire permit danger category picto, -1 if don't exist
	 */
	public function getFirePermitDangerCategoryName($object)
	{
		$risk_categories = $this->getFirePermitDangerCategories();
		foreach ($risk_categories as $category) {
			if ($category['position'] == $object->category) {
				return $category['name'];
			}
		}

		return -1;
	}

	/**
	 * Get children tasks
	 *
	 * @param $risk
	 * @return array|int $records or -1 if error
	 * @throws Exception
	 */
	public function getRelatedTasks($risk)
	{
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . 'projet_task_extrafields' . ' WHERE fk_risk =' . $risk->id;

		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i   = 0;
			$records = array();
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$record = new SaturneTask($this->db);
				$record->fetch($obj->fk_object);
				$records[$record->id] = $record;
				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Get children tasks
	 *
	 * @param $risk
	 * @return array|int $records or -1 if error
	 * @throws Exception
	 */
	public function getTasksWithFkRisk()
	{
		$sql = "SELECT * FROM " . MAIN_DB_PREFIX . 'projet_task_extrafields' . ' WHERE fk_risk > 0';
		$tasksList = saturne_fetch_all_object_type('SaturneTask', '', '', 0, 0, [], 'AND', false, false);

		$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i   = 0;
			$records = array();
			while ($i < $num) {
				$obj = $this->db->fetch_object($resql);
				$records[$obj->fk_risk][$obj->rowid] = $tasksList[$obj->fk_object];
				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

    /**
     * Load dashboard info risk
     *
     * @return array
     * @throws Exception
     */
    public function load_dashboard(): array
    {
        $arrayRisksByCotation = $this->getRisksByCotation();

        $array['graphs'] = [$arrayRisksByCotation];

        return $array;
    }

    /**
     * Get risks by cotation
     *
     * @return array
     * @throws Exception
     */
    public function getRisksByCotation(): array
    {
        global $conf, $langs;

        $riskAssessment = new RiskAssessment($this->db);

        // Graph Title parameters
        $array['title'] = $langs->transnoentities('RisksRepartition');
        $array['picto'] = $this->picto;

        // Graph parameters
        $array['width']      = '100%';
        $array['height']     = 400;
        $array['type']       = 'pie';
        $array['showlegend'] = $conf->browser->layout == 'phone' ? 1 : 2;
        $array['dataset']    = 1;

        $array['labels'] = [
            1 => [
                'label' => $langs->transnoentities('GreyRisk'),
                'color' => '#ececec'
            ],
            2 => [
                'label' => $langs->transnoentities('OrangeRisk'),
                'color' => '#e9ad4f'
            ],
            3 => [
                'label' => $langs->transnoentities('RedRisk'),
                'color' => 'e05353'
            ],
            4 => [
                'label' => $langs->transnoentities('BlackRisk'),
                'color' => '#2b2b2b'
            ]
        ];

        $riskAssessmentList = $riskAssessment->fetchAll('', '', 0, 0, ['customsql' => 'status = 1']);
        $array['data']      = $riskAssessment->getRiskAssessmentCategoriesNumber($riskAssessmentList);

        return $array;
	  }

    /**
     * Write information of trigger description
     *
     * @param  Object $object Object calling the trigger
     * @return string         Description to display in actioncomm->note_private
     */
    public function getTriggerDescription(SaturneObject $object): string
    {
        global $conf, $langs;

        $ret = parent::getTriggerDescription($object);

        $digiriskelement = new DigiriskElement($this->db);
        $digiriskelement->fetch($object->fk_element);

        $ret .= $langs->trans('ParentElement') . ' : ' . $digiriskelement->ref . " - " . $digiriskelement->label . '<br>';
        $ret .= $langs->trans('RiskCategory') . ' : ' . $object->getDangerCategoryName($object) . '<br>';

        if (dol_strlen($object->applied_on) > 0) {
            $digiriskelement->fetch($object->applied_on);
            $ret .= $langs->trans('RiskSharedWithEntityRefLabel', $object->ref) . ' S' . $conf->entity . ' ' . $digiriskelement->ref . " - " . $digiriskelement->label . '<br>';
        }

        return $ret;
    }
}
