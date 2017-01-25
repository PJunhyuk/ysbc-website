<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'ysbcadmin');

/** MySQL database username */
define('DB_USER', 'ysbcadmin');

/** MySQL database password */
define('DB_PASSWORD', 'ysbcadmin1!');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         ',&=+t=-p@^T$[>AN_|i+Sd>?w&9lw}9%R*~9=50:!&u Sww?dsS&|(0uT]>6t_s)');
define('SECURE_AUTH_KEY',  'KsfeKT_6^?y`V^d?m9OG![!d.XLK=9^yd(GBovU8u}}rcCF?9Vg~u8b&cq+IknBl');
define('LOGGED_IN_KEY',    '$:,oy[M(KCpsAeUPJ~Q.bn>tT~iqwA4)EK[}i8rJcYAnrnNVf:w/u$pFeU[2$/nK');
define('NONCE_KEY',        ']fiU8h7+q*CcA%(9S<9%W%,Q}]yv47}YWNxN#AuQ2a0Gjif~g%l+&-$t`z`oM&@:');
define('AUTH_SALT',        'a_*!Y{={yfF.Q{/dS_fZrC0`lKKcV]7nnfpi$.9-)OdaD>L2mBE8+(9Z`9q~Q|~8');
define('SECURE_AUTH_SALT', 'SSh08=1.Z/~q4m^H$ov2+B1y|Bk).AFL2=Kgop@e2IE~.8<]J@Cb#c|d[74Omd{G');
define('LOGGED_IN_SALT',   'tZb3a63[|sSyJ _M,BqIsP+~1M}Hk8SEch`VLj5+`I)(JX1m~]GS|87O+Ctk dRy');
define('NONCE_SALT',       'csZ-zIO7HsIq7/JlmIN7gw.R1dFk,TZ9]@{DeN;<.tkHQ6Tg;KV)!bP1@^RvNY{C');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ysbcwp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
