<?php

class LogoutPlugin extends VGPlugin
{
    function __construct() {
        global $conf;
        if (isset($conf['logout'])) {
            $this->register_action('logout');
            $this->register_hook('nav');
        }
    }

    function action($action) {
        global $page;
        global $conf;
        
        if (isset($_SESSION['loginname'])) {
            // Log user out
            // Unset all of the session variables.
            $_SESSION = array();
            
            // Delete the session cookie.
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy the session.
            session_destroy();
        }
        
        // Show logout page
        $page['title'] = "Logged out - ViewGit";
        // Header
        require 'templates/header.php';
        $this->display_plugin_template('logged_out', false);
        // Footer
        require 'templates/footer.php';
    }

    function hook($type) {
        if ($type == 'nav' and isset($_SESSION['loginname'])) {
            $this->output('<div id="logout">Logged in as ' . $_SESSION['loginname'] .
                          '. <a href="' . makelink(array('a' => 'logout')) .
                          '" title="Logout">Logout</a></div>');
        }
    }
}
