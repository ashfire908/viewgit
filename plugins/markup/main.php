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
        $page['path'] = $_REQUEST['f'];
        $page['title'] = "$page[project] - Blob - ViewGit";
        if (isset($_REQUEST['hb'])) {
            $page['commit_id'] = validate_hash($_REQUEST['hb']);
        }
        else {
            $page['commit_id'] = 'HEAD';
        }
        $page['subtitle'] = "Render";
    
        // For the header's pagenav
        $info = git_get_commit_info($page['project'], $page['commit_id']);
        $page['commit_id'] = $info['h'];
        $page['tree_id'] = $info['tree'];
        $page['lastlog'] = $info;
        
        $page['pathinfo'] = git_get_path_info($page['project'], $page['commit_id'], $page['path']);
        
        $page['data'] = fix_encoding(join("\n", run_git($page['project'], "cat-file blob $page[hash]")));
        
        //$page['lastlog'] = git_get_commit_info($page['project'], 'HEAD', $page['path']);
        
        // Render file
        switch ($this->match_file($page['path'])) {
            case 'markdown': // Markdown
                include_once('renders/markdown.php');
                $page['html_data'] = Markdown($page['data']);
                break;
            case 'textile':  // Textile
                include_once('renders/textile.php');
                $textile = new Textile();
                $page['html_data'] = $textile->TextileThis($page['data']);
                break;
            default:
                $page['html_data'] = $page['data'];
                break;
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
            if (($page['action'] == 'viewblob' or $page['action'] == 'render')
                and $this->match_file($page['path'])) {
                $request = array('a' => 'render',
                                 'p' => $page['project'],
                                 'h' => $page['hash'],
                                 'hb' => $page['commit_id'],
                                 'f' => $page['path']);
                
                $page['links']['Render'] = $request;
            }
        }
    }
    
    function match_file($path) {
        if (preg_match('/\.md|mkdn?|mdown|markdown$/', $path) > 0) {
            return 'markdown';
        } elseif (preg_match('/\.textile$/', $path) > 0) {
            return 'textile';
        } else {
            return false;
        }
    }
}
