<?php
/* 

Simple authorisation module for ViewGit - protects web access with pre-configured username/password.

To Use:

1. Copy this file to <viewgitdir>/inc/auth_simple.php
2. Update inc/localconfig.php to use simple auth module:

	$conf['auth_lib'] = 'simple_repo';
	$conf['auth_simplerepo_users'] = array(
		'username1'=>array('password'=>'nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn',
		                   'projects'=>array('project1', 'project2'))
		'username2'=>array('password'=>'nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn',
		                   'projects'=>array('project1', 'project4'))
	);

   where nnn is the md5 of the password for each user.

Tip: to generate the md5, use the login page with username 'md5' and your desired password.  The 
     login will fail but it will show the md5 of the entered password.

Released under AGPLv3 or older.

Developed by Topten Software (Brad Robinson) 
http://www.toptensoftware.com

Modifications by Andrew Hampe to support by-repo perms
*/

function auth_login_check($username, $password)
{
    global $conf;
    
    if (isset($conf['auth_simplerepo_users']))
    {
        foreach($conf['auth_simplerepo_users'] as $user => $data)
        {
            if (strtolower($user) == strtolower($username) and $data['password'] == $password)
            {
                $_SESSION['loginname']=$user;
                return true;
            }
        }
    }
    return false;
}

function auth_check()
{
    global $conf;
    global $page;

    // Setup session
    if (isset($conf['session']))
    {
        // Session Name
        if (isset($conf['session']['name']))
        {
            session_name($conf['session']['name']);
        }
        
        // Cookie Settings
        if (isset($conf['session']['lifetime'], $conf['session']['path'],
                  $conf['session']['domain'], $conf['session']['secure']))
        {
            session_set_cookie_params($conf['session']['lifetime'], $conf['session']['path'],
                                      $conf['session']['domain'], $conf['session']['secure']);
        }
    }
	session_start();
	
	// Check if already signed in.
	if (isset($_SESSION['loginname']))
		return;
	
	// Don't check login by default
	$check_login=false;
	
    // Form submit
	if (isset($_REQUEST['login_action']))
	{
	    // Form submit
		$username=$_REQUEST['username'];
		$password=md5($_REQUEST['password']);
		// Check login
		$check_login=true;
	}
    elseif ($page['action'] == 'rss-log')
    {
        // In case PHP is running as a CGI
        list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) =
          explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        
        // RSS feed
        if (isset($_SERVER['PHP_AUTH_USER']) and $_SERVER['PHP_AUTH_USER'] != '')
        {
            $username=$_SERVER['PHP_AUTH_USER'];
            $password=md5($_SERVER['PHP_AUTH_PW']);
            // Check Login
            $check_login=true;
        }
        else
        {
            // Let client know it can use HTTP auth.
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Basic realm="ViewGit"');
        }
    }
	
	if ($check_login)
	{
	    // Check if login is vaild
		$verified = auth_login_check($username, $password);
		if ($verified)
		    return;
		
		if ($username=="md5")
			$loginmessage="MD5: ".$password;
		else
			$loginmessage="Login Failed";
	}

	$page['title']="Login - ViewGit";


	// Not signed in, display login page
	require('templates/header.php');
?>
	<h2>Login Required</h2>
<?php if (isset($loginmessage)):?>
	<p style="border: 1px solid red; padding: 2px; background: #f77;"><?php echo htmlspecialchars($loginmessage)?></p>
<?php endif ?>
	<form id="login" method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
		<fieldset>
			<label for="username">User Name:</label>
				<input type="text" name="username" /><br />
			<label for="password">Password:</label>
				<input type="password" name="password" /><br />
			<input type="submit" name="login_action" value="Login" />
		</fieldset>
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
    
    $username = $_SESSION['loginname'];
    if (isset($conf['auth_simplerepo_users'][$username]) and
        isset($conf['auth_simplerepo_users'][$username]['projects']) and
	    in_array($project, $conf['auth_simplerepo_users'][$username]['projects']))
    {
        // User has access to the project.
        
        if ($return == true)
        {
            // Return whether or not the user has access
            return true;
        }
		
        // Return silently
		return;
    }
    else 
    {
        // User does not have access to the project.
        
        if ($return == true)
        {
            // Return whether or not the user has access
            return false;
        }
        
        // Set page title
        $page['title'] = "Access Denied - ViewGit";
        
        // Set project name
        if (!isset($page['project']))
        {
            $page['project'] = $project;
        }
        
        // Display error page
	    require('templates/header.php');
	    ?>
	<h2>Access Denied</h2>
	<p style="border: 1px solid red; padding: 2px; background: #f77;">You do not have access to the '<?php echo htmlspecialchars($project)?>' project.</p>
<?php
	    require('templates/footer.php');	
	    die;
    }
}
