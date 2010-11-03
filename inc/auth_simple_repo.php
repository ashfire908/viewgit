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

Mod to support by-repo perms
*/

function auth_check()
{
global $conf;
global $page;

	session_start();
	
	// Already signed in?
	if (isset($_SESSION['loginname']))
		return;

	// Form submit?
	if (isset($_REQUEST['login_action']))
	{
		$username=$_REQUEST['username'];
		$password=md5($_REQUEST['password']);
		if (isset($conf['auth_simplerepo_users'][$username]) && isset($conf['auth_simplerepo_users'][$username]['password'])
		    && $conf['auth_simplerepo_users'][$username]['password']==$password)
		{
			$_SESSION['loginname']=$username;
			return;
		}
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

function auth_project($project)
{
global $conf;
global $page;
    
    $username = $_SESSION['loginname'];
    if (isset($conf['auth_simplerepo_users'][$username]) and
        isset($conf['auth_simplerepo_users'][$username]['projects']) and
	    in_array($project, $conf['auth_simplerepo_users'][$username]['projects']))
    {
		// User has access to the project.
		return;
    }
    else 
    {
        // User does not have access to the project.
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
	<p style="border:1px solid red; padding:2px; background:#f77;">You do not have access to the '<?php echo htmlspecialchars($project)?>' project.<p>
<?php
	    require('templates/footer.php');	
	    die;
    }
}
