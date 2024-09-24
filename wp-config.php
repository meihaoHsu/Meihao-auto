<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'meihao_auto_tw');

/** Database username */
define('DB_USER', 'meihao_auto_tw');

/** Database password */
define('DB_PASSWORD', 'weJXE1OH5d0qcNUK');

/** Database hostname */
define('DB_HOST', 'localhost');

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '3EdzDbvcAOXz2wC8kNsG2peHCbi8ahXOsjBuXQrXVAXikV5YWjQha13DvCLVtYrR');
define('SECURE_AUTH_KEY', 'VFSRXQHiKMPP5qPCnOSGNmMU6d9fYPfZJhQWlGm9FiHt0Cq61IifsrRvU31a99BM');
define('LOGGED_IN_KEY', 'KVP8qgrTacQMFaGVmmtBmfOc51Q3qgKY1hdxTNWRRGVKKUjAIFq5ak0HFnZuAo1k');
define('NONCE_KEY', 'ywAhl94germXsyNGAlVlY699l6QGO2gLtoic3qcUMyM4G3q6nGEg6beMID52nR9p');
define('AUTH_SALT', 'KAjC3HEoiCHb8RVaK5Yk4zCir9qmIevWleaKT56eyDhWDXpVxuxzTfvtQ8QnEGgN');
define('SECURE_AUTH_SALT', 'ln6fVHSKCqlds9JMogMRNSf1YoyfGkjnvNb0BdFu6KYFthZkHS99eyuPSn6iuIvg');
define('LOGGED_IN_SALT', 'mYVyyETSNY0TAB1VJEEZs1F0Semj4lBijNsPU784Zj2GC6nkjgXGQbEfFsbNlQaX');
define('NONCE_SALT', 'AE622jLMlms24HbpD8OQxWpBq748K54KIlg6JKei0ziSmdEUtpFwYfPTSrvJYlOr');

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */

/** 開啟WordPress偵錯功能 */
define('WP_DEBUG', false );
/** 產生錯誤記錄檔，產生於wp-content/debug.log */
define('WP_DEBUG_LOG', false );
/** 顯示錯誤訊息於html上 */
define('WP_DEBUG_DISPLAY', false );
/** 腳本偵錯功能，如設為true，則載入非minified的js */
define('SCRIPT_DEBUG', false );

define('WP_REDIS_SELECTIVE_FLUSH', true);
define('WP_CACHE_KEY_SALT', 'wp_meihao-auto.tw');
/* Add any custom values between this line and the "stop editing" line. */



define('WP_REDIS_SELECTIVE_FLUSH', true);
define('WP_CACHE_KEY_SALT', 'wp_meihao-auto.tw');
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
