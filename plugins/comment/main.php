<?php
// Include comment classes
require_once('comment.php');

class CommentPlugin extends VGPlugin {
    function __construct() {
        global $conf;
        
        if (isset($conf['comment']) and $conf['comment']) {
            // Check for required modules
            if (!isset($conf['auth_lib']) or $conf['auth_lib'] != 'mysql') {
                // We need the mysql module
                die('Comment Plugin requires the mysql auth module to be in use.');
            }
            
            // Register actions and hooks
            $this->register_action('comment');
            $this->register_hook('commit');
        }
    }
    
    // Settings
    private $db_connection;
    
    // Action handler
    function action($action) {
        global $page;
        global $conf;
        
        // Setup page
        $page['title'] = 'Comment - ViewGit';
        $page['subtitle'] = 'Comment';
        
        // Connect to the database
        $this->db_connect();
        
        // Handle request
        switch($_REQUEST['m']) {
            case 'add':
                // Add comment
                $this->event_add_comment();
                break;
            case 'view':
                // View comment
                $this->event_view_comment();
                break;
            case 'edit':
                // Edit comment
                $this->event_edit_comment();
                break;
            case 'delete':
                // Delete comment
                $this->event_delete_comment();
                break;
            default:
                // TODO: Make this a nice error
                die('Invalid comment mode.');
                break;
        }
    }
    
    // Hook handler
    function hook($type) {
        global $page;
        global $conf;
        
        // Connect to the database
        $this->db_connect();
        
        switch ($type) {
            case 'commit':
                $this->event_commit_comments($page['project'], $page['commit_id']);
                break;
            // For the future compare view
            /*case 'compare':
                foreach ($page['commit_ids'] as $commit_id) {
                    $this->commit_comments($commit_id);
                }
                break;
            */
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
            trigger_error('No user database username specified', E_USER_ERROR);
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
    
    // DB Quote
    private function db_quote($input) {
        return mysql_real_escape_string($input, $this->db_connection);
    }
    
    // Get form input
    private function form_input() {
        // Get type of form submission
        if (isset($_REQUEST['comment_submit']) and isset($_REQUEST['comment_text'])) {
            $type = 'submit';
            $text = $_REQUEST['comment_text'];
        } elseif (isset($_REQUEST['comment_preview']) and isset($_REQUEST['comment_text'])) {
            $type = 'preview';
            $text = $_REQUEST['comment_text'];
        } elseif (isset($_REQUEST['comment_delete'])) {
            $type = 'delete';
            $text = null;
        } else {
            $type = 'none';
            $text = null;
        }
        
        return array($type, $text);
    }
    
    // Get comment (by ID)
    private function get_comment($id) {
        // Quote input
        $id = (int) $id;
        
        // Lookup comments
        $query = 'SELECT `com`.`com_user_id`, `com`.`com_project`, `com`.`com_commit`, `com`.`com_posted_date`, `com`.`com_edit_count`, `com`.`com_edit_date`, `com`.`com_edit_user`, `com`.`com_text`, `usr`.`usr_name` AS `author_name`, `usr`.`usr_type` AS `author_type`, `usr_e`.`usr_name` AS `editor_name`, `usr_e`.`usr_type` AS `editor_type` ' .
                 'FROM `comments` AS `com` ' .
                 'LEFT JOIN `users` AS `usr` ON `com`.`com_user_id` = `usr`.`usr_id` ' .
                 'LEFT JOIN `users` AS `usr_e` ON `com`.`com_edit_user` = `usr_e`.`usr_id` ' .
                 "WHERE `com`.`com_id` = $id";
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        // Get row data
        $data = mysql_fetch_assoc($result);
        
        // Get info for next lookup
        $project = $this->db_quote($data['com_project']);
        $commit = $this->db_quote($data['com_commit']);
        // Lookup position in the comments
        $query = 'SELECT `com`.`com_id` ' .
                 'FROM `comments` AS `com` ' .
                 "WHERE `com`.`com_project` = '$project' AND `com`.`com_commit` = '$commit' " .
                 'ORDER BY `com`.`com_id`';
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        // Find the comment in the list
        $cur_comment = 0;
        $last_id = 0;
        while ($last_id != $id) {
            $row = mysql_fetch_assoc($result);
            $last_id = (int) $row['com_id'];
            $cur_comment++;
        }
        
        // Construct a comment from the data
        $comment = new Comment();
        $comment->id = $id;
        $comment->num = $cur_comment;
        $comment->author->id = (int) $data['com_user_id'];
        $comment->author->name = $data['author_name'];
        $comment->author->type = $data['author_type'];
        $comment->project = $data['com_project'];
        $comment->commit = $data['com_commit'];
        $comment->posted = new DateTime($data['com_posted_date']);
        $comment->edit->count = (int) $data['com_edit_count'];
        $comment->edit->date = new DateTime($data['com_edit_date']);
        $comment->edit->author->id = (int) $data['com_edit_user'];
        $comment->edit->author->name = $data['editor_name'];
        $comment->edit->author->type = $data['editor_type'];
        $comment->text = $data['com_text'];
        $comment->render_comment();
        
        return $comment;
    }
    
    // Get comments (by project and hash)
    private function get_comments($project, $hash) {
        // Quote input
        $project = $this->db_quote($project);
        $hash = $this->db_quote($hash);
        
        // Lookup comments
        $query = 'SELECT `com`.`com_id`, `com`.`com_user_id` AS `usr_id`, `com`.`com_posted_date`, `com`.`com_edit_count`, `com`.`com_edit_date`, `com`.`com_edit_user`, `com`.`com_text`, `usr`.`usr_name`, `usr`.`usr_type`, `usr`.`usr_name` AS `author_name`, `usr`.`usr_type` AS `author_type`, `usr_e`.`usr_name` AS `editor_name`, `usr_e`.`usr_type` AS `editor_type` ' .
                 'FROM `comments` AS `com` ' .
                 'LEFT JOIN `users` AS `usr` ON `com`.`com_user_id` = `usr`.`usr_id` ' .
                 'LEFT JOIN `users` AS `usr_e` ON `com`.`com_edit_user` = `usr_e`.`usr_id` ' .
                 "WHERE `com`.`com_project` = '$project' AND `com`.`com_commit` = '$hash' " .
                 'ORDER BY `com`.`com_id`';
        $result = $this->db_query($query);
        // TODO: Handle this with an error message
        if ($result == false) { return; }
        
        $comments = array();
        $cur_comment = 0;
        
        // Go through the comments
        while ($data = mysql_fetch_assoc($result)) {
            // Next comment
            $cur_comment++;
            
            // Construct a comment from the data
            $comment = new Comment();
            $comment->id = (int) $data['com_id'];
            $comment->num = $cur_comment;
            $comment->author->id = (int) $data['com_user_id'];
            $comment->author->name = $data['author_name'];
            $comment->author->type = $data['author_type'];
            $comment->project = $project;
            $comment->commit = $hash;
            $comment->posted = new DateTime($data['com_posted_date']);
            $comment->edit->count = (int) $data['com_edit_count'];
            $comment->edit->date = new DateTime($data['com_edit_date']);
            $comment->edit->author->id = (int) $data['com_edit_user'];
            $comment->edit->author->name = $data['editor_name'];
            $comment->edit->author->type = $data['editor_type'];
            $comment->text = $data['com_text'];
            $comment->render_comment();
            
            // Append comment to array of comments
            $comments[$comment->id] = $comment;
        }
        
        return $comments;
    }
    
    // Add comment to the database
    private function add_comment($user_id, $project, $hash, $text) {
        // Validate and quote data
        $user_id = (int) $user_id;
        validate_project($project);
        $project = $this->db_quote($project);
        validate_hash($hash);
        $hash = $this->db_quote($hash);
        $text = $this->db_quote($text);
        
        // Build database query
        $query = 'INSERT INTO `comments` (`com_user_id`, `com_project`, `com_commit`, `com_posted_date`, `com_text`) ' .
                 "VALUES ($user_id, '$project', '$hash', NOW(), '$text')";
        
        // Run query
        if ($this->db_query($query) == false) {
            return false;
        } else {
            return true;
        }
    }
    
    // Edit comment in the database
    private function edit_comment($user_id, $comment_id, $text) {
        // Validate and quote data
        $user_id = (int) $user_id;
        $comment_id = (int) $comment_id;
        $text = $this->db_quote($text);
        
        // Build database query
        $query = "UPDATE `comments` SET `com_text` = '$text', `com_edit_count` = `com_edit_count` + 1, `com_edit_date` = NOW(), `com_edit_user` = $user_id " .
                 "WHERE `com_id` = $comment_id";
        
        // Run query
        if ($this->db_query($query) == false) {
            return false;
        } else {
            return true;
        }
    }
    
    // Delete comment in the database
    private function delete_comment($comment_id) {
        // Validate and quote data
        $comment_id = (int) $comment_id;
        
        // Build database query
        $query = 'DELETE FROM `comments` ' .
                 "WHERE `com_id` = $comment_id";
        
        // Run query
        if ($this->db_query($query) == false) {
            return false;
        } else {
            return true;
        }
    }
    
    // Event - Add comment
    private function event_add_comment() {
        global $page;
        global $conf;
        
        // Get the project, commit, and user info. Verify the user is authorized.
        if (!isset($_REQUEST['p'])) {
            die('No project given!'); // No project specified
        }
        if (!isset($_REQUEST['h'])) {
            die('No commit given!');  // No commit specified
        }
        // Project
        $project = validate_project($_REQUEST['p']);
        auth_project($project); // TODO: Customized error page for denied comments?
        // Commit
        $commit_id = validate_hash($_REQUEST['h']);
        // User
        $user_id = (int) $_SESSION['loginid'];
        $user_name = $_SESSION['loginname'];
        $user_type = auth_user_type();
        
        // Get form input
        list($form_mode, $comment_text) = $this->form_input();
        
        // Create the comment object
        $comment = new Comment();
        $comment->id = -1;
        $comment->author->id = $user_id;
        $comment->author->name = $user_name;
        $comment->author->type = $user_type;
        $comment->project = $project;
        $comment->commit = $commit_id;
        $comment->text = $comment_text;
        $comment->render_comment();
        
        // Handle form
        switch ($form_mode) {
            case 'submit':
                // Update comment text
                $comment->text = $comment_text;
                $comment->render_comment();
                
                // Submit to database
                $success = $this->add_comment($user_id, $project, $commit_id, $comment_text);
                
                if ($success) {
                    // Display success page
                    $page['title'] = "Added comment - $project - ViewGit";
                    $page['subtitle'] = 'Comment on commit ' . substr($commit_id, 0, 7);
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'add';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_success', false);
                    require 'templates/footer.php';
                } else {
                    // Display failure page
                    $page['title'] = "Failed to add comment - $project - ViewGit";
                    $page['subtitle'] = 'Error';
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'add';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_failure', false);
                    require 'templates/footer.php';
                }
                break;
            case 'preview':
                // Update comment text
                $comment->text = $comment_text;
                $comment->render_comment();
                
                // Configure page
                $page['title'] = "Preview comment - $project - ViewGit";
                $page['subtitle'] = 'Comment on commit ' . substr($commit_id, 0, 7);
                $page['project'] = $project;
                $page['commit_id'] = $commit_id;
                $page['comment'] = $comment;
                $page['comment_mode'] = 'add';
                $page['comment_opt'] = array('show_actions' => false);
                
                // Output page
                require 'templates/header.php'; // Page header
                $this->display_plugin_template('add_preview', false);     // Preview header
                $this->display_plugin_template('comment_display', false); // Display comment
                $this->display_plugin_template('add_edit', false);        // Edit header
                $this->display_plugin_template('comment_form', false);    // Comment form
                $this->display_plugin_template('add_footer', false);      // Add comment footer
                require 'templates/footer.php'; // Page footer
                break;
            default:
                // Configure page
                $page['title'] = "Add comment - $project - ViewGit";
                $page['subtitle'] = 'Comment on commit ' . substr($commit_id, 0, 7);
                $page['project'] = $project;
                $page['commit_id'] = $commit_id;
                $page['comment'] = $comment;
                $page['comment_mode'] = 'add';
                $page['comment_opt'] = array('show_actions' => false);
                
                require 'templates/header.php'; // Page header
                $this->display_plugin_template('add_initial', false);      // Add new comment header
                $this->display_plugin_template('comment_form', false); // Comment form
                $this->display_plugin_template('add_footer', false);   // Add comment footer
                require 'templates/footer.php'; // Page footer
                break;
        }
    }
    
    // Event - View Comment
    private function event_view_comment() {
        global $page;
        global $conf;
        
        // Get the comment ID
        if (!isset($_REQUEST['c'])) {
            die('No comment specified!');
        }
        $id = $_REQUEST['c'];
        
        // Get comment
        $comment = $this->get_comment($id);
        
        // Validate comment and check perms
        $project = validate_project($comment->project);
        auth_project($project); // TODO: Customized error page for denied comments?
        // Commit
        $commit_id = validate_hash($comment->commit);
        
        // Display comment
        $page['title'] = "View comment - $project - ViewGit";
        $page['subtitle'] = 'Comment on commit ' . substr($commit_id, 0, 7);
        $page['project'] = $project;
        $page['commit_id'] = $commit_id;
        $page['comment'] = $comment;
        $page['comment_mode'] = 'view';
        $page['comment_opt'] = array('show_actions' => false);
        
        // Output page
        require 'templates/header.php'; // Page header
        $this->display_plugin_template('view_header', false);     // View comment header
        $this->display_plugin_template('comment_display', false); // Display comment
        $this->display_plugin_template('view_footer', false);     // View comment footer
        require 'templates/footer.php'; // Page footer
    }
    
    // Event - Edit comment
    private function event_edit_comment() {
        global $page;
        global $conf;
        
        // Get the comment ID
        if (!isset($_REQUEST['c'])) {
            die('No comment given!');
        }
        $id = (int) $_REQUEST['c'];
        
        // Get comment
        $comment = $this->get_comment($id);
        
        // Validate comment and check perms
        $project = validate_project($comment->project);
        auth_project($project); // TODO: Customized error page for denied comments?
        // Commit
        $commit_id = validate_hash($comment->commit);
        // User
        $user_id = (int) $_SESSION['loginid'];
        $user_name = $_SESSION['loginname'];
        $user_type = auth_user_type();
        if ($user_id != $comment->author->id and $user_type != 'admin') {
            // TODO: Improve this error message.
            die('You do not have the permissions to edit this comment.');
        }
        
        // Get form input
        list($form_mode, $comment_text) = $this->form_input();
        
        // Handle form
        switch ($form_mode) {
            case 'submit':
                // Update comment text
                $comment->text = $comment_text;
                $comment->render_comment();
                
                // Submit to database
                $success = $this->edit_comment($user_id, $comment->id, $comment_text);
                
                if ($success) {
                    // Display success page
                    $page['title'] = "Edited comment - $project - ViewGit";
                    $page['subtitle'] = 'Comment #' . $comment->num . ' on commit ' . substr($commit_id, 0, 7);
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'edit';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_success', false);
                    require 'templates/footer.php';
                } else {
                    // Display failure page
                    $page['title'] = "Failed to edit comment - $project - ViewGit";
                    $page['subtitle'] = 'Error';
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'edit';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_failure', false);
                    require 'templates/footer.php';
                }
                break;
            case 'preview':
                // Update comment text and edit date
                $comment->text = $comment_text;
                $comment->render_comment();
                $comment->edit->count++;
                $comment->edit->date = new DateTime();
                $comment->edit->author->id = $user_id;
                $comment->edit->author->name = $user_name;
                $comment->edit->author->type = $user_type;
                
                // Configure page
                $page['title'] = "Preview comment - $project - ViewGit";
                $page['subtitle'] = 'Comment #' . $comment->num . ' on commit ' . substr($commit_id, 0, 7);
                $page['project'] = $project;
                $page['commit_id'] = $commit_id;
                $page['comment'] = $comment;
                $page['comment_mode'] = 'edit';
                $page['comment_opt'] = array('show_actions' => false);
                
                // Output page
                require 'templates/header.php'; // Page header
                $this->display_plugin_template('edit_preview', false);    // Preview header
                $this->display_plugin_template('comment_display', false); // Display comment
                $this->display_plugin_template('edit_edit', false);       // Edit header
                $this->display_plugin_template('comment_form', false);    // Comment form
                $this->display_plugin_template('edit_footer', false);     // Add comment footer
                require 'templates/footer.php'; // Page footer
                break;
            default:
                // Configure page
                $page['title'] = "Edit comment - $project - ViewGit";
                $page['subtitle'] = 'Comment #' . $comment->num . ' on commit ' . substr($commit_id, 0, 7);
                $page['project'] = $project;
                $page['commit_id'] = $commit_id;
                $page['comment'] = $comment;
                $page['comment_mode'] = 'edit';
                $page['comment_opt'] = array('show_actions' => false);
                
                require 'templates/header.php'; // Page header
                $this->display_plugin_template('edit_initial', false); // Initial Edit comment header
                $this->display_plugin_template('comment_form', false); // Comment form
                $this->display_plugin_template('edit_footer', false);  // Edit comment footer
                require 'templates/footer.php'; // Page footer
                break;
        }
    }
    
    private function event_delete_comment() {
        global $page;
        global $conf;
        
        // Get the comment ID
        if (!isset($_REQUEST['c'])) {
            die('No comment given!');
        }
        $id = (int) $_REQUEST['c'];
        
        // Get comment
        $comment = $this->get_comment($id);
        
        // Validate comment and check perms
        $project = validate_project($comment->project);
        auth_project($project); // TODO: Customized error page for denied comments?
        // Commit
        $commit_id = validate_hash($comment->commit);
        // User
        $user_id = (int) $_SESSION['loginid'];
        $user_name = $_SESSION['loginname'];
        $user_type = auth_user_type();
        if ($user_id != $comment->author->id and $user_type != 'admin') {
            // TODO: Improve this error message.
            die('You do not have the permissions to delete this comment.');
        }
        
        // Get form input
        list($form_mode, $comment_text) = $this->form_input();
        
        // Handle form
        switch ($form_mode) {
            case 'delete':
                // Submit to database
                $success = $this->delete_comment($comment->id);
                
                if ($success) {
                    // Display success page
                    $page['title'] = "Deleted comment - $project - ViewGit";
                    $page['subtitle'] = 'Comment deleted';
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'delete';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_success', false);
                    require 'templates/footer.php';
                } else {
                    // Display failure page
                    $page['title'] = "Failed to delete comment - $project - ViewGit";
                    $page['subtitle'] = 'Error';
                    $page['project'] = $project;
                    $page['commit_id'] = $commit_id;
                    $page['comment'] = $comment;
                    $page['comment_mode'] = 'delete';
                    $page['comment_opt'] = array('show_actions' => false);
                    
                    // Output header, page, and footer
                    require 'templates/header.php';
                    $this->display_plugin_template('comment_failure', false);
                    require 'templates/footer.php';
                }
                break;
            default:
                // Configure page
                $page['title'] = "Delete comment - $project - ViewGit";
                $page['subtitle'] = 'Comment #' . $comment->num . ' on commit ' . substr($commit_id, 0, 7);
                $page['project'] = $project;
                $page['commit_id'] = $commit_id;
                $page['comment'] = $comment;
                $page['comment_mode'] = 'delete';
                $page['comment_opt'] = array('show_actions' => false);
                
                require 'templates/header.php'; // Page header
                $this->display_plugin_template('delete_header', false); // Delete comment header
                $this->display_plugin_template('comment_form', false);  // Comment form
                $this->display_plugin_template('delete_footer', false); // Delete comment footer
                require 'templates/footer.php'; // Page footer
                break;
        }
    }

    // Display comments for a commit
    private function event_commit_comments($project, $hash) {
        global $page;
        global $conf;
        
        // Get comments
        $comments = $this->get_comments($project, $hash);
        
        // Create blank comment for form
        $form_comment = new Comment();
        $form_comment->id = -1;
        $form_comment->project = $project;
        $form_comment->commit = $hash;
        
           // Output comments list
           $page['comment_opt'] = array('show_actions' => true);
           $this->display_plugin_template('commit_header', false);
           foreach ($comments as $comment) {
               $page['comment'] = $comment;
               $this->display_plugin_template('comment_display', false);
           }
           // Set form comment, display form
           $page['comment'] = $form_comment;
           $page['comment_mode'] = 'add_inline';
           $this->display_plugin_template('commit_footer', false);
           $this->display_plugin_template('comment_form', false);
    }
}
