<?php
/**
 * Define database credentials
 */
define("DB_HOST", "localhost"); 
define("DB_NAME", "socialhub_app"); 
 define("DB_USER", "socialhub_app"); 
 define("DB_PASS", "IAMbrazilian*#*#AlsAls"); 
//define("DB_USER", "root"); 
//define("DB_PASS", "csduo2004mysql"); 
define("DB_ENCODING", "utf8"); // DB connnection charset


/**
 * Define DB tables
 */
define("TABLE_PREFIX", "np_");

// Set table names without prefix
define("TABLE_USERS", "users");
define("TABLE_ACCOUNTS", "accounts");
define("TABLE_PACKAGES", "packages");
define("TABLE_POSTS", "posts");
define("TABLE_GENERAL_DATA", "general_data");
define("TABLE_OPTIONS", "options");
define("TABLE_ORDERS", "orders");

define("TABLE_FILES", "files");
define("TABLE_CAPTIONS", "captions");
define("TABLE_PROXIES", "proxies");

define("TABLE_PLUGINS", "plugins");
define("TABLE_THEMES", "themes");
