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
define( 'WP_MEMORY_LIMIT', '256M' );
// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'cqljvytm_wp734' );

/** MySQL database username */
define( 'DB_USER', 'cqljvytm_wp734' );

/** MySQL database password */
define( 'DB_PASSWORD', 'pS2!6N00-E' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',         'qyjrzpwsxnfpzhsvaffmqh6odcil0u3objbgq3qh1erqx08yed8rcc1wzzckpt7d' );
define( 'SECURE_AUTH_KEY',  'snj4aadm78p59g2zhlqdjqjszqhhhvfwtopvhx92zaxnwvtfmhiebnzgkmyapnq4' );
define( 'LOGGED_IN_KEY',    'nhsc5yjpitramjqhcgu2fd2dy6z6yv9tmol82mwfrdwidgbmw80jbrh6992xlmc3' );
define( 'NONCE_KEY',        'pctrqbozbte1g52saphysumvrmuzeoghbt81bx9ojdghgctpqr6zfwqk19jwly7w' );
define( 'AUTH_SALT',        't27ny45x9dfrsjttcbzfovw0fze3fnabu1bpjjrzo9mqpfrdhuakd7tpa7dz7qhc' );
define( 'SECURE_AUTH_SALT', 'jmd6nkrfiaydq9svwfptzi5khu1qdc9h6iaiofxage9vgnhrknqg1wlxwjtkmtzl' );
define( 'LOGGED_IN_SALT',   'aelkazuzig3kik5e3r9vgjpygla5ezbbkl8y8e8dgf9txh5gcvvat7xk0xorkpcx' );
define( 'NONCE_SALT',       'cceecs5rnndhvsw3iikf440tpwpvi19aoujsc9cn38ahwlvvx4xa3mewbmfgwnve' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp7n_';

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
