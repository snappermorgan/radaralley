<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME',$_SERVER['RA_WP_DB_NAME']);

/** MySQL database username */
define('DB_USER', $_SERVER['RA_WP_DB_USER']);

/** MySQL database password */
define('DB_PASSWORD', $_SERVER['RA_WP_DB_PASSWORD']);

/** MySQL hostname */
define('DB_HOST', $_SERVER['RA_WP_DB_HOST']);

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'P7:H=f[w!~^?pc-cmE?x%>=nUK]:*MI(tjmjoM_y-W9iZ=Rgk/RsP+n~ywb)QVXh');
define('SECURE_AUTH_KEY',  'T8lal}IK:W-q3>=3lK>+1hU>|;E3U+i=+#uq71J$vX)rodlyO0IwzVf1}FUI&jFj');
define('LOGGED_IN_KEY',    'NBwBIe#6~b=NV/;<8o=c6N6@C~-HuDIBQ(,qP`A2!m}7VZn3-yT)L,|FY&H^fm@K');
define('NONCE_KEY',        '[J9b.Rr:T_:VRsnXG!5[hU=d+h7B|*m,vFx^J[/-I3?MfE~JDL|dfzrrxTH(|8_ ');
define('AUTH_SALT',        'K$#/NG1h/|5-8w~699O9!f`Pq[+gQVW:yQ3+q07tik.13gOmVZ(` qf@wJjnp)|&');
define('SECURE_AUTH_SALT', 's(4.HgN.J+JlTmnAg1PZ?mF1(22EaC9V7+&yxL,UoR7WFaQU8yrL9pq^4b95yC<C');
define('LOGGED_IN_SALT',   'J!R{KDB}mQlhHgc6(KBY!.4BM1-Cc pmsZ$(s1aEn2?th#9&8M0)zpe^jWoPs||K');
define('NONCE_SALT',       'OoyGY5&g^n~CrlsJq7DC_8-|}s0e0H-&x-1do0{wS8/,ky|R/Gh~0(-m2,Iq)n>@');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'rawp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
/*define ('WP_DEBUG', true);
define ('WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', false );
*/
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
