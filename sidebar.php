<div class="panel">
    <h4><?php echo __('IIIF API Sync'); ?></h4>
    <?php if (empty($task)) : ?>
    <p><?php echo ($thing instanceof Item) ? __('This item has never been synchronized.') : __('This collection has never been synchronized.'); ?></p>
    <?php else: ?>
    <p><?php echo __("Last sync: %s", format_date($task->modified)); ?></p>
    <p><?php echo __("Status: %s", Inflector::titleize(__($task->status))); ?></p>
    <?php endif; ?>
    <form action="<?php echo admin_url(array(), 'iiifapibridge_update') ?>" method="POST">
        <input type="hidden" name="thing_id" value="<?php echo $thing->id; ?>">
        <input type="hidden" name="thing_type" value="<?php echo get_class($thing); ?>">
        <button type="submit" name="action_type" value="push" class="big blue button" style="width: 100%;"><?php echo __('Push up'); ?></button>
    </form>
</div>
