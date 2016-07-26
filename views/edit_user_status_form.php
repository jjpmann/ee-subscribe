<?php
$attributes = array('id' => 'emma_edit_user_status_form');
echo form_open($action_url,$attributes);

	$this->table->add_row(array(
			lang('Status', 'emma_user_status'),
			form_dropdown('emma_user_status', array('a' => lang('Active'), 'e' => lang('Error'),'o' => lang('Opt-Out')), $user_status),
		)
	);
    
echo $this->table->generate();

?>

	<?=form_submit(array('name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'))?>

<?=form_close()?>    