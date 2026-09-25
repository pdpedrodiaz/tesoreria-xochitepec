<?php
/**
 * @file
 * Template for Default Home page panel.
 */
?>

    <div class="modules">
    
  <?php if (!empty($login)): ?>
    <div class="user-login">
      <?php print render($login); ?>
    </div>
  <?php else: ?>
    
  <?php endif; ?>

  
  </div><!--/modules-->


