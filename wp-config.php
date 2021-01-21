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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'woocommerce' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8/(%WOj?+BVi+d%=IXa!3}E;|O8MNS3`5y NEoeZ9H:Dciff{kC-3({9yzpflc&X' );
define( 'SECURE_AUTH_KEY',  ' {649D~.{V|Rjh{{&@K1{%~e>)4Bm?XQ#b>9wQ|l/{_7R]d5i`~XEhI+RxQR>/Jk' );
define( 'LOGGED_IN_KEY',    '%x[XdpFf%H@?kGPkLo{~q?MX;e/7,vy)$/5`hnXGEmCPF%/}Z^U+yn:GZCn~%q}C' );
define( 'NONCE_KEY',        ':-MG_LmX6iPvovN*$.:!%4U6q$4HQe.n#i iDbB;7G.SckhyBfy;4Q`RQB+{68)e' );
define( 'AUTH_SALT',        'dJ?9q&B32g1<dyX:zMRZgBaPjJ#)E9D]*v*WmK+SVuU?Xklu~X3t!r-8tTsFCE*m' );
define( 'SECURE_AUTH_SALT', '4ezsxkIGR|$?0O^[rN$R;~=0RsE X?bR}VY@3L{ZFC4$YUqPuhN. tLQ0MaNpoZ|' );
define( 'LOGGED_IN_SALT',   'w#zvFh!+vJ# B]$C3$P];zN26V4,$Bq>cpO7>|IcHw(*|@ft0KV^73,bo##vzHgU' );
define( 'NONCE_SALT',       'O<g?L8[;zUa%Y.D.uDVKNaD{2hXvz!Gj(l=RZQ^I<x0Rj4]BtK/&S#Q(/nWYrcEU' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';
define('FS_METHOD','direct');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
