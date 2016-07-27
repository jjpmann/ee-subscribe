<?php

class Subscribe_freeform_ft extends Freeform_base_ft
{
    public  $info   = array(
        'name'          => 'Subscribe',
        'version'       => '1.0',
        'description'   => 'Allow to dynamically signup for Real Magnet lists and Others'
    );

    public $show_label = FALSE;

    // our fields to match emma fields
    protected $fields = array(
        // Emma         =>FreeForm     =>  
        'name_first'    => 'first_name',
        'name_last'     => 'last_name'
    );

    protected $text = 'Join our mailing list';

    public function __construct ()
    {
        parent::__construct();
        ee()->load->model('subscribe_model');
    }

    public function display_settings ($data)
    {
        $list = isset($data['list']) ? $data['list'] : false;
        $type = isset($data['type']) ? $data['type'] : false;
        $text = isset($data['text']) ? $data['text'] : false;
        $form = isset($data['form']) ? $data['form'] : false;

        $groups =  ee()->subscribe_model->lists();

        $options = array();

        foreach ($groups as $id => $group) {
            $options[$group->id] = $group->name;
        }

        ee()->table->add_row(
           'List <div class="subtext">Selet the list users will sign up to.</div>',
            form_dropdown('subscribe_list', $options, $list)
        );

        ee()->table->add_row(
            'Type of Signup <div class="subtext">
                <strong>Always</strong> - user will automatically be added to the list<br>
                <strong>Opt-In</strong> - user will need to Opt-In to be added to the list
                </div>',
            '<label style="padding:0 5px">Always</label>' . 
                form_radio('subscribe_type', 'always', $type == 'always') . 
            '<label style="padding:0 5px">Opt-In</label>' . 
                form_radio('subscribe_type', 'opt-in', $type != 'always')
        );

        ee()->table->add_row(
           'Opt-In Text <div class="subtext">Displayed with Checkbox if Opt-In is selected</div>',
            form_input('subscribe_opt-in_text', $text)
        );

        ee()->table->add_row(
           'Signup Form Id<div class="subtext">Will ensure that new subscribers receive any signup-based trigger emails associated with this signup form (the standard plaintext confirmation email will not be sent).</div>',
            form_input('subscribe_form_id', $form)
        );
        
    }

    public function save_settings ()
    {

        $list = ee()->input->post('subscribe_list');
        $type = ee()->input->post('subscribe_type');
        $text = ee()->input->post('subscribe_opt-in_text');
        $form = ee()->input->post('subscribe_form_id');
     
        return array(
            'list'  => $list,
            'type'  => $type,
            'text'  => $text,
            'form'  => $form,
        );
    }

    public function display_composer_field ($data = null)
    {
        $s = $this->settings;
        $type = isset($s['type']) ? $s['type'] : 'opt-in';

        if ($type == 'always') {
            return 'This field is set to "Always" add user to the list, It will not display any code on the form.';
        }

    }

    public function save ($data)
    {
        
        // Does not fire on opt-in if checkbox is not selected
        $fields = ee()->subscribe_model->getFields();

        $settings   = $this->settings;

        $groups[]   = $settings['list'];
        $form       = $settings['form'];

        //echo "<pre>".__FILE__.'<br>'.__METHOD__.' : '.__LINE__."<br><br>"; var_dump( $data, $fields, $settings ); exit;

        $email      = ee()->input->post('email');
        $user       = array();

        foreach ($fields as $key => $field) {
            $v = $field->id;
            
            $user[$v] = ee()->input->post($v);

            if (isset($this->fields[$v])) {
                $n = $this->fields[$v];
                $user[$field->id] = ee()->input->post($n);
            }
        }

        $add = true;

        // is user in the system ???
        $return = 'Always: ';
        if ($settings['type'] == 'opt-in') {
            $return = 'Opt-in: ';
            $opt = ee()->input->post($this->field_name.'_opt-in');
            if ($opt !== 'y') {
                // Does not fire on opt-in if checkbox is not selected
                $return .= 'False';
                $add = false;
            }
        }


        if ($add && $settings['entry_id'] == 0) {
            // new user
            $response = ee()->subscribe_model->signup($user, $groups);

            echo "<pre>".__FILE__.'<br>'.__METHOD__.' : '.__LINE__."<br><br>"; var_dump( $response ); exit;
            

            if (isset($response->member_id)) {
                $return .= "Added ({$response->member_id})";
            } else {
                $return .= 'Failed';
            }
        }

        return $return;

    }

    public function display_entry_cp ($data)
    {
        return $data;
    }

    public function display_field ($data)
    {

        if (ee()->input->get('module') == 'freeform' && ee()->input->get('method') == 'edit_entry') {
            
            $pattern = '/Added \((\d+)\)/';
            if (preg_match($pattern, $data, $matches)) {
                $id = $matches[1];
                $link  = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=subscribe'.AMP.'method=subscribe_add_edit_user_form'.AMP.'group_id=0'.AMP.'id='.$id;
                return '<a href="'.$link.'" target="_blank">'.$data.'</a>';
            }
            return $data;
        }

        $s = $this->settings;
        $type = (isset($s['type']) && $s['type']) ? $s['type'] : 'opt-in';
        $text = (isset($s['text']) && $s['text']) ? $s['text'] : $this->text;

        if ($type == 'always') {
            return form_hidden($this->field_name, 'always');
        }

        $id = 'freeform_field_' . $this->field_id;

        return form_hidden($this->field_name, 'opt-in'). 
            form_checkbox(array(
            'name'  => $this->field_name . '_opt-in',
            'id'    => $id,
            'value' => 'y',
            'class' => 'form__check',
        )). form_label($text, $id, array(
            'class' => 'form__label form__label--check'
        )) ;
    }
}
