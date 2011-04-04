<?php
// Comment class file

// Comment class
class Comment {
    function __construct() {
    	// Requires markdown (load on first use)
    	require_once('inc/renders/markdown.php');
    	
        // Initialize properties
        $this->user = new CommentUser();
        $this->posted = new DateTime();
        $this->edited = new DateTime();
    }
    
    public $id;
    public $num;
    public $user;
    public $project;
    public $commit;
    public $posted;
    public $edited;
    public $text;
    public $render;
    
    // Render comment
    function render_comment() {
        $this->render = Markdown($this->text);
    }
}

// Comment User
class CommentUser {
    public $id;
    public $name;
    public $type;
}
