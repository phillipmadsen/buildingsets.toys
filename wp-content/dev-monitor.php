<?php
/*
Plugin Name: OnoDev - Development Monitor
Plugin URI: onodev.com/dev-monitor
Description: Monitor Scripts, Styles, Queries, and more during Development
Version: 1.1.0
Author: Tom Williams
Author URI: onodev.com
License: GPLv3

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'OnoDev_DevMonitor' ) ) :

class OnoDev_DevMonitor {
  public $DevMonitorOptions = array();
  
  public function __construct() {
    $this->DevMonitorOptions = array(
        'Queries',
        'Time',
        'Memory',
        'Files',
        'Javascripts',
        'Styles',
		'Widgets',
		'Conditionals'
      );
	
	add_action('admin_init',array(&$this,'DevMonitorOptions_init'));
	add_action('admin_menu', array(&$this,'DevMonitorOptions_add_page'));
	add_action( 'admin_bar_menu', array(&$this,'DevMonitor_admin_bar'), 999 );
	add_action('wp_head', array(&$this,'submenu_overflow_css'));
	add_action('admin_head', array(&$this,'submenu_overflow_css'));
	
  }
  
	public function submenu_overflow_css() {
		echo '<style type="text/css">
			.ono-dev-dev-monitor-table td {
				padding:10px !important;
				vertical-align: top;
			}
			.ono-dev-dev-monitor-overflow {
				max-height: 500px !important;
				width:500px !important;
				padding:5px !important;
				overflow-y: scroll !important;
			}
			.ono-dev-dev-monitor-overflow ul li
			{
				padding:5px !important;
				margin:0px 3px !important;
				list-style:initial !important;
				
			}
			.onodev-info, .onodev-success, .onodev-warning, .onodev-error, .onodev-validation {
			border: 1px solid;
			margin: 10px 0px;
			padding:15px 10px 15px 50px;
			background-repeat: no-repeat;
			background-position: 10px center;
			}
			.onodev-info {
			color: #00529B;
			background-color: #BDE5F8;
			}
			.onodev-success {
			color: #4F8A10;
			background-color: #DFF2BF;
			}
			.onodev-warning {
			color: #9F6000;
			background-color: #FEEFB3;
			}
			.onodev-error {
			color: #D8000C;
			background-color: #FFBABA;
			}
        </style>';
	}

  
	public function cleanStr($string) {
		//Lower case everything
		$string = strtolower($string);
		//Make alphanumeric (removes all other characters)
		$string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
		//Clean up multiple dashes or whitespaces
		$string = preg_replace("/[\s-]+/", " ", $string);
		//Convert whitespaces and underscore to dash
		$string = preg_replace("/[\s_]/", "-", $string);
		return $string;
	}

	public function DevMonitorOptions_add_page() {
		add_options_page('Dev Monitor', 'Dev Monitor', 'manage_options', 'devmonitor_options_group', array(&$this,'DevMonitorOptions_page'));
	}
  
	public function DevMonitorOptions_init(){
		register_setting( 'devmonitor_options_group', 'devmonitor_options', array(&$this,'DevMonitorOptions_validate') );
	}
  
	public function DevMonitorOptions_page() {
    ?>
		<div class="wrap">
			<h2>Dev Monitor Options</h2>
			<h3>Choose which monitoring tools you would like to display.</h3>
			<?php
			if (defined('SAVEQUERIES') && SAVEQUERIES === true)
				echo '<div class="onodev-success">SAVEQUERIES is enabled. Queries should display.</div>';
			else
				echo '<div class="onodev-warning">SAVEQUERIES is not enabled. Please add the following line to your wp-config.php if you want to view queries:<br><pre><code>define(\'SAVEQUERIES\',true);</code></pre></div>';
			?>
			<form method="post" action="options.php">
				<?php settings_fields('devmonitor_options_group'); ?>
				<?php $options = get_option('devmonitor_options');
				?>
				
				<table class="form-table">
				  <?php
					foreach ($this->DevMonitorOptions as $opt)
					{
					  echo '<tr valign="top"><th scope="row">' . $opt . '</th>';
					  echo '<td><input name="devmonitor_options[' . $this->cleanStr($opt) . ']" type="checkbox" value="1" ';
					  if (isset($options[$this->cleanStr($opt)]))
						  checked('1', $options[$this->cleanStr($opt)]);
					  echo ' /></td></tr>';
					  
					}
				?>
					
				</table>
				<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		</div>
	<?php
	}

	public function DevMonitorOptions_validate($input) {
    
		foreach ($this->DevMonitorOptions as $opt)
		{
			if (isset($input[$this->cleanStr($opt)]))
				$input[$this->cleanStr($opt)] = ( $input[$this->cleanStr($opt)] == 1 ? 1 : 0 );
		}
		return $input;
	}
  
	private function DevMonitor_sortQueriesByTime($a,$b)
	{
		if ($a[1]==$b[1]) return 0;
		return ($a[1]<$b[1])?1:-1;
	}
	
	private function DevMonitor_checkQueryPath($str){
		if (strpos($str,'theme') > 0 || strpos($str,'plugin') > 0)
			return 1;
		else
			return 0;
	}
 
	private function DevMonitor_do_queries(){
		global $wpdb,$wp_admin_bar;
		
		if (defined('SAVEQUERIES') && SAVEQUERIES===true) {
			
			$queryStr = '<ul>';

			$qArray = $wpdb->queries;
			usort($qArray,array('OnoDev_DevMonitor','DevMonitor_sortQueriesByTime'));
			$c=1;
			$totalTime = 0;
			foreach($qArray as $q)
			{
				$totalTime += $q[1];
				$queryStr .= "<li>$c.<br><strong>Time</strong>: ".round($q[1]*1000,2) . " ms<br>";
				$queryStr .= "<strong>Query</strong>: $q[0] <br>";
				$func = $q[2];
				if (!$this->DevMonitor_checkQueryPath($func))
				{
					$func = preg_replace('/require\(\'.*?\'\)\,/','require,',$func);
					$func = preg_replace('/require_once\(\'.*?\'\)\,/','require_once,',$func);
					$func = preg_replace('/include\(\'.*?\'\)\,/','require_once,',$func);
				}
				$queryStr .= "<strong>Function</strong>: " . $func."<hr></li>";
				$c++;
			}
			
			$queryStr .= '</ul>';
			
			$menu = array(
				  'parent' => 'dev_monitor',
				  'id' => 'dev_monitor_query',
				  'title' => 'Queries (' . round($totalTime*1000,2) . ' ms, '. 100 * round($totalTime / timer_stop( 0, 3 ),2) .'%)',
			);
	
			$querySubList = array(
				  'parent' => 'dev_monitor_query',
				  'id' => 'dev_monitor_query_list',
				  'title' => '--Query--',
				  'meta'=>array(
					'html'=>$queryStr,
					'class' => 'ono-dev-dev-monitor-overflow'
				  )
			);
			$wp_admin_bar->add_menu($menu);
			$wp_admin_bar->add_menu($querySubList);
			
		}
		
		return get_num_queries() . ' Qs';
	}
  
	private function DevMonitor_do_time(){
		return round(timer_stop( 0, 3 ),2) . ' sec';
	}
  
	private function DevMonitor_do_memory(){
		return  round(memory_get_peak_usage() / 1024 / 1024,2) . ' MB';
	}
  
	private function DevMonitor_do_files(){
		global $wp_admin_bar;
		$included_files = get_included_files();
		$themeCnt = 0;
		$pluginCnt=0;
		$themeStr = '<ul>';
		$pluginStr = '<ul>';
		$plugin_menu_list = array();
		preg_match('/(.*)\/(.*?)$/',rtrim(str_replace( '\\', '/',get_stylesheet_directory()),'/'),$matches);
		$theme_url = $matches[1];
		preg_match('/(.*)\/(.*?)$/',rtrim(str_replace( '\\', '/',dirname( __FILE__ )),'/'),$matches);
		$plugin_url = $matches[1];
		foreach ( $included_files as $key => $path ) {
			$path = str_replace( '\\', '/', $path );
			$theme_dir_pos = strpos( $path, $theme_url );
			$plugin_dir_pos = strpos( $path, $plugin_url );
			if ($theme_dir_pos !== false)
			{
				$themeCnt++;
				$themeStr .= '<li>'.$themeCnt.'. '.substr($path,$theme_dir_pos+strlen($theme_url)).'</li>';
			}
			elseif ($plugin_dir_pos !== false)
			{
				$subPath = ltrim(substr($path,$plugin_dir_pos+strlen($plugin_url)),'/');
				$plugin_name = substr($subPath,0,strpos($subPath,'/'));
				if (isset($plugin_menu_list[$plugin_name]))
					$plugin_menu_list[$plugin_name]['cnt']++;
				else
				{
					$plugin_menu_list[$plugin_name]['cnt'] = 1;
					$plugin_menu_list[$plugin_name]['str']='';
					$plugin_menu_list[$plugin_name]['path'] = $subPath;
				}
				$pluginCnt++;
				$plugin_menu_list[$plugin_name]['str'] .= '<li>'.$plugin_menu_list[$plugin_name]['cnt'].'. '.$subPath.'</li>';	
			}
		}
    
		$themeStr .= '</ul>';
		$pluginStr .= '</ul>';
		$menu = array(
			  'parent' => 'dev_monitor',
			  'id' => 'dev_monitor_php',
			  'title' => 'PHP Files',
		);
		$themeSub = array(
			  'parent' => 'dev_monitor_php',
			  'id' => 'dev_monitor_php_theme',
			  'title' => 'Theme ('.$themeCnt.')',
		);
		$themeSubList = array(
			  'parent' => 'dev_monitor_php_theme',
			  'id' => 'dev_monitor_php_theme_list',
			  'title' => '--Path--',
			  'meta'=>array(
				'html'=>$themeStr,
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
		
		
		$pluginSub = array(
			  'parent' => 'dev_monitor_php',
			  'id' => 'dev_monitor_php_plugin',
			  'title' => 'Plugin ('.$pluginCnt.')',
		);
		
		$wp_admin_bar->add_menu($menu);
		$wp_admin_bar->add_menu($themeSub);
		$wp_admin_bar->add_menu($themeSubList);
		$wp_admin_bar->add_menu($pluginSub);
		
		foreach($plugin_menu_list as $p=>$pValues)
		{
			$pluginCnt++;
			
			$wp_admin_bar->add_menu(
				array(
						'parent' => 'dev_monitor_php_plugin',
						'id' => 'dev_monitor_php_plugin_'.$p,
						'title' => $p ." (".$pValues['cnt'].")",
				)
			);
			$wp_admin_bar->add_menu(
				array(
						'parent' => 'dev_monitor_php_plugin_'.$p,
						'id' => 'dev_monitor_php_plugin_'.$p.'_list',
						'title' => '--Path--',
						'meta'=>array(
						  'html'=>'<ul>'.$pValues['str'].'</ul>',
						  'class' => 'ono-dev-dev-monitor-overflow'
						)
				)
				
			);
		
		}

		return ($pluginCnt+$themeCnt) . ' PHP';
	  }
  
	private function get_real_src($src)
	{
		if (file_exists(ABSPATH.$src))
			return ABSPATH.$src;
		$src2 = preg_replace('#'.site_url().'#', '', $src);
		if (file_exists(ABSPATH.$src2))
			return ABSPATH.$src2;
		$src3 = preg_replace('#^https?://#', '', $src);
		if (file_exists(ABSPATH.$src3))
			return ABSPATH.$src3;
		$url_piece = preg_replace('#^https?:#', '', site_url());
		$src4= preg_replace('#^'.$url_piece.'#', '', $src);
		if (file_exists(ABSPATH.$src4))
			return ABSPATH.$src4;
		
		return false;
	}
  
	private function DevMonitor_do_javascripts(){
		global $wp_scripts,$wp_admin_bar;
		$ret = '<ul>';
		$ct = 0;
		$script_list = $wp_scripts->registered;
		$total_size = 0;
		foreach( $wp_scripts->done as $handle ) :
			$ct++;
			$urlOpen = '';
				$urlClose = '';
			if( class_exists( 'OnoDev_ScriptManager' ) ) {
				$urlOpen = '<a href="'.get_admin_url() . 'options-general.php?page=scriptmanager-js&hndl='.$handle.'" title="'.$script_list[$handle]->src.'">';
				$urlClose = '</a>';
			}
			$src = $this->get_real_src($script_list[$handle]->src);
			
			if ($src)
			{
				$size= round(filesize($src) / 1024,2);
				$total_size += $size;
				$size = ' ('.$size. ' KB)';
				
			}
			else $size='';
			
			$ret .= "<li>".$urlOpen."$ct. $handle".$size.$urlClose."</li>";
		endforeach;
		$ret .= '</ul>';
		$menu = array(
			  'parent' => 'dev_monitor',
			  'id' => 'dev_monitor_js',
			  'title' => 'Javascripts (' . $total_size . ' KB)',
		);
		$sub = array(
			  'parent' => 'dev_monitor_js',
			  'id' => 'dev_monitor_js_list',
			  'title' => '--Handle--',
			  'meta'=>array(
				'html'=>$ret,
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
    
		$wp_admin_bar->add_menu($menu);
		$wp_admin_bar->add_menu($sub);
		
		return $ct . ' JS';
	}
  
	private function DevMonitor_do_styles(){
		global $wp_styles,$wp_admin_bar;
		
		$ret = '<ul>';
		$ct = 0;
		$style_list = $wp_styles->registered;
		$total_size = 0;
		foreach( $wp_styles->queue as $handle ) :
			$ct++;
			$urlOpen = '';
			$urlClose = '';
			if( class_exists( 'OnoDev_ScriptManager' ) ) {
				$urlOpen = '<a href="'.get_admin_url() . 'options-general.php?page=scriptmanager-css&hndl='.$handle.'" title="'.$style_list[$handle]->src.'">';
				$urlClose = '</a>';
			}
			$src = $this->get_real_src($style_list[$handle]->src);
			
			if ($src)
			{
				$size= round(filesize($src) / 1024,2);
				$total_size += $size;
				$size = ' ('.$size. ' KB)';
				
			}
			else $size='';

			$ret .= "<li>".$urlOpen."$ct. $handle".$size.$urlClose."</li>";			
		endforeach;
		$ret .= '</ul>';
		$menu = array(
			  'parent' => 'dev_monitor',
			  'id' => 'dev_monitor_css',
			  'title' => 'Styles ('.$total_size.' KB)',
		);
		$sub = array(
			  'parent' => 'dev_monitor_css',
			  'id' => 'dev_monitor_css_list',
			  'title' => '--Handle--',
			  'meta'=>array(
				'html'=>$ret,
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
		
		
		$wp_admin_bar->add_menu($menu);
		$wp_admin_bar->add_menu($sub);
		return $ct . ' CSS';
	}
  
	public function DevMonitor_admin_bar(){
		global $wp_admin_bar, $wpdb, $current_user;
		get_currentuserinfo();
		if ( !current_user_can('manage_options') || !is_admin_bar_showing() )
			return;
    
		$options = get_option('devmonitor_options');
		if (!$options)
			$options = array();
		$str = '';
		foreach($options as $opt=>$optVal)
		{
			if($optVal === 1)
			{
				$func = 'DevMonitor_do_'.$opt;
				if (method_exists($this,$func))
					$str.= $this->$func() . ' | ';
			}
			$str = rtrim($str, "|");
		}
		$str = rtrim($str, " | ");
		$wp_admin_bar->add_menu( array( 'id' => 'dev_monitor', 'title' => $str, 'href' => FALSE ) );
  
	}
	
	public function DevMonitor_do_widgets() {
		global $wp_widget_factory,$wp_admin_bar;
		$active=0;
		$activeStr='';
		$inactiveStr='';
		$inactive=0;
		foreach($wp_widget_factory->widgets as $handle=>$w)
		{
			if ( is_active_widget( false, false,$w->id_base,true) ) 
			{
				$active++;
				$activeStr .= '<li>'.$active.'. '.$w->name.'</li>';
			}
			else
			{
				$inactive++;
				$inactiveStr .= '<li>'.$inactive.'. '.$w->name.'</li>';
			}
		}
		
		$menu = array(
			  'parent' => 'dev_monitor',
			  'id' => 'dev_monitor_widgets',
			  'title' => 'Widgets ('.($active + $inactive).')',
		);
		
		$activeSub = array(
			  'parent' => 'dev_monitor_widgets',
			  'id' => 'dev_monitor_widgets_active',
			  'title' => 'Active ('.$active.')',
		);
		
		$inactiveSub = array(
			  'parent' => 'dev_monitor_widgets',
			  'id' => 'dev_monitor_widgets_inactive',
			  'title' => 'Inactive ('.$inactive.')',
		);
		
		$activeSubList = array(
			  'parent' => 'dev_monitor_widgets_active',
			  'id' => 'dev_monitor_widgets_active_list',
			  'title' => '--Name--',
			  'meta'=>array(
				'html'=>'<ul>'.$activeStr.'</ul>',
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
		$inactiveSubList = array(
			  'parent' => 'dev_monitor_widgets_inactive',
			  'id' => 'dev_monitor_widgets_inactive_list',
			  'title' => '--Name--',
			  'meta'=>array(
				'html'=>'<ul>'.$inactiveStr.'</ul>',
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
			
		$wp_admin_bar->add_menu($menu);
		$wp_admin_bar->add_menu($activeSub);
		$wp_admin_bar->add_menu($inactiveSub);
		$wp_admin_bar->add_menu($activeSubList);
		$wp_admin_bar->add_menu($inactiveSubList);

		return $active.' Ws';
	}
	
	private function DevMonitor_do_conditionals(){
		global $post,$wp_admin_bar;
		// True/False
		$bools = array(
						'is_home',
						'is_front_page',
						'is_admin',
						'is_admin_bar_showing',
						'is_single',
						'is_sticky',
						'is_post_type_archive',
						'comments_open',
						'pings_open',
						'is_page',
						'is_page_template',
						'is_tag',
						'has_tag',
						'is_tax',
						'is_author',
						'is_date',
						'is_year',
						'is_month',
						'is_time',
						'is_new_day',
						'is_archive',
						'is_search',
						'is_404',
						'is_paged',
						'is_attachment',
						'is_singular',
						'is_trackback',
						'is_preview',
						'has_excerpt',
						'is_dynamic_sidebar',
						'is_blog_installed',
						'is_rtl',
						'is_multisite',
						'is_main_site',
						'is_super_admin',
						'is_user_logged_in',
						'is_child_theme',
						'has_term'
						);
		sort($bools);
		$trueB = array();
		$falseB = array();
		foreach($bools as $b)
		{
			$val = (call_user_func($b)) ? true : false;
			
			if ($val)
				$trueB[$b] = 1;
			else
				$falseB[$b] = 1;
			
		}
		
		$boolList = '<table class="ono-dev-dev-monitor-table"><tr><th>True</th><th>False</th></tr>';
		$boolList .= '<tr><td>';
		foreach($trueB as $tb=>$tv)
			$boolList .= '<span style="color:green">'.$tb.'</span><br>';
		$boolList .= '</td><td>';
		foreach($falseB as $fb=>$fv)
			$boolList .= '<span style="color:red">'.$fb.'</span><br>';
		$boolList .= '</tr></table>';
		
		$customStrings = array();
		if (is_single()) {
			$customStrings[]="is_single($post->ID)";
			$customStrings[]="is_single('$post->post_title')";
			$customStrings[]="is_single('$post->post_name')";
			$customStrings[]="is_single(array($post->ID,'$post->post_title','$post->post_name'))";
			
			$taxs = get_taxonomies();
			foreach($taxs as $t=>$tS)
			{
				if (has_term('',$t))
				{
					$customStrings[]="has_term('',$t)";
					
					$term_list = wp_get_post_terms($post->ID,$t);
					foreach($term_list as $term)
					{
						$customStrings[]="has_term('$term->slug','$t',$post->ID)";
					}
				}
			}
		}
		if (is_post_type_hierarchical( $post->post_type ) )
			$customStrings[]="is_post_type_hierarchical('$post->post_type')";
		
		
		if(is_sticky())
			$customStrings[]="is_sticky($post->ID)";
			
		if(is_post_type_archive() )
			$customStrings[] = "is_post_type_archive('$post->post_type')";
		
		if (is_page())
		{
			$customStrings[]="is_page($post->ID)";
			$customStrings[]="is_page('$post->post_title')";
			$customStrings[]="is_page('$post->post_name')";
			$customStrings[]="is_page(array($post->ID,'$post->post_title','$post->post_name'))";
		}
		
		if(is_category())
		{
			global $wp_query;
			$cat_name = $wp_query->query_vars['category_name'];
			$cat_id = $wp_query->query_vars['cat'];
			$customStrings[]="is_category($cat_id)";
			$customStrings[]="is_category('$cat_name')";
			$customStrings[]="is_category(array($cat_id,'$cat_name'))";
		}
		if (is_tag())
		{
			global $wp_query;
			$tag_name = $wp_query->query_vars['tag'];
			$tag_id = $wp_query->query_vars['tag_id'];
			$customStrings[]="is_tag($tag_id)";
			$customStrings[]="is_tag('$tag_name')";
			$customStrings[]="is_tag(array($tag_id,'$tag_name'))";
		}
		if(is_tax())
		{
			// only custom taxonomies
			
			global $wp_query;
			
			$tax_name = $wp_query->query_vars['taxonomy'];
			$tax_term = $wp_query->query_vars['term'];
			$customStrings[]="is_tax('$tax_name')";
			$customStrings[]="is_tax('$tax_name','$tax_term')";
		}
		
		sort($customStrings);
		
		$csStr = '<ol>';
		foreach($customStrings as $s)
			$csStr .= '<li>'.$s.'</li>';
		$csStr .= '</ol>';
		
		$menu = array(
			  'parent' => 'dev_monitor',
			  'id' => 'dev_monitor_conditionals',
			  'title' => 'Conditionals',
		);
		$boolMenu = array(
			  'parent' => 'dev_monitor_conditionals',
			  'id' => 'dev_monitor_truefalse',
			  'title' => 'True/False',
		);
		
		$boolMenuList = array(
			  'parent' => 'dev_monitor_truefalse',
			  'id' => 'dev_monitor_widgets_conditional_list',
			  'title' => 'These generic functions will return true or false on this page',
			  'meta'=>array(
				'html'=>$boolList,
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
		$pageMenu = array(
			  'parent' => 'dev_monitor_conditionals',
			  'id' => 'dev_monitor_pagemenu',
			  'title' => 'Page Specific',
		);
		$pageMenuList = array(
			  'parent' => 'dev_monitor_pagemenu',
			  'id' => 'dev_monitor_widgets_pagemenu_list',
			  'title' => 'These targeted functions will return true on this page',
			  'meta'=>array(
				'html'=>$csStr,
				'class' => 'ono-dev-dev-monitor-overflow'
			  )
		);
		$wp_admin_bar->add_menu($menu);
		$wp_admin_bar->add_menu($boolMenu);
		$wp_admin_bar->add_menu($boolMenuList);
		$wp_admin_bar->add_menu($pageMenu);
		$wp_admin_bar->add_menu($pageMenuList);
		return '';
	}
}
endif;

new OnoDev_DevMonitor();

function OnoDev_DevMonitor_meta( $links, $file ) { // add 'Support' link to plugin meta row once it has been approved
    if ( strpos( $file, 'dev-monitor.php' ) !== false ) {
        $links = array_merge( $links, array( '<a href="http://wordpress.org/plugins/dev-monitor/" title="Need help?">' . __('Support') . '</a>' ) );
		$links = array_merge( $links, array( '<a href="http://onodev.com/donate/" title="Support the development">' . __('Donate') . '</a>' ) );
    }
    return $links;
}
add_filter( 'plugin_row_meta', 'OnoDev_DevMonitor_meta', 10, 2 );


