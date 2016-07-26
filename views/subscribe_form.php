<?php 
    if($message)
    {?>

    <div> <span style="color:<?php echo $message_color?>"><?php echo $message?></span>
<?php }  ?>
<style type="text/css">
        #emma_subscribe_form { padding: 4px; margin: 0;  }
        #emma_subscribe_form label { padding: 4px 0; text-align: left; }
        #emma_subscribe_form input[type="submit"] { background-color: #ECECEC; border: 1px solid #B4B4B4; text-transform: uppercase; padding: 4px 7px; margin: 7px 0 5px 0; text-align: center; }
        #emma_subscribe_form input[type="submit"]:hover { background-color: #CECECE; }
        #emma_subscribe_form input[type="text"] { border: 1px solid; color: #565656; width: 95%; padding: 4px; }
        #emma_subscribe_form input[type="email"] { border: 1px solid; color: #565656; width: 95%; padding: 4px; }
        #emma_subscribe_form select { border: 1px solid #DCDCDC; color: #565656; width: 98%; padding: 4px; }
        #emma_subscribe_form select option { padding: 2px 0; }
</style>
<?php

    $attributes = array('id' => 'emma_subscribe_form');
    echo form_open($action_url,$attributes);
    $tmpl = array ( 'table_open'  => '<table width="100%" border="1" cellpadding="4px" cellspacing="1"' );
    $this->table->set_template($tmpl);
    $this->table->add_row(array(
        '<label>Name</label>','<input type="text" name="name" id="name"/>'));
    //style="color: #565656; width: 95%; padding: 4px;" />'));
    $this->table->add_row(array('<label>Email*</label>','<input type="email" name="email" id="email" required="true" />'));
    echo $this->table->generate();
    #echo form_hidden('emma_member_id', $member_id, 'class="field"');
    
    echo form_hidden('emma_subscribe', '1', 'class="field"')
?>
	<?=form_submit(array('name' => 'submit', 'value' => lang('Submit')))?>
<?=form_close()?>    
