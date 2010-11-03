<?php

class MarkupPlugin extends VGPlugin
{
	function __construct() {
		global $conf;
		if (isset($conf['markup'])) {
			$this->register_action('render');
			$this->register_hook('pagenav');
		}
	}

	function action($action) {
	    global $page;
	    global $conf;
	    
    	$page['project'] = validate_project($_REQUEST['p']);
    	$page['hash'] = validate_hash($_REQUEST['h']);
    	$page['title'] = "$page[project] - Blob - ViewGit";
    	if (isset($_REQUEST['hb'])) {
    		$page['commit_id'] = validate_hash($_REQUEST['hb']);
    	}
    	else {
    		$page['commit_id'] = 'HEAD';
    	}
    	$page['subtitle'] = "Render";
    
    	$page['path'] = '';
    		$page['path'] = $_REQUEST['f']; // TODO validate?
    
    	// For the header's pagenav
    	$info = git_get_commit_info($page['project'], $page['commit_id']);
    	$page['commit_id'] = $info['h'];
    	$page['tree_id'] = $info['tree'];
    
    	$page['pathinfo'] = git_get_path_info($page['project'], $page['commit_id'], $page['path']);
    
    	$page['data'] = fix_encoding(join("\n", run_git($page['project'], "cat-file blob $page[hash]")));
    
    	$page['lastlog'] = git_get_commit_info($page['project'], 'HEAD', $page['path']);
    	
    	// Render file
    	if (preg_match('/\.md|mkdn?|mdown|markdown$/', $page['path']) > 0) {
    	    // Markdown
    	    include_once("markdown.php");
    	    $page['html_data'] = Markdown($page['data']);
    	}
    	
    	// Display page
    	// Header
        require 'templates/header.php';
        $this->display_plugin_template('render', false);
        // Footer
        require 'templates/footer.php';
	}

	function hook($type) {
		if ($type == 'pagenav') {
			global $page;
			if ($page['action'] == 'viewblob' or $page['action'] == 'render') {
			    $request = array('a' => 'render',
			                     'p' => $page['project'],
			                     'h' => $page['hash'],
			                     'f' => $page['path']);
			    
			    $page['links']['Render'] = $request;
			}
		}
	}
}
