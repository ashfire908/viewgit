<?php
/* 

MySQL authorisation module for ViewGit - protects web access with username/password from DB.

Originally based off of auth_simple auth module

Released under AGPLv3 or older.

Developed by Topten Software (Brad Robinson) 
http://www.toptensoftware.com

Modifications by Andrew Hampe to support by-repo perms and MySQL support
*/

function auth_login_check($username, $password)
{
    global $conf;
    
    // Connect to database
    $db_connection = auth_dbconnect();
    if ($db_connection == false) { return false; }
    
    // Query database
    $quser = mysql_real_escape_string($username, $db_connection);
    $query = "SELECT `usr_id`, `usr_name`, `usr_pass` FROM `users`".
             "  WHERE `usr_name` = '$quser' AND `usr_status` = 'active'";
    $output = auth_dbquery($query, $db_connection);
    if ($output == false) { return false; }
    
    // Check password
    if (mysql_num_rows($output) == 1) {
        $data = mysql_fetch_array($output, MYSQL_ASSOC);
        if ($data['usr_pass'] == $password) {
            return array(true, $data['usr_name'], $data['usr_id']);
        }
    }
    return array(false, null, null);
}

function auth_project_check($uid, $project)
{
    global $conf;
    
    // Connect to database
    $db_connection = auth_dbconnect();
    if ($db_connection == false) { return false; }
    
    // Query database
    $quid = mysql_real_escape_string($uid, $db_connection);
    $qproject = mysql_real_escape_string($project, $db_connection);
    $query = "SELECT * FROM `repo_auth`" .
             "  WHERE `aut_user_id` = $uid AND `aut_repo_name` = '$project'";
    $output = auth_dbquery($query, $db_connection);

    // Check access
    if ($output != false and mysql_num_rows($output) == 1) { return true; }
    return false;
}

function auth_projects_allowed_check($uid)
{
    global $conf;
    
    // Connect to database
    $db_connection = auth_dbconnect();
    if ($db_connection == false) { return false; }
    
    // Query database
    $quid = mysql_real_escape_string($uid, $db_connection);
    $query = "SELECT `aut_repo_name` FROM `repo_auth`" .
             "  WHERE `aut_user_id` = $uid";
    $output = auth_dbquery($query, $db_connection);

    // Check access
    if ($output != false) {
        $repos = array();
        while ($row = mysql_fetch_array ($output, MYSQL_ASSOC)) {
            $repos[] = $row['aut_repo_name'];
        }
        return $repos;
    }
    return array();
}

function auth_user_type_check($uid)
{
    global $conf;
        
    // Connect to database
    $db_connection = auth_dbconnect();
    if ($db_connection == false) { return false; }
    
    // Query database
    $quid = mysql_real_escape_string($uid, $db_connection);
    $query = "SELECT `usr_type` FROM `users`".
             "  WHERE `usr_id` = '$quid'";
    $output = auth_dbquery($query, $db_connection);
    if ($output == false) { return false; }
    
    // Get user type
    if (mysql_num_rows($output) == 1) {
        $data = mysql_fetch_array($output, MYSQL_ASSOC);
        if ($data['usr_pass'] == $password) {
            return $data['usr_type'];
        }
    }
    return false;
}

function auth_check()
{
    global $conf;
    global $page;

    // Setup session
    if (isset($conf['session'])) {
        // Session Name
        if (isset($conf['session']['name'])) {
            session_name($conf['session']['name']);
        }
        
        // Cookie Settings
        if (isset($conf['session']['lifetime'], $conf['session']['path'],
                  $conf['session']['domain'], $conf['session']['secure'])) {
            session_set_cookie_params($conf['session']['lifetime'], $conf['session']['path'],
                                      $conf['session']['domain'], $conf['session']['secure']);
        }
    }
    
    // Start session
	session_start();
	
	// Check if already signed in.
	if (isset($_SESSION['loginname'])) {
		return;
	}
	
	// Don't check login by default
	$check_login = false;
	
    // Form submit
	if (isset($_REQUEST['login_action'])) {
	    // Form submit
		$username = $_REQUEST['username'];
		$password = md5($_REQUEST['password']);
		// Check login
		$check_login = true;
	} elseif ($page['action'] == 'rss-log') {
        // In case PHP is running as a CGI
        list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
          explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        
        // RSS feed
        if (isset($_SERVER['PHP_AUTH_USER']) and $_SERVER['PHP_AUTH_USER'] != '') {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = md5($_SERVER['PHP_AUTH_PW']);
            // Check Login
            $check_login = true;
        } else {
            // Let client know it can use HTTP auth.
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="ViewGit"');
        }
    }
	
	if ($check_login) {
	    // Check if login is vaild
		list($verified, $user, $uid) = auth_login_check($username, $password);
		if ($verified) {
		    $_SESSION['loginname'] = $user;
		    $_SESSION['loginid'] = $uid;
		    return;
		}
		
		if ($username == "md5") {
			$loginmessage = "MD5: ".$password;
		} else {
			$loginmessage = "Login Failed";
		}
	}

	$page['title']="Login - ViewGit";


	// Not signed in, display login page
	require('templates/header.php');
?>
	<h2>Login Required</h2>
<?php if (isset($loginmessage)):?>
	<p style="border:1px solid red; padding:2px; background:#f77;"><?php echo htmlspecialchars($loginmessage)?><p>
<?php endif ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    <table>
        <tr>
            <td width="100pt">User Name:</td>
            <td><input type="text" name="username" id="username"></td>
        </tr>
        <tr>
            <td>Password:</td>
            <td><input type="password" name="password"></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="login_action" value="Login"></td>
        </tr>
    </table>
    </form>
	<script type="text/javascript">
	document.getElementById("username").focus();
	</script>

<?php
	require('templates/footer.php');	
	die;
}

function auth_project($project, $return = false)
{
    global $conf;
    global $page;
    
    $uid = $_SESSION['loginid'];
    
    if (auth_project_check($uid, $project)) {
        // User has access to the project.
        if ($return == true) {
            // Return whether or not the user has access
            return true;
        }
		
        // Return silently
		return;
    } else {
        // User does not have access to the project.
        if ($return == true) {
            // Return whether or not the user has access
            return false;
        }
        
        // Set page title
        $page['title'] = "Access Denied - ViewGit";
        
        // Set project name
        if (!isset($page['project'])) {
            $page['project'] = $project;
        }
        
        // Display error page
	    require('templates/header.php');
	    ?>
	<h2>Access Denied</h2>
	<p style="border:1px solid red; padding:2px; background:#f77;">You do not have access to the '<?php echo htmlspecialchars($project)?>' project.<p>
<?php
	    require('templates/footer.php');	
	    die;
    }
}

function auth_projects_allowed()
{
    global $conf;
    
    $uid = $_SESSION['loginid'];
    return auth_projects_allowed_check($uid);
}

function auth_user_type()
{
    global $conf;
    
    $uid = $_SESSION['loginid'];
    return auth_user_type_check($uid);
}

function auth_dbconnect()
{
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
            return $connection;
        }
    }
}

function auth_dbquery($query, $db_connection)
{
    $query_output = mysql_query($query, $db_connection);
    if ($query_output == false) {
        trigger_error('User database query failed', E_USER_ERROR);
        return false;
    } else {
        return $query_output;
    }
}
