<?php defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Bonfire
 *
 * @package   Surveyors
 * @author    Dimsoft Dev Team
 * @copyright Copyright (c) 2008 - 2015, Dimsoft S.A.
 * @link      http://dimpanama.com
 * @since     Version 1.0
 */


/**
 * Metronic Library
 *
 * Provides helper methods for displaying Context Navigation.
 *
 * @package Bonfire\Core\Modules\UI\Libraries\Metronic
 * @author  Dimsoft S.A.
 * @link reference    http://cibonfire.com/docs/developer/contexts
 */
class Metronic
{
    /*
     * Templates and related strings for building the context menus
     */
    //protected static $templateContextNav  = "<ul class='{class}'{extra}>\n{menu}</ul>\n";
    protected static $templateContextNav  = '{menu}';
    
   // protected static $templateContextMenu = "<li class='{parent_class}'><a href='{url}' id='{id}' class='{current_class}' title='{title}'{extra}>{text}</a>{content}</li>\n";
   protected static $templateContextMenu ='<li class="{parent_class}"><a href="#" id="{id}" class="nav-link"><i class="nav-icon {current_class}"></i><p>{text}<i class="right fa fa-angle-left"></i></p></a><ul class="nav nav-treeview">{content}</ul></li>';
   
   
   
    
    //protected static $templateMenu        = "<li><a {extra}href='{url}' title='{title}'>{display}</a>\n</li>\n";
    protected static $templateMenu        = '<li class="nav-item"><a href="{url}" class="nav-link"><i class="fa fa-circle nav-icon"></i><p>{display}</p></a></li>';
    
    //<!--cool-->
    
    protected static $templateSubMenu     = "<li class='{submenu_class}'><a href='{url}'>{display}</a><ul class='{child_class}'>{view}</ul></li>\n";
	
	//protected static $templateSubMenu     = '<li class="{submenu_class}"><a href="{url}" class="nav-link"><i class="fa fa-circle nav-icon"></i><p>{display}</p></a></li>';
	
    protected static $templateContextEnd             = "<span class='caret'></span>";
    protected static $templateContextImage           = "<img src='{image}' alt='{title}' />";
    protected static $templateContextText            = "{title}";
    protected static $templateContextMenuAnchorClass = 'dropdown-toggle';
    protected static $templateContextMenuExtra       = " data-hover='megamenu-dropdown' data-close-others='true' data-toggle='dropdown' data-id='{dataId}_menu' ";
    protected static $templateContextNavMobileClass  = 'mobile_nav';

    /** @var string The class name to attach to the outer ul tag. */
    protected static $outer_class = 'nav navbar-nav';

    /** @var string The class to attach to li tags with children. */
    protected static $parent_class = 'nav-item ';

    /** @var string The class to apply to li tags within ul tags inside. */
    protected static $submenu_class = 'nav-item';

    /** @var string The class to apply to ul tags within li tags. */
    protected static $child_class = 'dropdown-menu pull-left';

    /** @var string The id to apply to the outer ul tag. */
    protected static $outer_id = null;

    /** @var array Stores the available menu actions. */
    protected static $actions = array();

    /** @var object Pointer to the CodeIgniter instance. */
    protected static $ci;

    /** @var array The context menus configuration. */
    protected static $contexts = array();

    /** @var array Any errors which occurred during the Context creation. */
    protected static $errors = array();

    /** @var array Stores the organized menu actions. */
    protected static $menu = array();

    /** @var string[] Contexts which are required. */
    protected static $requiredContexts = array();//array('settings', 'developer');

    /** @var string Admin area to link to or other context. */
    protected static $site_area;

    //--------------------------------------------------------------------------

    /**
     * Get the CI instance and call the init method.
     *
     * @return void
     */
    public function __construct()
    {
        self::$ci =& get_instance();
        self::init();
    }

    /**
     * Load the configured contexts.
     *
     * @return void
     */
    protected static function init()
    {
        self::setContexts(self::$ci->config->item('contexts'), SITE_AREA);
        log_message('debug', 'UI/Contexts library loaded');
    }

    /**
     * Set the contexts array and, optionally, the site area.
     *
     * @param array  Context menus to display, normally stored in application config.
     * @param string Area to link to, if not provided (or null), will remain unchanged.
     *
     * @return void
     */
    public static function setContexts($contexts = array(), $siteArea = null)
    {
        if (empty($contexts) || ! is_array($contexts)) {
            die(lang('bf_no_contexts'));
        }

        // Ensure required contexts exist.
        foreach (self::$requiredContexts as $requiredContext) {
            if (! in_array($requiredContext, $contexts)) {
                $contexts[] = $requiredContext;
            }
        }

        self::$contexts = $contexts;
        if (! is_null($siteArea)) {
            self::$site_area = $siteArea;
        }

        log_message('debug', 'UI/Contexts setContexts has been called.');
    }

    /**
     * Return the context array, just in case it is needed later.
     *
     * @param boolean $landingPageFilter If true, only returns contexts which have
     * a landing page (index.php) available.
     *
     * @return array The names of the contexts.
     */
    public static function getContexts($landingPageFilter = false)
    {
        if (! $landingPageFilter) {
            return self::$contexts;
        }

        $returnContexts = array();
        foreach (self::$contexts as $context) {
            if (file_exists(realpath(VIEWPATH) . '/' . self::$site_area . "/{$context}/index.php")) {
                $returnContexts[] = $context;
            }
        }

        return $returnContexts;
    }

    /**
     * Returns a string of any errors during the create context process.
     *
     * @param string $open  A string to place at the beginning of each error.
     * @param string $close A string to place at the end of each error.
     *
     * @return string All of the current errors with the provided $open/$close strings,
     * with each close string followed by a newline (\n) character.
     */
    public static function errors($open = '<li>', $close = '</li>')
    {
        $out  = '';
        foreach (self::$errors as $error) {
            $out .= "{$open}{$error}{$close}\n";
        }

        return $out;
    }

    /**
     * Renders a list-based menu (with submenus) for each context.
     *
     * @param string $mode            What to output in the top menu ('icon'/'text'/'both').
     * @param string $order_by        The sort order of the elements ('normal'/'reverse'/'asc'/'desc').
     * @param boolean $top_level_only If true, output only the top-level links.
     * @param boolean $benchmark      If true, output benchmark start/end marks.
     *
     * @return string A string with the built navigation.
     */
    public static function render_menu($mode = 'text', $order_by = 'normal', $top_level_only = false, $benchmark = false, $current_user)
    {

        if ($benchmark) {
            self::$ci->benchmark->mark('render_menu_start');
        }

        // As long as the contexts were set with setContexts(), the required contexts
        // should be in place. However, it's still a good idea to make sure an array
        // of contexts was provided.
        $contexts = self::getContexts();
        if (empty($contexts) || ! is_array($contexts)) {
            die(self::$ci->lang->line('bf_no_contexts'));
        }

        // Sorting (top-level menus).
        switch ($order_by) {
            case 'reverse':
                $contexts = array_reverse($contexts);
                break;
            case 'asc':
                natsort($contexts);
                break;
            case 'desc':
                rsort($contexts);
                break;
            case 'normal':
            case 'default':
                
            default:
                break;
        }

        $template = '';
        if ($mode == 'text') {
            $template = self::$templateContextText;
        } else {
            $template = self::$templateContextImage;
            if ($mode == 'both') {
                $template .= self::$templateContextText;
            }
        }
        $template .= self::$templateContextEnd;

        // Build out the navigation.
        $menu = '';
        
        $icons = array("content" => "fa fa-users", "files"=> "fa fa-folder-open", "cases" => "fa fa-list-alt", "reportes" =>"fa fa-line-chart", "settings" => "fa fa-cogs", "developer"=>"");
        //$context
        
        foreach ($contexts as $context) {
            // Don't display an entry in the menu if the user doesn't have permission
            // to view it (unless the permission doesn't exist).
            $viewPermission = 'Site.' . ucfirst($context) . '.View';
            if (self::$ci->auth->has_permission($viewPermission)
                || ! self::$ci->auth->permission_exists($viewPermission)
            ) {
                // The text/image displayed in the top-level context menu.
                $title    = self::$ci->lang->line("bf_context_{$context}");
                $navTitle = str_replace(
                    array('{title}', '{image}'),
                    array(
                        $title,
                        $mode == 'text' ? '' : Template::theme_url("images/context_{$context}.png"),
                    ),
                    $template
                );

                // Build the menu for this context.
                $menu .= str_replace(
                    array('{parent_class}', '{url}', '{id}', '{current_class}', '{title}', '{extra}', '{text}', '{content}'),
                    array(
                        self::$parent_class . ' ' . check_class($context, true),
                        site_url(self::$site_area . "/{$context}"),
                        "tb_{$context}",
                        $icons[$context], 
                        $title,
                        str_replace('{dataId}', $context, self::$templateContextMenuExtra),
                        $navTitle,
                        $top_level_only ? '' : self::context_nav_custom($context),
                    ),
                    self::$templateContextMenu
                );

                if($context == "files"){//expedientes

                }

                //var_dump("tb_{$context}");die();
            }
        }
       

        // Put the generated menu into the context nav template.
        $nav = str_replace(
            array('{class}', '{extra}', '{menu}'),
            array(
                self::$outer_class,
                trim(self::$outer_id) == '' ? '' : ' id="' . self::$outer_id . '"',
                $menu,
            ),
            self::$templateContextNav
        );

        if ($benchmark) {
            self::$ci->benchmark->mark('render_menu_end');
        }
        
        
         
        $origen_user = get_origen();
        $drop = "";
        foreach (origenes_trust() as $key => $origen) {

                            
         $drop.=' <a href="'.site_url("ajax/content/set_origen/$key").'" class="dropdown-item">
            <!-- Message Start -->
            
            <div class="media">
        
              <div class="media-body">
            
	             <div class="card_boton '.($origen_user==$key? 'active_card':'').'">
	                <img width="100%" src="'.base_url('assets/images/logo_'.$key.'.png').'"> 
	             </div>
	          </div>
	        </div>
          </a>
          <div class="dropdown-divider"></div>';

          }
          $userDisplayName = isset($current_user->display_name) && ! empty($current_user->display_name) ? $current_user->display_name : ($current_user->username ? $current_user->username : $current_user->email);
        
     $nav='<nav class="main-header navbar navbar-expand navbar-dark navbar-primary">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fa fa-bars"></i></a>
      </li>
     <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>-->
    </ul>

	 <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
    
    <!-- Navbar Search -->
      <li class="nav-item">
        <a class="nav-link" data-widget="navbar-search" href="#" role="button">
          <i class="fa fa-search"></i>
        </a>
        <div class="navbar-search-block">
          <form class="form-inline">
            <div class="input-group input-group-sm">
              <input class="form-control form-control-navbar" type="search" placeholder="Búsqueda General" aria-label="Search">
              <div class="input-group-append">
                <button class="btn btn-navbar" type="submit">
                  <i class="fa fa-search"></i>
                </button>
                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                  <i class="fa fa-times"></i>
                </button>
              </div>
            </div>
          </form>
        </div>
      </li>
      
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="fa fa-institution"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">'.$drop.'
        
        </div>
      </li>
      
     
    
    <li class="nav-item">
        <a class="nav-link"  data-slide="true" href="'.site_url("logout").'" role="button">
          <i class="fa fa-sign-out"></i>
        </a>
      </li>
      
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="'.base_url().'" class="brand-link">
      <img src="'.base_url().'assets/images/Logo_Legal.png" alt="Legal" class="brand-image img-circle elevation-2" >
      <span class="brand-text font-weight-light"><img src="'.base_url().'assets/images/title_assets.png" width="200"  alt="Legal"  ></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="'.base_url().'assets/images/icon-user.png" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><p>'.$userDisplayName.'</p></a>
        </div>
      </div>
      
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
      '.$nav;
      
      
      
        
     $nav.='
     </ul>
      </nav>
     <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>';
        

        return $nav;
    }

    public static function context_nav_custom($context = null, $class = 'dropdown-menu', $ignore_ul = false)
    {
        # code...
        $origen = get_origen();
        //echo $origen."<==";
		//if(!$origen)$origen="ATC";
        switch ($context) {
            case 'files':
                # code...
                $html = "";
                
                //'<li class="nav-item"><a href="{url}" class="nav-link"><i class="fa fa-circle nav-icon"></i><p>{display}</p></a></li>';
                
                /*if($origen=="ATC"){
                    $html = '
                        <li class="nav-item"><a href="#" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Persona Natural</p></a>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Sociedades en Panamá</p>
                        <i class="right fa fa-angle-left"></i>
                        </a>
                        <ul class="nav nav-treeview">
		              <li class="nav-item">
		                <a id="lnkGroups" href="#" class="nav-link">
		                 <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Sociedades Anónimas</p>
		                </a>
		              </li>
		              <li class="nav-item">
		                <a href="#" class="nav-link">
		                  <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Sociedades de Responsabilidad Limitada</p>
		                </a>
		              </li>
		              <li class="nav-item">
		                <a href="#" class="nav-link">
		                 <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Sociedad Civil</p>
		                </a>
		              </li>
		              <li class="nav-item">
		                <a href="#" class="nav-link">
		                  <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Sucursal de Sociedad Extranjera</p>
		                </a>
		              </li>
		              <li class="nav-item">
		                <a href="#" class="nav-link">
		                  <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Propiedad Horizontal</p>
		                </a>
		              </li>
		              <li class="nav-item">
		                <a href="#" class="nav-link">
		                  <i class="fa fa-circle-o nav-icon"></i>
		                  <p>Naves</p>
		                </a>
		              </li>
				  </ul>
                        </li>
                         <li class="nav-item"><a href="#"  class="nav-link"title="Fundaciones"><i class="fa fa-circle nav-icon"></i><p>Sociedades Extranjeras</p></a>
                        </li >
                         <li class="nav-item"><a href="#" class="nav-link" title="Depositos Escrow"><i class="fa fa-circle nav-icon"></i><p>Fundaciones de Interés Privado</p></a>
                        </li>
                        
                    ';
                }*/
                
                if($origen=="ATC"){
                    $html = '
                        <li class="nav-item"><a href="'.site_url('admin/content/natural_person').'" class="nav-link" title="Persona Natural"><i class="fa fa-circle nav-icon"></i><p>Persona Natural</p></a>
                        </li>
                        <li class="nav-item"><!--<a href="http://201.218.125.250/legal/app/index.php/admin/files/society_pty" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Sociedades en Panamá</p>
                       
                        </a>-->
                        <a href="'.site_url('admin/files/society_pty').'" class="nav-link" title="Sociedades en Panamá"><i class="fa fa-circle nav-icon"></i><p>Sociedades en Panamá</p>
                       
                        </a>
                        </li>
                         <li class="nav-item"><a href="#"  class="nav-link"title="Sociedades Extranjeras"><i class="fa fa-circle nav-icon"></i><p>Sociedades Extranjeras</p></a>
                        </li >
                         <li class="nav-item"><a href="#" class="nav-link" title="Fundaciones de Interés Privado"><i class="fa fa-circle nav-icon"></i><p>Fundaciones de Interés Privado</p></a>
                        </li>
                        
                    ';
                }
                
                if($origen=="PMA"){
                    $html = '
                        <li class="nav-item"><a href="#" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Fideicomiso</p></a>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link" title="Contratos de Escrow"><i class="fa fa-circle nav-icon"></i><p>Contratos de Escrow</p></a>
                        </li>
                         <li class="nav-item"><a href="#"  class="nav-link"title="Fundaciones"><i class="fa fa-circle nav-icon"></i><p>Fundaciones de interés Privado</p></a>
                        </li >
                    ';
                }
                
                if($origen=="BVI"){
                    $html = '
                        <li class="nav-item"><a href="#" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Servicios Fiduciarios</p></a>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Sociedades</p></a>
                        </li>';
                }
                
                if($origen=="LTD"){
                    $html = '
                        <li class="nav-item"><a href="#" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Servicios Fiduciarios</p></a>
                        </li>
                        <li class="nav-item"><a href="#" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Sociedades</p></a>
                        </li>';
                }
                
                /*if($origen=="PMA" || $origen=="ATC" || $origen=="LTD" ){
                    $html = '
                        <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/society_pty" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Sociedades</p></a>
                        </li>
                        <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/escrow_pty" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Fideicomisos</p></a>
                        </li>
                         <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/foundation"  class="nav-link"title="Fundaciones"><i class="fa fa-circle nav-icon"></i><p>Fundaciones</p></a>
                        </li >
                         <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/deposit_escrow" class="nav-link" title="Depositos Escrow"><i class="fa fa-circle nav-icon"></i><p>Depositos Escrow</p></a>
                        </li>
                        
                    ';
                }

                if($origen=="BVI"){
                    $html = '
                        <li  class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/society_foreign" class="nav-link" title="Sociedades"><i class="fa fa-circle nav-icon"></i><p>Sociedades</p></a>
                        </li>
                        <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/escrow_foreign" class="nav-link" title="Fideicomisos"><i class="fa fa-circle nav-icon"></i><p>Fideicomisos</p></a>
                        </li>
                         <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/foundation" class="nav-link" title="Fundaciones"><i class="fa fa-circle nav-icon"></i><p>Fundaciones</p></a>
                        </li>
                         <li class="nav-item"><a href="http://190.35.135.42/trust/public/index.php/admin/files/deposit_escrow" class="nav-link" title="Depositos Escrow"><i class="fa fa-circle nav-icon"></i><p>Depositos Escrow</p></a>
                        </li>
                        
                    ';
                }*/
                if($html==""){
                    return self::context_nav($context);
                }else{
                    return $html;

                }

                break;
            
            default:
                return self::context_nav($context);
                break;
        }
        
    }
    /**
     * Create the mobile navigation.
     *
     * The tab-style mobile navigation is made up of a series of divs, each of which
     * contains a list of links within that context.
     *
     * @return string The navigation lists.
     */
    public static function render_mobile_navs()
    {
        $out = '';
        foreach (self::getContexts() as $context) {
            $contextNav = self::context_nav($context, '', true);
            $currentId  = " id='{$context}_menu'";
            $out .= str_replace(
                array('{class}', '{extra}', '{menu}'),
                array(self::$templateContextNavMobileClass, $currentId, $contextNav),
                self::$templateContextNav
            );
        }

        return $out;
    }

    /**
     * Build the main navigation menu for each context.
     *
     * @param string  $context   The context of the nav to be built.
     * @param string  $class     The class to use on the nav.
     * @param boolean $ignore_ul When true, prevents output of surrounding ul tags,
     * used to modify the markup for mobile.
     *
     * @return string The HTML necessary to display the menu.
     */
    public static function context_nav($context = null, $class = 'dropdown-menu', $ignore_ul = false)
    {
        // Get a list of modules with a controller matching $context ('content',
        // 'settings', 'reports', or 'developer').
        foreach (Modules::list_modules() as $module) {
            if (Modules::controller_exists($context, $module)) {
                $mod_config = Modules::config($module);
                if(isset($mod_config['visible']) and $mod_config['visible']) {
                    self::$actions[$module] = array(
                        'display_name' => isset($mod_config['name']) ? $mod_config['name'] : $module,
                        'menus'        => isset($mod_config['menus']) ? $mod_config['menus'] : false,
                        'title'        => isset($mod_config['description']) ? $mod_config['description'] : $module,
                        'weight'       => isset($mod_config['weights'][$context]) ? $mod_config['weights'][$context] : 0,
                        'visible'      => isset($mod_config['visible']) ? $mod_config['visible'] : '',
                        'order'        => isset($mod_config['order']) ? $mod_config['order'] : '',
                    );

                    // This is outside the array because the else portion uses the
                    // 'display_name' value,
                    self::$actions[$module]['menu_topic'] = isset($mod_config['menu_topic']) ?
                        $mod_config['menu_topic'] : self::$actions[$module]['display_name'];
                }
            }
        }

        // Are there any actions?
        if (empty(self::$actions)) {
            return str_replace(
                array('{class}', '{extra}', '{menu}'),
                array($class, '', ''),
                self::$templateContextNav
            );
        }

        // Order the actions by weight, then alphabetically.
        self::sortActions();

        // Build up the menu array.
        $ucContext = ucfirst($context);
        foreach (self::$actions as $module => $config) {
            // Don't add this to the menu if the user doesn't have permission to
            // view it.
            if (self::$ci->auth->has_permission('Bonfire.' . ucfirst($module) . '.View')
                || self::$ci->auth->has_permission(ucfirst($module) . ".{$ucContext}.View")
            ) {
                // Drop-down menus?
                $menu_topic = is_array($config['menu_topic']) && isset($config['menu_topic'][$context]) ?
                    $config['menu_topic'][$context] : $config['display_name'];

                self::$menu[$menu_topic][$module] = array(
                    'display_name' => $config['display_name'],
                    'title'        => $config['title'],
                    'menu_topic'   => $menu_topic,
                    'menu_view'    => $config['menus'] && isset($config['menus'][$context]) ? $config['menus'][$context] : '',
                );
            }
        }

        // Add any sub-menus and reset the $actions array for the next pass.
        $menu = self::build_sub_menu($context, $ignore_ul);
        self::$actions = array();

        return $menu;
    }

    //--------------------------------------------------------------------------
    // !BUILDER METHODS
    //--------------------------------------------------------------------------

    /**
     * Create everything needed for a new context to run.
     *
     * This includes creating permissions, assigning them to certain roles, and
     * creating an application migration for the permissions.
     *
     * @todo Create the migration file if $migrate is true...
     *
     * @param string  $name    The name of the context to create.
     * @param array   $roles   The roles (names or IDs) which should have permission
     * to view this module.
     * @param boolean $migrate If true, will create a migration file.
     *
     * @return boolean False on error, else true.
     */
    public static function create_context($name = '', $roles = array(), $migrate = false)
    {
        if (empty($name)) {
            self::$errors = lang('ui_no_context_name');
            return false;
        }

        // Write the context name to the config file.

        self::$ci->load->helper('config_file');

        $contexts  = self::getContexts();
        $lowerName = strtolower($name);

        // Add the context if it is not already in the list of contexts.
        if (! in_array($lowerName, $contexts)) {
            array_unshift($contexts, $lowerName);

            if (! write_config('application', array('contexts' => $contexts), null)) {
                self::$errors[] = lang('ui_cant_write_config');
                return false;
            }
        }

        // Create an entry in the application_lang file for the context.

        if (! function_exists('addLanguageLine')) {
            self::$ci->load->helper('translate/languages');
        }

        $temp = addLanguageLine('application_lang.php', array("bf_context_{$lowerName}" => $name), 'english');
        if (! $temp) {
            // @todo set error/return if the language line was not added successfully?
        }

        // Create the relevant permissions.

        $cname = 'Site.' . ucfirst($name) . '.View';

        // Get the permission ID, either from an existing permission or by inserting
        // a new permission.
        self::$ci->load->model('permissions/permission_model');
        if (self::$ci->permission_model->permission_exists($cname)) {
            $pid = self::$ci->permission_model->find_by('name', $cname)->permission_id;
        } else {
            $pid = self::$ci->permission_model->insert(
                array(
                    'name'        => $cname,
                    'description' => 'Allow user to view the ' . ucwords($name) . ' Context.',
                )
            );
        }

        // Assign the permission to the supplied roles.

        // If no roles were supplied, exit, indicating success.
        if (empty($roles)) {
            return true;
        }

        // Assign the permission to each role.
        self::$ci->load->model('roles/role_permission_model');
        foreach ($roles as $role) {
            if (is_numeric($role)) {
                // Assign By Id.
                self::$ci->role_permission_model->delete($role, $pid);
                self::$ci->role_permission_model->create($role, $pid);
            } else {
                // Assign By Name.
                self::$ci->role_permission_model->assign_to_role($role, $cname);
            }
        }

        // if ($migrate) {
        //  @todo create a migration file.
        // }

        return true;
    }

    //--------------------------------------------------------------------------
    // !UTILITY METHODS
    //--------------------------------------------------------------------------

    /**
     * Take an array of key/value pairs and set the class/id names.
     *
     * @param array $attrs An array of key/value pairs that correspond to the class
     * methods for classes and ids.
     *
     * @return void
     */
    public static function set_attrs($attrs = array())
    {
        if (empty($attrs) || ! is_array($attrs)) {
            return null;
        }

        foreach ($attrs as $attr => $value) {
            if (isset(self::$attr)) {
                self::$attr = $value;
            }
        }
    }

    /**
     * Build out the HTML for the menu.
     *
     * @param string  $context   The context to build the nav for.
     * @param boolean $ignore_ul If true, the list will be returned without being
     * placed into the template.
     *
     * @return string HTML for the sub menu.
     */
    public static function build_sub_menu($context, $ignore_ul = false)
    {
        $search = array('{submenu_class}', '{url}', '{display}', '{child_class}', '{view}');
        $list   = '';
        foreach (self::$menu as $topic_name => $topic) {
            if (count($topic) <= 1) {
                foreach ($topic as $module => $vals) {
                    $list .= self::buildItem(
                        $module,
                        $vals['title'],
                        $vals['display_name'],
                        $context,
                        $vals['menu_view']
                    );
                }
            } else {
                // If there is more than one item in the topic, build out a menu
                // based on the multiple items.
                $subMenu = '';
                foreach ($topic as $module => $vals) {
                    if (empty($vals['menu_view'])) {
                        // If it has no sub-menu, add the item.
                        $subMenu .= self::buildItem(
                            $module,
                            $vals['title'],
                            $vals['display_name'],
                            $context,
                            $vals['menu_view']
                        );
                    } else {
                        // Otherwise, echo out the sub-menu only. To maintain backwards
                        // compatility, strip out any <ul> tags.
                        $subMenu .= str_ireplace(
                            array('<ul>', '</ul>'),
                            array('', ''),
                            self::$ci->load->view($vals['menu_view'], null, true)
                        );
                    }
                }

                // Handle localization of the topic name, if needed.
                if (strpos($topic_name, 'lang:') === 0) {
                    $topic_name = self::$ci->lang->line(str_replace('lang:', '', $topic_name));
                }

                $list .= str_replace(
                    $search,
                    array(
                        self::$submenu_class,
                        '#',
                        ucwords($topic_name),
                        self::$child_class,
                        $subMenu,
                    ),
                    self::$templateSubMenu
                );
            }
        }

        self::$menu = array();

        if ($ignore_ul) {
            return $list;
        }

        return str_replace(
            array('{class}', '{extra}', '{menu}'),
            array(self::$child_class, '', $list),
            self::$templateContextNav
        );
    }

    /**
     * Build an individual list item (with sub-menus) for the menu.
     *
     * @param string $module       The name of the module this link belongs to.
     * @param string $title        The title used on the link.
     * @param string $display_name The name to display in the menu.
     * @param string $context      The name of the context.
     * @param string $menu_view    The name of the view file that contains the sub-menu.
     *
     * @return string The HTML necessary for a single item and its sub-menus.
     */
    private static function buildItem($module, $title, $display_name, $context, $menu_view = '')
    {
        // Handle localization of the display name, if needed.
        if (strpos($display_name, 'lang:') === 0) {
            $display_name = lang(str_replace('lang:', '', $display_name));
        }
        $displayName = ucwords(str_replace('_', '', $display_name));
		
		//echo base_url(self::$site_area . "/{$context}/{$module}");
        if (empty($menu_view)) {
	        //$url = str_replace('/public', '',site_url(self::$site_area . "/{$context}/{$module}"));
	        if(in_array($context, array('cases', 'reportes')))
	        	$url = '#';
	        else
		        $url = site_url(self::$site_area . "/{$context}/{$module}");
            return str_replace(
                array('{extra}', '{url}', '{title}', '{display}'),
                array(
                    $module == self::$ci->uri->segment(3) ? 'class="active" ' : '',
                    $url,
                    $title,
                    $displayName,
                ),
                self::$templateMenu
            );
        }

        // Sub Menus?. Only works if it's a valid view…
        return str_replace(
            array('{submenu_class}', '{url}', '{display}', '{child_class}', '{view}'),
            array(
                self::$submenu_class,
                '#',
                $displayName,
                self::$child_class,
                str_ireplace(
                    array('<ul>', '</ul>'),
                    array('', ''),
                    self::$ci->load->view($menu_view, null, true)
                ), // To maintain backwards compatility, strip out any <ul> tags
            ),
            self::$templateSubMenu
        );
    }

    /**
     * Sort the actions array.
     *
     * @return void
     */
    private static function sortActions()
    {
        $weights       = array();
        $display_names = array();

        foreach (self::$actions as $key => $action) {
            $weights[$key]       = $action['order'];
            $display_names[$key] = $action['display_name'];
        }
         
       
        array_multisort($weights, SORT_NUMERIC, $display_names, SORT_ASC, self::$actions);
    }
}
