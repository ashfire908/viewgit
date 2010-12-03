<?php
class UserAdminPlugin extends VGPlugin {
    function __construct() {
        global $conf;
        
        if (isset($conf['useradmin']) and $conf['useradmin']) {
            if (!isset($conf['auth_lib']) or $conf['auth_lib'] != 'mysql') {
                // We need the mysql module
                die('User Admin Plugin requires the mysql auth module to be in use.');
            }
            if (!isset($conf['logout']) or !$conf['logout']) {
                // We need the logout module
                die('User Admin Plugin requires the logout plugin to be enabled.');
            }
            $this->register_action('admin');
            $this->register_hook('plugin_logout');
        }
    }
    
    // Settings
    private $db_connection;
    
    // Action handler
    function action($action) {
        global $page;
        global $conf;
        
        // Check if the user is authorized
        if (auth_user_type() != 'admin') {
            // Display error page
            // Header
            require 'templates/header.php';
            $this->display_plugin_template('no_auth', false);
            // Footer
            require 'templates/footer.php';
            die;
        }
        
        // Setup page
        $page['title'] = 'User Admin - ViewGit';
        $page['subtitle'] = 'User Admin';
        
        // Connect to the database
        $this->db_connect();
        
        switch($_REQUEST['m']) {
            case 'adduser':
                // Add user
                $this->adduser();
                break;
            case 'viewuser':
                // View user
                $this->viewuser($_REQUEST['id']);
                break;
            case 'edituser':
                // Edit user
                $this->edituser($_REQUEST['id']);
                break;
            case 'editauth':
                // Edit user's authed projects
                $this->editauth($_REQUEST['id']);
                break;
            case 'deleteuser':
                // Delete user
                $this->deleteuser($_REQUEST['id']);
                break;
            case 'userlist':
            default:
                // Display userlist
                $this->userlist();
                break;
        }
    }
    
    // Hook handler
    function hook($type) {
        if ($type == 'plugin_logout' and auth_user_type() == 'admin') {
            $this->output(' <a href="' . makelink(array('a' => 'admin')) . '"' .
                          ' title="User Administration">Admin</a> ');
        }
    }
    
    // DB connect
    private function db_connect() {
        global $conf;
        
        // Check for valid settings
        if (isset($conf['auth_mysql']['db_server'])) {
            $server = $conf['auth_mysql']['db_server'];
        } else {
            trigger_error('No user database server specified', E_USER_ERROR);
        }
        if (isset($conf['auth_mysql']['db_user'])) {
            $username = $conf['auth_mysql']['db_user'];
        } else {
            trigger_error('No user adatabase username specified', E_USER_ERROR);
        }
        if (isset($conf['auth_mysql']['db_pass'])) {
            $password = $conf['auth_mysql']['db_pass'];
        } else {
            trigger_error('No user database password specified', E_USER_ERROR);
        }
        if (isset($conf['auth_mysql']['db_name'])) {
            $database = $conf['auth_mysql']['db_name'];
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
    
    // Error - No User
    private function no_user($uid) {
        global $conf;
        global $page;
        
        // Set page variables
        $page['userinfo']['id'] = $uid;
        
        // Display page
        $page['title'] = 'Error - User Admin - ViewGit';
        // Header
        require 'templates/header.php';
        $this->display_plugin_template('no_user', false);
        // Footer
        require 'templates/footer.php';
    }
    
    // Compare all projects with authed projects
    private function compare_auth($projects, $authed) {
        $output = array();
        
        // Find if the user is authed for each project is the projects list
        foreach($projects as $project) {
            if (in_array($project, $authed)) {
                $output[$project] = true;
            } else {
                $output[$project] = false;
            }
        }
        
        // Add the authed projects not in the projects list
        foreach($authed as $project) {
            if (!in_array($project, $projects)) {
                $output[$project] = true;
            }
        }
        
        return $output;
    }
    
    // Add user
    private function adduser() {
        global $conf;
        global $page;
        
        $page['userinfo'] = array();
        $errors = '';
        
        // Check for a submitted form
        if (isset($_REQUEST['submit_user'])) {
            // Form submitted, process form
            $abort = false;
            $page['userinfo']['success'] = false;
            
            // Check we have the fields, filter stuff
            // Username
            if (isset($_REQUEST['name']) and $_REQUEST['name'] != '') {
                $username = trim($_REQUEST['name']);
                $qusername = mysql_real_escape_string($username, $this->db_connection);
            } else {
                $abort = true;
                $errors .= "Username is not valid/missing.<br />\n";
            }
            // Type
            if (isset($_REQUEST['type']) and in_array($_REQUEST['type'], array('standard', 'admin'))) {
                $qtype = mysql_real_escape_string($_REQUEST['type'], $this->db_connection);
            } else {
                $abort = true;
                $errors .= "User type is not valid/missing.<br />\n";
            }
            // Status
            if (isset($_REQUEST['status']) and in_array($_REQUEST['status'], array('active', 'disabled'))) {
                $qstatus = mysql_real_escape_string($_REQUEST['status'], $this->db_connection);
            } else {
                $abort = true;
                $errors .= "User status is not valid/missing.<br />\n";
            }
            // Comment
            if (isset($_REQUEST['comment'])) {
                $comment = trim($_REQUEST['comment']);
                $qcomment = mysql_real_escape_string($comment, $this->db_connection);
            } else {
                $abort = true;
                $errors .= "Comment is not valid/missing.<br />\n";
            }
            
            if (isset($_REQUEST['password'], $_REQUEST['password_confirm']) and 
                $_REQUEST['password'] == $_REQUEST['password_confirm']) {
                $password = trim($_REQUEST['password']);
                $qpassword = mysql_real_escape_string(md5($password), $this->db_connection);
            } else {
                $abort = true;
                $errors .= "Passwords do not match or are missing.<br />\n";
            }
            
            if (!$abort) {
                $query = 'INSERT INTO `users` (`usr_name`, `usr_pass`, `usr_type`, `usr_status`, `usr_comment`)' .
                         "VALUES ('$qusername', '$qpassword', '$qtype', '$qstatus', '$qcomment')";
                $result = $this->db_query($query);
                // TODO: Handle this with an error message
                if ($result == false) { return; }
                
                // Lookup user's id
                $query = 'SELECT `usr_id` FROM `users`' .
                         " WHERE `usr_name` = '$username'";
                $result = $this->db_query($query);
                // TODO: Handle this with an error message
                if ($result == false) { return; }

                $data = mysql_fetch_assoc($result);
                $uid = $data['usr_id'];
                
                // Set page settings
                $page['userinfo']['success'] = true;
                $page['userinfo']['name'] = $username;
                $page['userinfo']['id'] = $uid;
            }
        }
        
        // Set errors
        $page['userinfo']['error'] = $errors;
        
        // Display page
        $page['title'] = 'Add User - User Admin - ViewGit';
        // Header
        require 'templates/header.php';
        $this->display_plugin_template('adduser', false);
        // Footer
        require 'templates/footer.php';
    }
    
    // View user
    private function viewuser($uid) {
        global $conf;
        global $page;
        
        $page['userinfo'] = array();
        
        $quid =  mysql_real_escape_string($uid, $this->db_connection);
        
        // Get user data
        $query = 'SELECT `usr_name`, `usr_type`, `usr_status`, `usr_comment` FROM `users`' .
                 "  WHERE `usr_id` = '$quid'";
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        // Check if user exists
        if (mysql_num_rows($result) == 1) {
            // Collect info
            $page['userinfo']['id'] = $uid;
            $data = mysql_fetch_assoc($result);
            $page['userinfo']['name'] = $data['usr_name'];
            $page['userinfo']['type'] = $data['usr_type'];
            $page['userinfo']['status'] = $data['usr_status'];
            $page['userinfo']['comment'] = $data['usr_comment'];
            
            // Authed Projects
            $page['userinfo']['auth_projects'] = auth_projects_allowed_check($uid);
            
            // Display page
            $page['title'] = 'View User - User Admin - ViewGit';
            // Header
            require 'templates/header.php';
            $this->display_plugin_template('viewuser', false);
            // Footer
            require 'templates/footer.php';
        } else {
            $this->no_user($uid);
        }
    }
    
    // Edit user
    private function edituser($uid) {
        global $conf;
        global $page;
        
        $page['userinfo'] = array();
        $errors = '';
        $read_old = true;
        
        // Escape uid for SQL
        $quid = mysql_real_escape_string($uid, $this->db_connection);
        
        // Check for a submitted form
        if (isset($_REQUEST['submit_user'])) {
            // Form submitted.
            // Check if user exists first
            $query = 'SELECT `usr_name` FROM `users`' .
                     "  WHERE `usr_id` = '$quid'";
            $result = $this->db_query($query);
            // TODO: Handle this with an error message
            if ($result == false) { return; }
            
            if (mysql_num_rows($result) == 1) {
                // It does, process form
                $read_old = false;
                $abort = false;
                
                // Check we have the fields, filter stuff
                // Username
                if (isset($_REQUEST['name']) and $_REQUEST['name'] != '') {
                    $username = trim($_REQUEST['name']);
                    $qusername = mysql_real_escape_string($username, $this->db_connection);
                } else {
                    $abort = true;
                    $errors .= "Username is not valid/missing.<br />\n";
                }
                // Type
                if (isset($_REQUEST['type']) and in_array($_REQUEST['type'], array('standard', 'admin'))) {
                    $qtype = mysql_real_escape_string($_REQUEST['type'], $this->db_connection);
                } else {
                    $abort = true;
                    $errors .= "User type is not valid/missing.<br />\n";
                }
                // Status
                if (isset($_REQUEST['status']) and in_array($_REQUEST['status'], array('active', 'disabled'))) {
                    $qstatus = mysql_real_escape_string($_REQUEST['status'], $this->db_connection);
                } else {
                    $abort = true;
                    $errors .= "User status is not valid/missing.<br />\n";
                }
                // Comment
                if (isset($_REQUEST['comment'])) {
                    $comment = trim($_REQUEST['comment']);
                    $qcomment = mysql_real_escape_string($comment, $this->db_connection);
                } else {
                    $abort = true;
                    $errors .= "Comment is not valid/missing.<br />\n";
                }
                
                // Password
                if (isset($_REQUEST['password_keep']) and $_REQUEST['password_keep'] == 'true') {
                    // Keep old one
                    if (!$abort) {
                        $query = "UPDATE `users` SET `usr_name` = '$qusername', `usr_type` = '$qtype', `usr_status` = '$qstatus', `usr_comment` = '$qcomment'" .
                                 "  WHERE `usr_id` = $quid";
                        $result = $this->db_query($query);
                        // TODO: Handle this with an error message
                        if ($result == false) { return; }
                        
                        $errors = "Updated settings.\n";
                    }
                } else {
                    if (isset($_REQUEST['password'], $_REQUEST['password_confirm']) and 
                        $_REQUEST['password'] == $_REQUEST['password_confirm']) {
                        // Change password
                        $password = trim($_REQUEST['password']);
                        $qpassword = mysql_real_escape_string(md5($password), $this->db_connection);
                        if (!$abort) {
                            $query = "UPDATE `users` SET `usr_name` = '$qusername', `usr_pass` = '$qpassword', `usr_type` = '$qtype', `usr_status` = '$qstatus', `usr_comment` = '$qcomment'" .
                                     "  WHERE `usr_id` = $quid";
                            $result = $this->db_query($query);
                            // TODO: Handle this with an error message
                            if ($result == false) { return; }
                            
                            $errors = "Updated settings.\n";
                        }
                    }
                }
            } else {
                // Invalid user
                $this->no_user($uid);
                die;
            }
        }
        
        // Set errors/msg, if any.
        $page['userinfo']['msg'] = $errors;
        
        // Get current settings
        $query = 'SELECT `usr_name`, `usr_type`, `usr_status`, `usr_comment` FROM `users`' .
                 "  WHERE `usr_id` = '$quid'";
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        // Check if user exists
        if (mysql_num_rows($result) == 1) {
            // Collect info
            $page['userinfo']['id'] = $uid;
            $data = mysql_fetch_assoc($result);
            $page['userinfo']['name'] = $data['usr_name'];
            $page['userinfo']['type'] = $data['usr_type'];
            $page['userinfo']['status'] = $data['usr_status'];
            $page['userinfo']['comment'] = $data['usr_comment'];
            
            // Display page
            $page['title'] = 'Edit User - User Admin - ViewGit';
            // Header
            require 'templates/header.php';
            $this->display_plugin_template('edituser', false);
            // Footer
            require 'templates/footer.php';
        } else {
            $this->no_user($uid);
        }
    }
    
    // Edit user's authed projects
    private function editauth($uid) {
        global $conf;
        global $page;
        
        // Escape uid for SQL
        $quid = mysql_real_escape_string($uid, $this->db_connection);
        
        // Get user info
        $query = 'SELECT `usr_name`, `usr_comment` FROM `users`' .
                 "  WHERE `usr_id` = '$quid'";
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        // Check if user exists
        if (mysql_num_rows($result) == 1) {
            // Yep, collect data
            $page['userinfo']['id'] = $uid;
            $data = mysql_fetch_assoc($result);
            $page['userinfo']['name'] = $data['usr_name'];
            $page['userinfo']['comment'] = $data['usr_comment'];
            
            // Get all projects and projects authed to the user
            $projects = array_keys($conf['projects']);
            $auth_projects = auth_projects_allowed_check($uid);
            
            // Check for submitted form
            if (isset($_REQUEST['submit_auth'])) {
                
                // Build project auth list as given by form
                $form_projects = array();
                foreach(array_merge($projects, $auth_projects) as $project) {
                    if (isset($_REQUEST[str_replace(' ', '_', $project)]) and $_REQUEST[str_replace(' ', '_', $project)] == 'on') {
                        $form_projects[$project] = true;
                    } else {
                        $form_projects[$project] = false;
                    }
                }
                
                // Compare against auth list as given by db
                $auth_diff = array_diff_assoc($form_projects, $this->compare_auth($projects, $auth_projects));
                
                // Update DB to changes made on form
                $projects_disable = array();
                foreach($auth_diff as $project => $enabled) {
                    $qproject = mysql_real_escape_string($project, $this->db_connection);
                    if ($enabled) {
                        // Insert record for project (auths user to the project)
                        $query = 'INSERT INTO `repo_auth`' .
                               "VALUES ('$quid', '$qproject')";
                        $result = $this->db_query($query);
                        // TODO: Handle this with an error message
                        if ($result == false) { return; }
                    } else {
                        // Collect removed projects for the delete query
                        $projects_disable[] = $qproject;
                    }
                }
                
                // Check if we have anything to delete
                if (count($projects_disable) > 0) {
                    // Delete records for projects (deauths user to the project)
                    // all at once
                    $query = 'DELETE FROM `repo_auth`'.
                             '  WHERE';
                    $first = true;
                    // Append projects to the query
                    foreach($projects_disable as $qproject) {
                        if (!$first) {
                            $query .= ' OR';
                        } else {
                            $first = false;
                        }
                        $query .= " (`aut_user_id` = $quid AND `aut_repo_name` = '$qproject')";
                    }
                    // Run delete query
                    $result = $this->db_query($query);
                    // TODO: Handle this with an error message
                    if ($result == false) { return; }
                }
                
                // Reload authed projects
                $auth_projects = auth_projects_allowed_check($uid);
            }
            
            // Prep data for form
            $page['authinfo'] = $this->compare_auth($projects, $auth_projects);
            
            // Display page
            $page['title'] = 'Update Authorized Projects - User Admin - ViewGit';
            // Header
            require 'templates/header.php';
            $this->display_plugin_template('editauth', false);
            // Footer
            require 'templates/footer.php';
            
        } else {
            $this->no_user($uid);
        }
    }
    
    // Delete user
    private function deleteuser($uid) {
        global $conf;
        global $page;
        
        $page['userinfo'] = array();
        
        $quid = mysql_real_escape_string($uid, $this->db_connection);
        
        // Get username
        $query = 'SELECT `usr_name` FROM `users`' .
                 "  WHERE `usr_id` = '$quid'";
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        $page['userinfo']['id'] = $uid;
        
        // Check if user exists
        if (mysql_num_rows($result) == 1) {
            // Collect info
            $data = mysql_fetch_assoc($result);
            $page['userinfo']['name'] = $data['usr_name'];
            
            $page['userinfo']['confirm'] = false;
            
            if (isset($_REQUEST['confirm']) and $_REQUEST['confirm'] == 'Yes') {
                // Delete confirmed!
                $page['userinfo']['confirm'] = true;
                
                $quid =  mysql_real_escape_string($uid, $this->db_connection);
                
                // Delete repo_auth records
                $query = 'DELETE FROM `repo_auth`' .
                         "  WHERE `aut_user_id` = $quid";
                $result = $this->db_query($query);
                // TODO: Handle this with an error message
                if ($result == false) { return; }
                
                // Delete record from users
                $query = 'DELETE FROM `users`' .
                         "  WHERE `usr_id` = $quid";
                $result = $this->db_query($query);
                // TODO: Handle this with an error message
                if ($result == false) { return; }
            }
            
            // Display page
            $page['title'] = 'Delete User - User Admin - ViewGit';
            // Header
            require 'templates/header.php';
            $this->display_plugin_template('deleteuser', false);
            // Footer
            require 'templates/footer.php';
            
        } else {
            $this->no_user($uid);
        }
    }
    
    // Userlist
    private function userlist() {
        global $conf;
        global $page;
        
        $page['userlist'] = array();
        
        // Get user data for the list
        $query = 'SELECT `usr_id`, `usr_name`, `usr_type`, `usr_status` FROM `users`';
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }

        // Process data
        while ($data = mysql_fetch_assoc($result)) {
            $user = array();
            $user['id'] = $data['usr_id'];
            $user['username'] = $data['usr_name'];
            $user['type'] = $data['usr_type'];
            $user['status'] = $data['usr_status'];
            
            $page['userlist'][] = $user;
        }
        
        // Display page
        $page['title'] = 'Userlist - User Admin - ViewGit';
        // Header
        require 'templates/header.php';
        $this->display_plugin_template('userlist', false);
        // Footer
        require 'templates/footer.php';
    }
}
