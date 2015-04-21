<?php



define('DB_NAME', 'knucles2014');


define('DB_USER', 'toys_grace_2014');


define('DB_PASSWORD', 'grace_admin_2014');


define('DB_HOST', '10.176.138.198');


define('DB_CHARSET', 'utf8');


define('DB_COLLATE', '');


define('AUTH_KEY',         'F,kc20j>4;1KvtTfn3+P5&zi/lyO5r7RrJyV|:OO!u(7-*}I}{wOzV|I&jU!xc,o');
	define('SECURE_AUTH_KEY',  'YyQl[YCXr0e^qWLD,A{+Pf_,Dp0KUaS{JGame}/ Uk+v$8cHVs&F5PAHv>*Y-|<l');
	define('LOGGED_IN_KEY',    'ev_#fn+Nm-!Y~nQdb~k|,chJJR,*Ij+|Tp`F[x/-5}5OTh|8!|q+N]vY)Zhwa?^M');
	define('NONCE_KEY',        'fF}4A.gH|B $(9q^r6@%gs1~h4pZJ|#v)iAP>x);R`-alBktQDe1^KOs~#2/;0zT');
	define('AUTH_SALT',        '-:LL{@?D=6;Om}W{*|-E3yqRSp(asz@GgP9u+,,8Vz0)Zo_R~Ib#0C[kaL_Y^u`+');
	define('SECURE_AUTH_SALT', '6Fk)-a$PAHioB.^-t=-Gm<CZ0!-(:yFg=I3{|!Vo?ES9hH5*FF#xTcU$Ww|CC5eU');
	define('LOGGED_IN_SALT',   'LxiV*;NyVXo-IVZVWD>_A,5}q4/~9i46];nm+*YV&=MWQ]9`?Qk&zI}r; ;j#vg[');
	define('NONCE_SALT',       '9fR6n</_o}ZOTZ|LK(g!g/J}uF~XqdaK(o;!ek&`:;TSY0W1uj2,i2efU+.7oLet');




		$table_prefix  = 'ks_';

		define( 'WP_SITEURL',     'http://www.http://buildingsets.toys/' );
		define( 'WP_HOME',        'http://www.http://buildingsets.toys/' );
		
		define('WPLANG', '');
		
		define( 'WP_POST_REVISIONS', false );

		define( 'WP_MEMORY_LIMIT', '128M' );
		define( 'WP_MAX_MEMORY_LIMIT', '512M' );

		define( 'WP_DEBUG_LOG',     true );
		define( 'WP_DEBUG_DISPLAY', false );
		define( 'SCRIPT_DEBUG',     true );
		define( 'SAVEQUERIES',      true );
		define( 'WP_ALLOW_MULTISITE', true );

		define('FS_METHOD', 'direct');





		if ( !defined('ABSPATH') )
			define('ABSPATH', dirname(__FILE__) . '/');


		require_once(ABSPATH . 'wp-settings.php');
