<?php
// Comment class file

// Requires markdown
require_once('markdown.php');

// Comment class
class Comment {
    function __construct() {
        // Initliaze properties
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
