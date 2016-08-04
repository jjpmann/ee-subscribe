
<?php
$attributes = ['id' => 'emma_add_edit_group_form'];
echo form_open($action_url, $attributes);
$this->table->set_template($cp_pad_table_template);
/*$this->table->set_heading(
    array('data' => lang('preference'), 'style' => 'width:50%;'),
    lang('setting')
);*/
$this->table->add_row([
        lang('Group Name', 'Group Name'),
        form_input('group_name', $group_name, 'class="field"'),
    ]);
if (!$edit) {
    $this->table->add_row([
            lang('Make this a Test Group', 'test_group'),
            form_checkbox('test_group', '1'),
        ]);
}

echo $this->table->generate();
?>
<?=form_submit(['name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'])?>
<?=form_close()?>
        <script>$("#emma_add_edit_group_form").submit(function () {
        var return_value;
        fv_url=(("<?php echo htmlspecialchars_decode($fv_url)?>"));
         $.ajax({
                type:'POST',
                url:fv_url,
                async:false,
                data:$("#emma_add_edit_group_form").serialize(),
                beforeSend: function() {
                    $("#emma_add_edit_group_form input.submit").after('<span id="submit_loading">&nbsp&nbsp;&nbsp&nbsp;<img alt="Loading" src="themes/cp_themes/default/images/indicator.gif"></span>');

                },      
                complete: function() {
                   $('#submit_loading').remove();
                },                  
                success: function(msg){      
                    if(msg==1)
                    {
                        return_value=true;
                    }
                    else
                    {
                        var data = jQuery.parseJSON(msg);
                        var error_msg='';
                        $.each(data, function(key, value){
                            error_msg +=value+"<br>";
                           // $("#"+key).addClass('errorInput');
                        });                        
                        $.ee_notice(error_msg,{"type" : "error"});
                        return_value=false;
                    }
                }
          });         
          return return_value;      

        });
        </script>