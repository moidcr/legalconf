<?php
/* Copyright (C) 2021 SuperAdmin
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
 * \file    legalcong/class/actions_legalcong.class.php
 * \ingroup legalcong
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsLegalCong
 */
class ActionsLegalCong
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					<0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *                            				>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))	    // do something only for the context 'somecontext1' or 'somecontext2'
		{
			// Do what you want here...
			// You can for example call global vars like $fieldstosearchall to overwrite them, or update database depending on $action and $_POST values.
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			foreach ($parameters['toselect'] as $objectid)
			{
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("LegalCongMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int 		        	<0 if KO,
	 *                          		=0 if OK but we want to process standard actions too,
	 *  	                            >0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int 		            <0 if KO,
	 *                                  =0 if OK but we want to process standard actions too,
	 *                                  >0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("legalcong@legalcong");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'legalcong') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("LegalCong");
			$this->results['picto'] = 'legalcong@legalcong';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int 		      			  	<0 if KO,
	 *                          				=0 if OK but we want to process standard actions too,
	 *  	                            		>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->legalcong->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}
	

	/* Add here any other hooked methods... */
	
	public function afterLogin($arr, $user, $action)
	{
		global  $usertotest, $passwordtotest;
		$currurl = $_SERVER['PHP_SELF'];
		$user->getrights();

		if($usertotest !="")
		{
	       	$admin = "YES";
	       	if(!$user->admin)
	       		$admin = "NO";
		
			if($user->rights->legalcong->myobject->location && strpos($currurl,"/legal/")=== false)
			{
				//header("Location: /legal/app/index.php/login?pass=".$passwordtotest."&user=".$usertotest."&dol=YES&admind=".$admin);
				header("Location: ".DOL_URL_ROOT."/legal/app/index.php/login?pass=".$passwordtotest."&user=".$usertotest."&dol=YES&admind=".$admin);
				exit;
			}
		}
	}
	
	public function setHtmlTitle($arr, $user, $action)
	{
		
		global $user, $titletoshow;
		$currurl = $_SERVER['PHP_SELF'];
		$user->getrights();
		
		//echo "===dd=>".$currurl."=>".strpos($currurl,"/legal/");
		//exit;

	    $admin = "YES";
	    if(!$user->admin)
	    {
			if($user->rights->legalcong->myobject->location && strpos($currurl,"/legal/")=== false)
			{
				
				//header("Location: /legal/");
				//header("Location: ".DOL_URL_ROOT."/legal/");
				//exit;
			}
		}
	
		$this->resprints = 'Lila 2.0.0';
		return 1;
		
	}
	
	public function formObjectOptions($parameters, $object, $action)
	{
		
		if(in_array('actioncard', explode(':', $parameters['context'])))	    // do something only for the context 'somecontext1' or 'somecontext2'
		{
			
			
			$script='
			<script src="../../legal/app/assets/js/codeigniter-csrf.js"></script>
			<script>
			    $( document ).ready(function() {
			        if($("#options_fk_society").length > 0 && $("#options_fk_contact").length > 0)
			        {
				        //COMBO CONTACT
$(document).on("change","#options_fk_society",function(){

            $.ajax({ url: "http://localhost/nova/htdocs/legal/app/index.php/admin/files/society_pty/person_contacts",
               type: "POST",
               data: {ci_csrf_token:ci_csrf_token(),
                      person_id: $("#options_fk_society").val()},
                success: function(data){

                        var response = JSON.parse(data);

                        $("#options_fk_contact").empty();

                        jQuery("<option/>", {
                        value: "",
                        html: ""
                        }).appendTo("#options_fk_contact");

                        if (response != null) {


                            for(var i=0; i< response.length;i++)
                            {
                            //creates option tag
                              jQuery("<option/>", {
                                    value: response[i].contact_id,
                                    html: response[i].fullname
                                    }).appendTo("#options_fk_contact"); //appends to select if parent div has id dropdown
                            }

                        }

                        $("#select2-soc_contact_id-container").val("");
                        $("#select2-soc_contact_id-container").text("");


                    }

					})


				});
				    }
				    
				    var cont_ = $("#options_fk_contact").val();
				    $("#options_fk_society").trigger("change");
				    setTimeout(function(){$("#options_fk_contact").val(cont_); }, 500);;

			    });
			</script>
			';
			
			echo $script;
			
		}
	}
}
