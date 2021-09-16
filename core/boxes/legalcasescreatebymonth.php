<?php
/* Copyright (C) 2004-2017  Laurent Destailleur <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2020  Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2021 Super Admin
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
 * \file    legalcong/core/boxes/legalcasescreatebymonth.php
 * \ingroup legalcong
 * \brief   Widget provided by LegalCong
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php";


/**
 * Class to manage the box
 *
 * Warning: for the box to be detected correctly by dolibarr,
 * the filename should be the lowercase classname
 */
class legalcasescreatebymonth extends ModeleBoxes
{
	/**
	 * @var string Alphanumeric ID. Populated by the constructor.
	 */
	public $boxcode = "legalcongbox5";

	/**
	 * @var string Box icon (in configuration page)
	 * Automatically calls the icon named with the corresponding "object_" prefix
	 */
	public $boximg = "legalcong@legalcong";

	/**
	 * @var string Box label (in configuration page)
	 */
	public $boxlabel;

	/**
	 * @var string[] Module dependencies
	 */
	public $depends = array('legalcong');

	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var mixed More parameters
	 */
	public $param;

	/**
	 * @var array Header informations. Usually created at runtime by loadBox().
	 */
	public $info_box_head = array();

	/**
	 * @var array Contents informations. Usually created at runtime by loadBox().
	 */
	public $info_box_contents = array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 * @param string $param More parameters
	 */
	public function __construct(DoliDB $db, $param = '')
	{
		global $user, $conf, $langs;
		$langs->load("boxes");
		$langs->load('legalcong@legalcong');

		parent::__construct($db, $param);

		$this->boxlabel = $langs->transnoentitiesnoconv("BoxLegalCasesCreateByMonth");

		$this->param = $param;

		//$this->enabled = $conf->global->FEATURES_LEVEL > 0;         // Condition when module is enabled or not
		//$this->hidden = ! ($user->rights->legalcong->myobject->read);   // Condition when module is visible by user (test on permission)
	}

	/**
	 * Load data into info_box_contents array to show array later. Called by Dolibarr before displaying the box.
	 *
	 * @param int $max Maximum number of records to load
	 * @return void
	 */
	public function loadBox($max = 5)
	{
		global $conf, $user, $langs;
		
		
		$this->max = $max;

		$refreshaction = 'refresh_'.$this->boxcode;

		//include_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
		//$commandestatic=new Commande($this->db);

		$startmonth = $conf->global->SOCIETE_FISCAL_MONTH_START ? ($conf->global->SOCIETE_FISCAL_MONTH_START) : 1;
		if (empty($conf->global->GRAPH_USE_FISCAL_YEAR)) $startmonth = 1;

		$text = $langs->trans("BoxLegalCasesCreateByMonth", $max);
		$this->info_box_head = array(
				'text' => $text,
				'limit'=> dol_strlen($text),
				'graph'=> 1,
				'sublink'=>'',
				'subtext'=>$langs->trans("Filter"),
				'subpicto'=>'filter.png',
				'subclass'=>'linkobject boxfilter',
				'target'=>'none'	// Set '' to get target="_blank"
		);

		$dir = ''; // We don't need a path because image file will not be saved into disk
		$prefix = '';
		$socid = 0;
		if ($user->socid) $socid = $user->socid;
		//if (!$user->rights->societe->client->voir || $socid) $prefix .= 'private-'.$user->id.'-'; // If user has no permission to see all, output dir is specific to user

		if ($user->rights->commande->lire || 1==1)
		{
			$langs->load("legalcong@legalcong");

			$param_year = 'DOLUSERCOOKIE_box_'.$this->boxcode.'_year';
			$param_shownb = 'DOLUSERCOOKIE_box_'.$this->boxcode.'_shownb';
			$param_showtot = 'DOLUSERCOOKIE_box_'.$this->boxcode.'_showtot';

			include_once DOL_DOCUMENT_ROOT.'/core/class/dolgraph.class.php';
			include_once DOL_DOCUMENT_ROOT.'/custom/legalcong/class/legalcongstats.class.php';
			$autosetarray = preg_split("/[,;:]+/", GETPOST('DOL_AUTOSET_COOKIE'));
			if (in_array('DOLUSERCOOKIE_box_'.$this->boxcode, $autosetarray))
			{
				$endyear = GETPOST($param_year, 'int');
				$shownb = 1;
				$showtot = 0;
			} else {
				$tmparray = json_decode($_COOKIE['DOLUSERCOOKIE_box_'.$this->boxcode], true);
				$endyear = $tmparray['year'];
				$shownb = 1;
				$showtot = 0;
			}
			if (empty($shownb) && empty($showtot)) { $shownb = 1; $showtot = 1; }
			$nowarray = dol_getdate(dol_now(), true);
			if (empty($endyear)) $endyear = $nowarray['year'];
			$startyear = $endyear - (empty($conf->global->MAIN_NB_OF_YEAR_IN_WIDGET_GRAPH) ? 1 : $conf->global->MAIN_NB_OF_YEAR_IN_WIDGET_GRAPH);

			$mode = 'customer';
			$WIDTH = (($shownb && $showtot) || !empty($conf->dol_optimize_smallscreen)) ? '256' : '320';
			$HEIGHT = '192';

			$stats = new LegalCongStats($this->db, $socid, $mode, 0);

			// Build graphic number of object. $data = array(array('Lib',val1,val2,val3),...)
			if ($shownb)
			{
				$data1 = $stats->getCasesByMonthWithPrevYearLegal($endyear, $startyear, (GETPOST('action', 'aZ09') == $refreshaction ?-1 : (3600 * 24)), ($WIDTH < 300 ? 2 : 0), $startmonth, "ATC");

				$filenamenb = $dir."/".$prefix."casesnbinyear-".$endyear.".png";
				// default value for customer mode
				$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=legalcongstats&amp;file=casessnbinyear-'.$endyear.'.png';
				//if ($mode == 'supplier') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=orderstatssupplier&amp;file=ordersnbinyear-'.$endyear.'.png';

				$px1 = new DolGraph();
				$mesg = $px1->isGraphKo();
				if (!$mesg)
				{
					$px1->SetData($data1);
					unset($data1);
					$i = $startyear;
					$legend = array();
					while ($i <= $endyear)
					{
						if ($startmonth != 1)
						{
							$legend[] = sprintf("%d/%d", $i - 2001, $i - 2000);
						} else {
							$legend[] = $i;
						}
						$i++;
					}
					$px1->SetLegend($legend);
					$px1->SetMaxValue($px1->GetCeilMaxValue());
					$px1->SetWidth($WIDTH);
					$px1->SetHeight($HEIGHT);
					$px1->SetYLabel($langs->trans("NumberOfCases"));
					$px1->SetShading(3);
					$px1->setShowPointValue(0);
					$px1->SetHorizTickIncrement(1);
					$px1->SetCssPrefix("cssboxes");
					$px1->mode = 'depth';
					$px1->SetTitle($langs->trans("NumberOfCasesByMonth"));

					$px1->draw($filenamenb, $fileurlnb);
				}
			}

			// Build graphic number of object. $data = array(array('Lib',val1,val2,val3),...)
			if ($showtot)
			{
				$data2 = $stats->getAmountByMonthWithPrevYear($endyear, $startyear, (GETPOST('action', 'aZ09') == $refreshaction ?-1 : (3600 * 24)), ($WIDTH < 300 ? 2 : 0), $startmonth);

				$filenamenb = $dir."/".$prefix."casesamountinyear-".$endyear.".png";
				// default value for customer mode
				$fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=legalcongstats&amp;file=casesamountinyear-'.$endyear.'.png';
				//if ($mode == 'supplier') $fileurlnb = DOL_URL_ROOT.'/viewimage.php?modulepart=orderstatssupplier&amp;file=ordersamountinyear-'.$endyear.'.png';

				$px2 = new DolGraph();
				$mesg = $px2->isGraphKo();
				if (!$mesg)
				{
					$px2->SetData($data2);
					unset($data2);
					$i = $startyear; $legend = array();
					while ($i <= $endyear)
					{
						if ($startmonth != 1)
						{
							$legend[] = sprintf("%d/%d", $i - 2001, $i - 2000);
						} else {
							$legend[] = $i;
						}
						$i++;
					}
					$px2->SetLegend($legend);
					$px2->SetMaxValue($px2->GetCeilMaxValue());
					$px2->SetWidth($WIDTH);
					$px2->SetHeight($HEIGHT);
					$px2->SetYLabel($langs->trans("AmountOfCasessHT"));
					$px2->SetShading(3);
					$px2->SetHorizTickIncrement(1);
					$px2->SetCssPrefix("cssboxes");
					$px2->mode = 'depth';
					$px2->SetTitle($langs->trans("AmountOfCasesByMonthHT"));

					$px2->draw($filenamenb, $fileurlnb);
				}
			}

			if (empty($conf->use_javascript_ajax))
			{
				$langs->load("errors");
				$mesg = $langs->trans("WarningFeatureDisabledWithDisplayOptimizedForBlindNoJs");
			}

			if (!$mesg)
			{
				$stringtoshow = '';
				$stringtoshow .= '<script type="text/javascript" language="javascript">
					jQuery(document).ready(function() {
						jQuery("#idsubimg'.$this->boxcode.'").click(function() {
							jQuery("#idfilter'.$this->boxcode.'").toggle();
						});
					});
					</script>';
				$stringtoshow .= '<div class="center hideobject" id="idfilter'.$this->boxcode.'">'; // hideobject is to start hidden
				$stringtoshow .= '<form class="flat formboxfilter" method="POST" action="'.$_SERVER["PHP_SELF"].'">';
				$stringtoshow .= '<input type="hidden" name="token" value="'.newToken().'">';
				$stringtoshow .= '<input type="hidden" name="action" value="'.$refreshaction.'">';
				$stringtoshow .= '<input type="hidden" name="page_y" value="">';
				$stringtoshow .= '<input type="hidden" name="DOL_AUTOSET_COOKIE" value="DOLUSERCOOKIE_box_'.$this->boxcode.':year,shownb,showtot">';
				/*$stringtoshow .= '<input type="checkbox" name="'.$param_shownb.'"'.($shownb ? ' checked' : '').'> '.$langs->trans("NumberOfFilesByMonth");
				$stringtoshow .= ' &nbsp; ';
				$stringtoshow .= '<input type="checkbox" name="'.$param_showtot.'"'.($showtot ? ' checked' : '').'> '.$langs->trans("AmountOfOrdersByMonthHT");
				$stringtoshow .= '<br>';*/
				$stringtoshow .= $langs->trans("Year").' <input class="flat" size="4" type="text" name="'.$param_year.'" value="'.$endyear.'">';
				$stringtoshow .= '<input type="image" class="reposition inline-block valigntextbottom" alt="'.$langs->trans("Refresh").'" src="'.img_picto($langs->trans("Refresh"), 'refresh.png', '', '', 1).'">';
				$stringtoshow .= '</form>';
				$stringtoshow .= '</div>';
				if ($shownb && $showtot)
				{
					$stringtoshow .= '<div class="fichecenter">';
					$stringtoshow .= '<div class="fichehalfleft">';
				}
				if ($shownb) $stringtoshow .= $px1->show();
				if ($shownb && $showtot)
				{
					$stringtoshow .= '</div>';
					$stringtoshow .= '<div class="fichehalfright">';
				}
				if ($showtot) $stringtoshow .= $px2->show();
				if ($shownb && $showtot)
				{
					$stringtoshow .= '</div>';
					$stringtoshow .= '</div>';
				}
				$this->info_box_contents[0][0] = array(
					'tr'=>'class="oddeven nohover"',
					'td' => 'class="nohover center"',
					'textnoformat'=>$stringtoshow,
				);
			} else {
				$this->info_box_contents[0][0] = array(
					'tr'=>'class="oddeven nohover"',
					'td' => 'class="nohover left"',
					'maxlength'=>500,
					'text' => $mesg,
				);
			}
		} else {
			$this->info_box_contents[0][0] = array(
				'td' => 'class="nohover opacitymedium left"',
				'text' => $langs->trans("ReadPermissionNotAllowed")
			);
		}
		
		
		
		//=====================================||||||cases created by Month||||||=====================
		
		
		
		
		
		
	}
	/*{
		global $user, $langs;
		$langs->load("boxes");

		$this->max = 100;//customer required all registers

		include_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		include_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
        $userstatic = new Societe($this->db);

        $this->info_box_head = array('text' => $langs->trans("BoxTitleBirthdaysSoc"));

		if ($user->rights->birthdaysoc->myobject->read)
		{
			$tmparray = dol_getdate(dol_now(), true);

			$sql = "SELECT u.rowid, u.nom, o.nacimiento, u.email";
			$sql .= " FROM ".MAIN_DB_PREFIX."societe as u";
			$sql .= " JOIN ".MAIN_DB_PREFIX."societe_extrafields as o on u.rowid = o.fk_object";
			$sql .= " WHERE u.entity IN (".getEntity('user').")";
			$sql .= " AND u.status = 1";
			$sql .= " AND EXTRACT(MONTH FROM o.nacimiento ) = EXTRACT(MONTH FROM NOW() )";
			$sql .= " AND EXTRACT(DAY FROM o.nacimiento ) = EXTRACT(DAY FROM NOW() )";
			$sql .= " ORDER BY o.nacimiento ASC";
			$sql .= $this->db->plimit($max, 0);
			dol_syslog(get_class($this)."::loadBox", LOG_DEBUG);
			$result = $this->db->query($sql);
			if ($result)
			{
				$num = $this->db->num_rows($result);

				$line = 0;
				while ($line < $num)
				{
					$objp = $this->db->fetch_object($result);
                    $userstatic->id = $objp->rowid;
                    $userstatic->nom = $objp->nom;
                    $userstatic->email = $objp->email;
                    $dateb = $this->db->jdate($objp->nacimiento);
                    $age = date('Y', dol_now()) - date('Y', $dateb);

                    $this->info_box_contents[$line][] = array(
                        'td' => '',
                        'text' => $userstatic->getNomUrl(1),
                        'asis' => 1,
                    );

                    $this->info_box_contents[$line][] = array(
                        'td' => 'class="right"',
                        'text' => dol_print_date($dateb, "day").' - '.$age.' '.$langs->trans('DurationYears')
                    );

                    / *$this->info_box_contents[$line][] = array(
                        'td' => 'class="right" width="18"',
                        'text' => $userstatic->LibStatut($objp->status, 3)
                    );* /

					$line++;
				}

				if ($num == 0) $this->info_box_contents[$line][0] = array('td' => 'class="center opacitymedium"', 'text'=>$langs->trans("None"));

				$this->db->free($result);
			}
			else {
				$this->info_box_contents[0][0] = array(
                    'td' => '',
                    'maxlength'=>500,
                    'text' => ($this->db->error().' sql='.$sql)
                );
			}
		}
		else {
			$this->info_box_contents[0][0] = array(
			    'td' => 'class="nohover opacitymedium left"',
                'text' => $langs->trans("ReadPermissionNotAllowed")
			);
		}
	}*/

	/**
	 * Method to show box. Called by Dolibarr eatch time it wants to display the box.
	 *
	 * @param array $head       Array with properties of box title
	 * @param array $contents   Array with properties of box lines
	 * @param int   $nooutput   No print, only return string
	 * @return void
	 */
	public function showBox($head = null, $contents = null, $nooutput = 0)
	{
		// You may make your own code here…
		// … or use the parent's class function using the provided head and contents templates
		return parent::showBox($this->info_box_head, $this->info_box_contents, $nooutput);
	}
	
}
