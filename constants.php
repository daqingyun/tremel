<?php
error_reporting(0);
/**
 * Constants.php
 *
 * This file is intended to provide all constants to make it easier for the
 * site administrator to modify the login script.
 * 
 * <daqing.yun@gmail.com>
 *
 * July 29, 2017
 *      Add username and password for local xampp
 *      Add username and password for binf1
 * June 17, 2022
 *      Remove all personal info not needed
 * Last Updated: June 17, 2022
 */


/**
 * URL constants
 */
//define("BASE_URL", "http://binf2.memphis.edu/tremel/");
$hostname = gethostname();
define("BASE_URL", "http://" . $hostname . "/tremel/");

/**
 * Database Constants - these constants are required
 * in order to establish a successful connection
 * to the MySQL database. Make sure the information is
 * correct.
 */
 /* use the following credential for local xampp */
define("DB_SERVER", "localhost");
define("DB_USER", "xxxx");
define("DB_PASS", "xxxx");
define("DB_NAME", "tremel");

/* use the following credential for UM machine - for test only */
// define("DB_SERVER", "localhost");
// define("DB_USER", "tremel");
// define("DB_PASS", "projtremel");
// define("DB_NAME", "tremel");

/**
 * Database Table Constants - these constants
 * hold the names of all the database tables used
 * in the script.
 */
define("TBL_USERS", "users");
define("TBL_ACTIVE_USERS",  "active_users");
define("TBL_ACTIVE_GUESTS", "active_guests");
define("TBL_BANNED_USERS",  "banned_users");

/**
 * Special Names and Level Constants - the admin
 * page will only be accessible to the user with
 * the admin name and also to those users at the
 * admin user level. Feel free to change the names
 * and level constants as you see fit, you may
 * also add additional level specifications.
 * Levels must be digits between 0-9.
 */
define("ADMIN_NAME", "admin");
define("GUEST_NAME", "Guest");
define("ADMIN_LEVEL", 1);
define("USER_LEVEL",  2);
define("GUEST_LEVEL", 0);

/**
 * This boolean constant controls whether or
 * not the script keeps track of active users
 * and active guests who are visiting the site.
 */
define("TRACK_VISITORS", true);

/**
 * Timeout Constants - these constants refer to
 * the maximum amount of time (in minutes) after
 * their last page fresh that a user and guest
 * are still considered active visitors.
 */
define("USER_TIMEOUT", 100);
//define("GUEST_TIMEOUT", 5);

/**
 * Cookie Constants - these are the parameters
 * to the setcookie function call, change them
 * if necessary to fit your website. If you need
 * help, visit www.php.net for more info.
 * <http://www.php.net/manual/en/function.setcookie.php>
 */
define("COOKIE_EXPIRE", 60*60*24*100);  // 100 days by default
define("COOKIE_PATH", "/");  // Avaible in whole domain

/**
 * Email Constants - these specify what goes in
 * the from field in the emails that the script
 * sends to users, and whether to send a
 * welcome email to newly registered users.
 */
define("EMAIL_FROM_NAME", "Daqing Yun");
define("EMAIL_FROM_ADDR", "daqing.yun@gmail.com");
define("EMAIL_WELCOME", false);

/**
 * This constant forces all users to have
 * lowercase logins, capital letters are
 * converted automatically.
 */
define("ALL_LOWERCASE", false);

define("PAGE_SIZE", 10);
?>
