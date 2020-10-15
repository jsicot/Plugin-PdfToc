<div id="table-of-content">
    <?php if ($toc == ''): ?>
    <p><?php echo __('No table of content.'); ?></p>
    <?php else: ?>
     	<?php if(strlen($toc) > 8) : ?>
        <div id='ToCbutton' title='<?php echo __('Show/hide toc bar'); ?>' class='closed'></div>
        <div id='ToCmenu'>
            <h2><?php echo __('Table of Contents'); ?></h2>
            <?php echo $toc; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<!-- Spécificités Bordeaux 3 pour masquer la table des matières par défaut -->
<script type='text/javascript'>
$(document).ready(
	function()
	{
		$("#ToCbutton").css("left", 0);
		$("#ToCbutton").attr("class", "close");
		$("#ToCmenu").css("left", -$("#ToCmenu").width());
	}
);
</script>
