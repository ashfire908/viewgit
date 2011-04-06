<?php
require_once('user.php');

class UserAdminPlugin extends VGPlugin {
    function __construct() {
        $this->page =& $GLOBALS['page'];
        $this->conf =& $GLOBALS['conf'];
        
        if (isset($this->conf['useradmin']) and $this->conf['useradmin']) {
            if (!isset($this->conf['auth_lib']) or $this->conf['auth_lib'] != 'mysql') {
                // We need the mysql module
                die('User Admin Plugin requires the mysql auth module to be in use.');
            }
            if (!isset($this->conf['logout']) or !$this->conf['logout']) {
                // We need the logout module
                die('User Admin Plugin requires the logout plugin to be enabled.');
            }
            $this->register_action('admin');
            $this->register_hook('plugin_logout');
        }
    }
    
    // Globals
    private $page;
    private $conf;
    
    // Settings
    private $db_connection;
    
    // Action handler
    function action($action) {
        switch ($action) {
            case 'admin':
                $this->action_user_admin();
                break;
        }
    }
    
    // Hook handler
    function hook($type) {
        switch ($type) {
            case 'plugin_logout':
                $this->hook_plugin_logout();
                break;
        }
    }
    
    // Display the given template.
    function display_template($template, $with_headers = true) {
        global $conf;
        global $page;
        
        if ($with_headers) {
            require 'templates/header.php';
        }
        require "$template";
        if ($with_headers) {
            require 'templates/footer.php';
        }
    }
    
    // DB connect
    private function db_connect() {
        // Check for valid settings
        if (isset($this->conf['auth_mysql']['db_server'])) {
            $server = $this->conf['auth_mysql']['db_server'];
        } else {
            trigger_error('No user database server specified', E_USER_ERROR);
        }
        if (isset($this->conf['auth_mysql']['db_user'])) {
            $username = $this->conf['auth_mysql']['db_user'];
        } else {
            trigger_error('No user adatabase username specified', E_USER_ERROR);
        }
        if (isset($this->conf['auth_mysql']['db_pass'])) {
            $password = $this->conf['auth_mysql']['db_pass'];
        } else {
            trigger_error('No user database password specified', E_USER_ERROR);
        }
        if (isset($this->conf['auth_mysql']['db_name'])) {
            $database = $this->conf['auth_mysql']['db_name'];
        } else {
            trigger_error('No user database specified', E_USER_ERROR);
        }
        
        // Connect to database
        $connection = mysql_connect($server, $username, $password);
        
        if ($connection == false) {
            trigger_error('Failed to connect to the user database', E_USER_ERROR);
            return false;
        } else {
            if (mysql_select_db($database, $connection) == false) {
                trigger_error('Failed to select the user database', E_USER_ERROR);
                return false;
            } else {
                $this->db_connection = $connection;
                return true;
            }
        }
    }
    
    // DB Query
    private function db_query($query) {
        $query_output = mysql_query($query, $this->db_connection);
        if ($query_output == false) {
            trigger_error('User database query failed', E_USER_ERROR);
            return false;
        } else {
            return $query_output;
        }
    }
    
    // DB Quote
    private function db_quote($input) {
        return mysql_real_escape_string($input, $this->db_connection);
    }
    
    // DB Error
    private function db_error() {
        return mysql_error($this->db_connection);
    }
    
    // Check for request parameter
    private function check_param($name) {
        if (isser($_REQUEST[$name])) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get request parameter
    private function get_param($name, $filter=null) {
        if (isset($_REQUEST[$name])) {
            $param = trim($_REQUEST[$name]);
            
            if ($filter != null) {
                return call_user_func($filter, $param);
            } else {
                return $param;
            }
        } else {
            return false;
        }
    }
    
    // Hash password
    private function hash_pass($password) {
        if ($password == '') {
            return false;
        } else {
            return md5($password);
        }
    }
    
    // Error - No User
    private function error_no_user($uid) {
        // Set page variables
        $this->page['userinfo']['id'] = $uid;
        
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/no_user', true);
        die;
    }
    
    // Error - No Auth
    private function error_no_auth() {
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/no_auth', true);
        die;
    }
    
    // Error - Unknown Mode
    private function error_unknown_mode() {
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/unknown_mode', true);
        die;
    }
    
    // Error - Missing UID
    private function error_missing_id() {
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/missing_id', true);
        die;
    }
    
    // Error - MySQL query failure
    private function error_mysql_query() {
        // Set page variables
        if ($this->conf['debug']) {
            $this->page['mysql_error'] = $this->db_error();
        }
        
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/mysql_query', true);
        die;
    }
    
    // Error - MySQL connect failure
    private function error_mysql_connect() {
        // Display page
        $this->page['title'] = 'Error - User Admin - ViewGit';
        $this->display_plugin_template('error/mysql_connect', true);
        die;
    }
    
    // Add error to error list
    private function add_error($message, $mode) {
        switch ($mode) {
            case 'admin':
                $this->page['useradmin']['errors'][] = $message;
                break;
        }
    }
    
    // Add message to message list
    private function add_message($message, $mode) {
        switch ($mode) {
            case 'admin':
                $this->page['useradmin']['messages'][] = $message;
                break;
        }
    }
    
    // Combine available projects with authed projects
    private function all_projects($authed_projects) {
        return array_unique(array_merge(array_keys($this->conf['projects']), $authed_projects));
    }
    
    // Get the status of a user's authed projects
    private function auth_status($authed_projects) {
        $auth_status = array();
        
        foreach($this->all_projects($authed_projects) as $project) {
            if (in_array($project, $authed_projects)) {
                $auth_status[$project] = true;
            } else {
                $auth_status[$project] = false;
            }
        }
        
        return $auth_status;
    }
    
    // Determine changes made between two sets of authed projects
    private function auth_changes($current_auth, $updated_auth) {
        $reverse = array_diff_assoc($current_auth, $updated_auth);
        array_walk($reverse, create_function('&$item,$key', '$item = $item ? false : true;'));
        return array_merge($reverse, array_diff_assoc($updated_auth, $current_auth));
    }
    
    // Get user (by ID)
    private function get_user($uid, $projects=true) {
        // Quote input
        $quid = $this->db_quote($uid);
        
        // Get user data
        $query = 'SELECT `usr_name`, `usr_type`, `usr_status`, `usr_comment` FROM `users`' .
                 " WHERE `usr_id` = '$quid'";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        
        // Check if user exists
        if (mysql_num_rows($result) == 1) {
            $data = mysql_fetch_assoc($result);
            // Create user object
            $user = new User();
            $user->id = $uid;
            $user->name = $data['usr_name'];
            $user->type = $data['usr_type'];
            $user->status = $data['usr_status'];
            $user->comment = $data['usr_comment'];
            if ($projects) {
                $user->projects = auth_projects_allowed_check($uid);
            }
        } else {
            $this->error_no_user($uid);
        }
        
        // Return user
        return $user;
    }
    
    // Get uid (by username)
    private function get_uid($username) {
        // Quote input
        $username = $this->db_quote($username);
        
        // Lookup user's id
        $query = 'SELECT `usr_id` FROM `users`' .
                 " WHERE `usr_name` = '$username'";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        
        // Check if the user actually exists
        if (mysql_num_rows($result) == 1) {
            $data = mysql_fetch_assoc($result);
            return $data['usr_id'];
        } else {
            return false;
        }
    }
    
    // Get username (by ID)
    private function get_username($uid) {
        // Quote input
        $uid = $this->db_quote($uid);
        
        // Lookup username
        $query = 'SELECT `usr_name` FROM `users`' .
                 " WHERE `usr_id` = '$uid'";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        
        // Check if the user actually exists
        if (mysql_num_rows($result) == 1) {
            $data = mysql_fetch_assoc($result);
            return $data['usr_name'];
        } else {
            return false;
        }
    }
    
    // Add new user
    private function add_user($username, $password, $type, $status, $comment) {
        // Quote input
        $qusername = $this->db_quote($username);
        $password = $this->db_quote($password);
        $qtype = $this->db_quote($type);
        $qstatus = $this->db_quote($status);
        $qcomment = $this->db_quote($comment);
        
        // Add user to table
        $query = 'INSERT INTO `users` (`usr_name`, `usr_pass`, `usr_type`, `usr_status`, `usr_comment`)' .
                 "VALUES ('$qusername', '$password', '$qtype', '$qstatus', '$qcomment')";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }

        // Create User object
        $user = new User();
        $user->id = $this->get_uid($username);
        $user->name = $username;
        $user->type = $type;
        $user->status = $status;
        $user->comment = $comment;
        
        // Return user
        return $user;
    }
    
    // Edit user
    private function edit_user($uid, $username, $password, $type, $status, $comment) {
        // Check if a password was given
        $update_password = is_null($password) ? false : true;

        // Quote input
        $quid = $this->db_quote($uid);
        $username = $this->db_quote($username);
        if ($update_password) {
            $password = $this->db_quote($password);
        }
        $type = $this->db_quote($type);
        $status = $this->db_quote($status);
        $comment = $this->db_quote($comment);
        
        // Edit user's record
        if ($update_password) {
            $query = "UPDATE `users` SET `usr_name` = '$username', `usr_pass` = '$password', `usr_type` = '$type', `usr_status` = '$status', `usr_comment` = '$comment'" .
                     " WHERE `usr_id` = $quid";
        } else {
            $query = "UPDATE `users` SET `usr_name` = '$username', `usr_type` = '$type', `usr_status` = '$status', `usr_comment` = '$comment'" .
                     " WHERE `usr_id` = $quid";
        }
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        
        // Purge cache for user
        auth_cache_purge($uid);
    }
    
    // Edit authed projects
    private function edit_auth($uid, $authed_projects) {
        // Quote input
        $quid = $this->db_quote($uid);
        
        // Calculate the changes
        $current_auth = $this->auth_status(auth_projects_allowed_check($uid));
        $new_auth = $this->auth_status($authed_projects);
        $changes = $this->auth_changes($current_auth, $new_auth);
        
        // Return if there is nothing to do
        if (!count($changes) > 0) {
            return;
        }
        
        // Break down into projects to add and remove
        $add = array();
        $remove = array();
        foreach($changes as $project => $status)
        {
            if ($status) {
                $add[] = $project;
            } else {
                $remove[] = $project;
            }
        }
        $do_add = count($add) > 0 ? true : false;
        $do_remove = count($remove) > 0 ? true : false;
        
        // Build queries
        if ($do_add) {
            // Build add query
            $query_add = 'INSERT INTO `repo_auth` (`aut_user_id`, `aut_repo_name`)' .
                         " VALUES ";
            $build = array();
            foreach($add as $project) {
                $build[] = "($quid, '$project')";
            }
            $query_add .= implode(', ', $build);
        }
        
        if ($do_remove) {
            // Build remove query
            $query_remove = 'DELETE FROM `repo_auth`'.
                            " WHERE `aut_user_id` = $quid AND ";
            $build = array();
            foreach($remove as $project) {
                $build[] = "`aut_repo_name` = '$project'";
            }
            $query_remove .= '(' . implode(' OR ', $build) . ')';
        }
        
        // Execute queries
        if ($do_add) {
            $result = $this->db_query($query_add);
            if ($result == false) { $this->error_mysql_query(); }
        }
        if ($do_remove) {
            $result = $this->db_query($query_remove);
            if ($result == false) { $this->error_mysql_query(); }
        }
        
        // Purge cache for user
        auth_cache_purge($uid);
    }
    
    // Delete user
    private function delete_user($uid) {
        // Quote input
        $quid = $this->db_quote($uid);
        
        // Delete user auth
        $query = 'DELETE FROM `repo_auth`' .
                 " WHERE `aut_user_id` = $quid";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        // Delete user account
        $query = 'DELETE FROM `users`' .
                 " WHERE `usr_id` = $quid";
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }
        
        // Purge cache for user
        auth_cache_purge($uid);
    }
    
    // List users
    private function list_users() {
        $users = array();
        
        // Get user data
        $query = 'SELECT `usr_id`, `usr_name`, `usr_type`, `usr_status` FROM `users` ' .
                 'ORDER BY `usr_id` ASC';
        $result = $this->db_query($query);
        if ($result == false) { $this->error_mysql_query(); }

        // Create user objects for list
        while ($data = mysql_fetch_assoc($result)) {
            $user = new User();
            $user->id = $data['usr_id'];
            $user->name = $data['usr_name'];
            $user->type = $data['usr_type'];
            $user->status = $data['usr_status'];
            
            $users[] = $user;
        }
        
        return $users;
    }
    
    // Event - Add user
    private function event_add_user() {
        // Check for a submitted form
        $submission = $this->get_param('submit_user');
        if ($submission !== false) {
            // Form submitted, process form
            $abort = false;
            
            // Check we have the fields, filter stuff
            // Username
            $username = $this->get_param('name');
            if ($username === false or $username == '') {
                $abort = true;
                $this->add_error('Username is not valid/missing.', 'admin');
            }
            // Password
            $password = $this->get_param('password', array($this, 'hash_pass'));
            if ($password === false or $password != $this->get_param('password_confirm', array($this, 'hash_pass'))) {
                $abort = true;
                $this->add_error('Passwords do not match or are missing.', 'admin');
            }
            // Type
            $type = $this->get_param('type');
            if ($type === false or !in_array($type, array('standard', 'admin'))) {
                $abort = true;
                $this->add_error('User type is not valid/missing.', 'admin');
            }
            // Status
            $status = $this->get_param('status');
            if ($status === false or !in_array($status, array('active', 'disabled'))) {
                $abort = true;
                $this->add_error('User status is not valid/missing.', 'admin');
            }
            // Comment
            $comment = $this->get_param('comment');
            if ($comment === false) {
                $abort = true;
                $this->add_error('Comment is not valid/missing.', 'admin');
            }
            
            if (!$abort) {
                // Add user
                $this->page['user'] = $this->add_user($username, $password, $type, $status, $comment);
                
                // Mark success
                $this->page['useradmin']['success'] = true;
            }
        }
        
        // Display page
        $this->page['title'] = 'Add User - User Admin - ViewGit';
        $this->display_plugin_template('admin/adduser', true);
    }
    
    // Event - View user
    private function event_view_user() {
        // Check for the user id
        $uid = $this->get_param('id');
        if ($uid === false or $uid == '') {
            $this->error_missing_id();
        }
        
        // Get user
        $this->page['user'] = $this->get_user($uid);

        // Display page
        $this->page['title'] = 'View User - User Admin - ViewGit';
        $this->display_plugin_template('admin/viewuser', true);
    }
    
    // Event - Edit user
    private function event_edit_user() {
        // Check for the user id, verify it exists
        $uid = $this->get_param('id');
        if ($uid === false or $uid == '') {
            $this->error_missing_id();
        }
        if ($this->get_username($uid) === false) {
            $this->error_no_user($uid);
        }
        
        // Check for a submitted form
        $submission = $this->get_param('submit_user');
        if ($submission !== false) {
            // Form submitted.
            $abort = false;
            
            // Check we have the fields, filter stuff
            // Username
            $username = $this->get_param('name');
            if ($username === false or $username == '') {
                $abort = true;
                $this->add_error('Username is not valid/missing.', 'admin');
            }
            // Password
            $keep_pass = $this->get_param('password_keep');
            if ($keep_pass === false or $keep_pass != 'true') {
                $password = $this->get_param('password', array($this, 'hash_pass'));
                if ($password === false or $password != $this->get_param('password_confirm', array($this, 'hash_pass'))) {
                    $abort = true;
                    $this->add_error('Passwords do not match or are missing.', 'admin');
                }
            } else {
                $password = null;
            }
            // Type
            $type = $this->get_param('type');
            if ($type === false or !in_array($type, array('standard', 'admin'))) {
                $abort = true;
                $this->add_error('User type is not valid/missing.', 'admin');
            }
            // Status
            $status = $this->get_param('status');
            if ($status === false or !in_array($status, array('active', 'disabled'))) {
                $abort = true;
                $this->add_error('User status is not valid/missing.', 'admin');
            }
            // Comment
            $comment = $this->get_param('comment');
            if ($comment === false) {
                $abort = true;
                $this->add_error('Comment is not valid/missing.', 'admin');
            }
            
            if (!$abort) {
                // Edit user
                $this->edit_user($uid, $username, $password, $type, $status, $comment);
                
                // Mark success
                $this->page['useradmin']['success'] = true;
            }
        }
        
        // Get user data
        $this->page['user'] = $this->get_user($uid, false);
        
        // Display page
        $this->page['title'] = 'Edit User - User Admin - ViewGit';
        $this->display_plugin_template('admin/edituser', true);
    }
    
    // Event - Edit authed projects
    private function event_edit_auth() {
        // Check for the user id, verify it exists, get username
        $uid = $this->get_param('id');
        if ($uid === false or $uid == '') {
            $this->error_missing_id();
        }
        $username = $this->get_username($uid);
        if ($username === false) {
            $this->error_no_user($uid);
        }
        
        // Check for a submitted form
        $submission = $this->get_param('submit_auth');
        if ($submission !== false) {
            // Form submitted, get new list of authed projects
            $authed_projects = array();
            foreach($this->all_projects(auth_projects_allowed_check($uid)) as $project) {
                if ($this->get_param(str_replace(' ', '_', $project)) == 'on') {
                    $authed_projects[] = $project;
                }
            }
            
            // Update authed projects
            $this->edit_auth($uid, $authed_projects);
            
            // Mark success
            $this->page['useradmin']['success'] = true;
        }
        
        // Get auth status
        $this->page['useradmin']['auth'] = $this->auth_status(auth_projects_allowed_check($uid));
        
        // Create user object
        $this->page['user'] = new User();
        $this->page['user']->id = $uid;
        $this->page['user']->name = $username;
        
        // Display page
        $this->page['title'] = 'Edit Authorized Projects - User Admin - ViewGit';
        $this->display_plugin_template('admin/editauth', true);
    }
    
    // Event - Delete user
    private function event_delete_user() {
        // Check for the user id, verify it exists, get username
        $uid = $this->get_param('id');
        if ($uid === false) {
            $this->error_missing_id();
        }
        $username = $this->get_username($uid);
        if ($username === false) {
            $this->error_no_user($uid);
        }
        
        // Check for deletion comfirmation
        $confirm = $this->get_param('confirm');
        if ($confirm == 'Yes') {
            // Confirmed
            $this->page['useradmin']['confirm'] = true;
            
            // Delete user
            $this->delete_user($uid);
        }
        
        // Create user object
        $this->page['user'] = new User();
        $this->page['user']->id = $uid;
        $this->page['user']->name = $username;
        
        // Display page
        $this->page['title'] = 'Delete User - User Admin - ViewGit';
        $this->display_plugin_template('admin/deleteuser', true);
    }
    
    // Event - Userlist
    private function event_user_list() {
        // Get userlist
        $this->page['users'] = $this->list_users();
        
        // Display page
        $this->page['title'] = 'Userlist - User Admin - ViewGit';
        $this->display_plugin_template('admin/userlist', true);
    }
    
    // Action - User Admin
    private function action_user_admin() {
        // Check if the user is authorized
        if (auth_user_type() != 'admin') {
            $this->error_no_auth();
        }
        
        // Setup page
        $this->page['title'] = 'User Admin - ViewGit';
        $this->page['subtitle'] = 'User Admin';

        // Check for mode
        $mode = $this->get_param('m', 'strtolower');
        if ($mode === false) {
            $mode = 'userlist';
        }
        
        // Setup page variables
        $this->page['useradmin'] = array();
        $this->page['useradmin']['errors'] = array();
        $this->page['useradmin']['messages'] = array();
        $this->page['useradmin']['success'] = false;
        $this->page['useradmin']['confirm'] = false;
        
        // Connect to the database
        if (!$this->db_connect()) {
            $this->error_mysql_connect();
        }
        
        // Hand off to the method for the mode given
        switch($mode) {
            case 'adduser':
                // Add user
                $this->event_add_user();
                break;
            case 'viewuser':
                // View user
                $this->event_view_user();
                break;
            case 'edituser':
                // Edit user
                $this->event_edit_user();
                break;
            case 'editauth':
                // Edit user's authed projects
                $this->event_edit_auth();
                break;
            case 'deleteuser':
                // Delete user
                $this->event_delete_user();
                break;
            case 'userlist':
                // Display userlist
                $this->event_user_list();
                break;
            default:
                // Display error message
                $this->error_unknown_mode();
                break;
        }
    }
    
    // Hook - Logout plugin
    private function hook_plugin_logout() {
        switch (auth_user_type()) {
            case 'admin':
                $this->output(' <a href="' . makelink(array('a' => 'admin')) .
                              '" title="User Administration">Admin</a> ');
                break;
        }
    }
}
