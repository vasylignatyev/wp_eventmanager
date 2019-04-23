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
define('DB_NAME', 'emanager');

/** MySQL database username */
define('DB_USER', 'u_emanager');

/** MySQL database password */
define('DB_PASSWORD', 'Sedwo143B');

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
define('AUTH_KEY',         'f85D~YjN^a`Jnb|00cpPh]@E&n#(C!u2i+W,A)bA3QP5BKz[ d:, HFvUQV^BOzl');
define('SECURE_AUTH_KEY',  'hpYcMfpRqzCFG%$qJwU7/Hd0kq}A2jg?= ZtW3=d]s_8Yn,qfAie`9bapWK?rd&x');
define('LOGGED_IN_KEY',    'aVYlwc^f6T!*O2L.~XY[[uU28xYb+u#a`<9QKA^wy32KwSg!S.[Uc} }G8y+2NmP');
define('NONCE_KEY',        'P]K}dp#y2.T]45=*+M}K)5y6@3[+@42@z62sW[`.k6Rmj4D(zW[|7GyZr*tC:h|y');
define('AUTH_SALT',        '?P;.K,fD+o,9jgzo:sKKsQAp0Yfg^F=7To=dEN^B%>*:GcX9u`|c0ww>oZ>YO11q');
define('SECURE_AUTH_SALT', '8Nm]{vuiZ/{{pJ>Tqz@:F]VQc #oV)DANjQs:&;xOkk84yh>~7`U99gDqh<0eQrb');
define('LOGGED_IN_SALT',   'Tw:Qra ~w6vsW<ZSSxGUz@43W]TD2+Ok^HiwY|ud-86<P0o0_#v.aO/w|40?>v@2');
define('NONCE_SALT',       'iwiT r>b-A0Mu,u8E#w59AP$OCGWB7j!j%q&_/KC)y:fbp*b{?i*e2KMO)l]$=59');

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
