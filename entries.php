<?php include_from_template('header.php'); ?>

<?php show_guestbook_add_form(); ?>

<h2>Current Entries</h2>

<p class="entryCount">
Viewing entries <?php show_entries_start_offset(); ?> through <?php show_entries_end_offset(); ?> 
(Total entries: <?php show_entry_count(); ?>)
</p>

<?php show_entries(); ?>

<?php include_from_template('footer.php'); ?>