<?php
global $page;
?><h1>User settings</h1>

<?php
if (count($page['useradmin']['errors']) > 0) {
    echo '  <p style="border: 1px solid red; padding: 2px; background: #f77;">' . implode("<br />\n", $page['useradmin']['errors']) . "</p>\n\n";
}
if (count($page['useradmin']['messages']) > 0) {
    echo '  <p style="border: 1px solid #999; padding: 2px; background: #ffe;">' . implode("<br />\n", $page['useradmin']['messages']) . "</p>\n\n";
}
?><h2><?php echo $page['user']->name; ?></h2>

  <dl class="viewuser">
    <dt>Type</dt>
      <dd><?php echo $page['user']->type; ?></dd>

    <dt>Status</dt>
      <?php
switch ($page['user']->status) {
    case 'active':
        ?><dd class="active_user"><?php
        break;
    case 'disabled':
        ?><dd class="disabled_user"><?php
        break;
    default:
        ?><dd><?php
        break;
} echo $page['user']->status; ?></dd>

    <dt>Comment</dt>
      <dd><?php echo $page['user']->comment; ?></dd>
    
    <br style="clear: left;" />
  </dl>
  
  <h2>Authorized Projects</h2>
    <ul>
<?php foreach($page['user']->projects as $project) { ?>
      <li><a href="<?php echo makelink(array('a' => 'summary', 'p' => $project)); ?>"><?php echo $project; ?></a></li>
<?php }?>
    </ul>

  <p>
     <a href="<?php echo makelink(array('a' => 'user', 'm' => 'changepass')); ?>" title="Change password" class="change_pass">Change password</a>
  </p>
