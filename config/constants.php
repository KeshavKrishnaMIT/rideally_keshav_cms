<?php
define('BASE_URL', 'http://localhost/mini_pro_rideally/');
define('UPLOAD_DIR', dirname(__DIR__) . '/assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');

define('ROLE_SUPER_ADMIN', 'super_admin');
define('ROLE_ADMIN',       'admin');
define('ROLE_EDITOR',      'editor');
define('ROLE_AUTHOR',      'author');
define('ROLE_USER',        'user');

define('POST_DRAFT',    'draft');
define('POST_PENDING',  'pending');
define('POST_APPROVED', 'approved');
define('POST_REJECTED', 'rejected');

define('USER_ACTIVE',   'active');
define('USER_INACTIVE', 'inactive');
define('USER_BANNED',   'banned');

define('COMMENT_PENDING',  'pending');
define('COMMENT_APPROVED', 'approved');
define('COMMENT_REJECTED', 'rejected');