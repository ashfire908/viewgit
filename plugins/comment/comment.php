<?php
// Comment class file

// Comment class
class Comment {
    function __construct() {
    	// Requires markdown (load on first use)
    	require_once('inc/renders/markdown.php');
    	
        // Initialize properties
        $this->author = new CommentUser();
        $this->posted = new DateTime();
        $this->edit = new CommentEdit();
    }
    
    public $id;
    public $num;
    public $author;
    public $project;
    public $commit;
    public $posted;
    public $edit;
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

// Comment Edit
class CommentEdit {
    function __construct() {
        // Initialize properties
        $this->author = new CommentUser();
        $this->date = new DateTime();
    }
    
    public $count;
    public $date;
    public $author;
}