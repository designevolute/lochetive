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
define('DB_NAME', 'lfish');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         '0{fy|uXtrE7Vl6zYR8;gHni{iDI.pjfOm==jMz~<{#6_(amA{,H0%?%V`I7)t;wy');
define('SECURE_AUTH_KEY',  '=tnrcePJZ]z8zrxxrqREU6LU`HdEzE=0Xg)<nZWc^sgZm:_pR!>WtCVxau{mamqq');
define('LOGGED_IN_KEY',    'bTwlT$x`X1KWz&,B3^=X5RD_Hc=i[NJ[u+bz/z,uGX:ML,m}!O#lc^H{Peh2Nx4!');
define('NONCE_KEY',        '!V2#t@qT{7fII>g_<{>S[v]Y&wI{0Hk|h,/h)c]t 5ux/Fh@ fo/Me)10~#G4>bO');
define('AUTH_SALT',        'cWp`f}|sg4eK?ifl0-Icp?WwvCsp!H3&;sBE>5#{EPkW62PJ{F,Do(Ukez_C]=e:');
define('SECURE_AUTH_SALT', 'r{Jm-7qJC7|37D3&q0&)uzfk7M.A&4Wm#nN $}KcHO47;TVg+s!6ZstF3vYqpki4');
define('LOGGED_IN_SALT',   'r*el3I}$q;9`Y>)P@a(s6JwU%x5V{d0B+62L{_n3Y:7+G`@5!1?1ajMtD CSQ2M&');
define('NONCE_SALT',       '!0uQ]hP+K5bf8hEi2!Q9X:MKH75DOGeg,lyPY|-R_Ou]k@iduWvXp8}zY}ogq(C.');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
