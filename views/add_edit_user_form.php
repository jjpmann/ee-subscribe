
<?php

    function date_dropdown($year_limit, $default, $shortcut_name)
    {
        /*days*/
            $html_output = '';
        $html_output .= '           <select name="'.$shortcut_name.'[day]" id="day_select">'."\n";
        $html_output .= '               <option  value="0">---</option>'."\n";
        for ($day = 1; $day <= 31; $day++) {
            $is_selected = ($default['day'] == $day) ? 'selected="selected"' : '';
            $html_output .= '               <option '.$is_selected.' value='.$day.'>'.$day.'</option>'."\n";
        }
        $html_output .= '           </select>'."\n";

            /*months*/
            $html_output .= '           <select name="'.$shortcut_name.'[month]" id="month_select" >'."\n";
        $months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $html_output .= '               <option  value="0">---</option>'."\n";
        for ($month = 1; $month <= 12; $month++) {
            $is_selected = ($default['month'] == $month) ? 'selected="selected"' : '';
            $html_output .= '               <option '.$is_selected.' value="'.$month.'">'.$months[$month].'</option>'."\n";
        }
        $html_output .= '           </select>'."\n";

            /*years*/
            $html_output .= '           <select name="'.$shortcut_name.'[year]" id="year_select">'."\n";
        $html_output .= '               <option  value="0">---</option>'."\n";
        for ($year = 1900; $year <= (date('Y') - $year_limit); $year++) {
            $is_selected = ($default['year'] == $year) ? 'selected="selected"' : '';
            $html_output .= '               <option '.$is_selected.' value='.$year.'>'.$year.'</option>'."\n";
        }
        $html_output .= '           </select>'."\n";

        $html_output .= '   </div>'."\n";

        return $html_output;
    }

    $attributes = ['id' => 'emma_add_edit_user_form'];
    echo form_open($action_url, $attributes);

    $this->table->set_template($cp_pad_table_template);
    foreach ($fields as $field) {
        $options = [];
        if ($field->options) {
            foreach ($field->options as $key => $val) {
                $options[$val] = $val;
            }
        }
        $shortcut_name = $field->shortcut_name;
        switch ($field->widget_type) {
            case 'text':
                        $this->table->add_row(
                            [
                                lang($field->display_name, $field->shortcut_name),
                                form_input($field->shortcut_name, (isset($member_details->fields->$shortcut_name)) ? $member_details->fields->$shortcut_name : '', 'class="field"'),
                            ]
                        );
                        break;
            case 'radio':
                        $radio = '';
                        //var_dump($options);
                        foreach ($options as $key => $option) {
                            $is_checked = '';
                            if ((isset($member_details->fields->$shortcut_name))) {
                                $is_checked = ($member_details->fields->$shortcut_name == $option) ? 'checked' : '';
                            }
                            $radio .= '<input type="radio" class="form-radio" '.$is_checked.' value="'.$option.'" name="'.$field->shortcut_name.'" id="edit-radio-'.$field->display_name.'">  <label for="edit-radio-'.$field->display_name.'" class="option">'.$option.' </label>';
                        }
                        $this->table->add_row([
                                lang($field->display_name, $field->shortcut_name),
                                $radio,
                            ]
                        );
                        break;
            case 'check_multiple':
                        $checkboxes = '';
                        //var_dump($options);

                        foreach ($options as $option) {
                            $is_checked = '';
                            if ((isset($member_details->fields->$shortcut_name))) {
                                $is_checked = (in_array($option, $member_details->fields->$shortcut_name)) ? 'checked' : '';
                            }
                            $checkboxes .= '<input type="checkbox" class="form-checkbox" '.$is_checked.' value="'.$option.'" name="check['.$option.']" id="edit-check-'.$option.'">  <label for="edit-check-'.$option.'" class="option">'.$option.' </label>';
                        }
                        $this->table->add_row([
                                lang($field->display_name, $field->shortcut_name),
                                $checkboxes,
                            ]
                        );
                        break;
            case 'select multiple':
                        $this->table->add_row([
                                lang($field->display_name, $field->shortcut_name),
                                form_multiselect($field->shortcut_name.'[]', $options, isset($member_details->fields->$shortcut_name) ? $member_details->fields->$shortcut_name : ''),
                            ]
                        );
                        break;
            case 'long':
                        $this->table->add_row([
                                lang($field->display_name, $field->shortcut_name),
                                form_textarea($field->shortcut_name, (isset($member_details->fields->$shortcut_name)) ? $member_details->fields->$shortcut_name : '', 'class="field"'),
                            ]
                        );
                        break;
            case 'date':
                       if ((isset($member_details->fields->$shortcut_name))) {
                           $date = str_replace('@D:', '', $member_details->fields->$shortcut_name);
                           list($year, $month, $day) = explode('-', $date);
                       } else {
                            $year = $month = $day = 0;
                        }
                        $default = [
                            'day'   => $day,
                            'month' => $month,
                            'year'  => $year,
                        ];
                        $date = date_dropdown(0, $default, $field->shortcut_name);
                        $this->table->add_row([
                                lang($field->display_name, $field->shortcut_name),
                                $date,
                            ]
                        );

                        break;
        }
    }
    $this->table->add_row([
            lang('Email', 'emma_email'),
            form_input('emma_email', isset($member_details->email) ? $member_details->email : '', 'class="field"'),
        ]
    );
    $this->table->add_row([
            lang('Default Groups', 'group_list'),
            form_multiselect('group_list[]', $groups_list, $member_group_ids),
        ]
    );
    echo form_hidden('member_id', isset($member_details->member_id) ? $member_details->member_id : '', 'class="field"');
    echo form_hidden('member_status', isset($member_details->status) ? substr($member_details->status, 0, 1) : '', 'class="field"');
    echo $this->table->generate();

?>

	<?=form_submit(['name' => 'submit', 'value' => lang('submit'), 'class' => 'submit'])?>

<?=form_close()?>

		<script>$("#emma_add_edit_user_form").submit(function () {
        var return_value;
        fv_url=(("<?php echo htmlspecialchars_decode($fv_url)?>"));
         $.ajax({
                type:'POST',
                url:fv_url,
                async:false,
                data:$("#emma_add_edit_user_form").serialize(),
                beforeSend: function() {
                    $("#emma_add_edit_user_form input.submit").after('<span id="submit_loading">&nbsp&nbsp;&nbsp&nbsp;<img alt="Loading" src="themes/cp_themes/default/images/indicator.gif"></span>');

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

