<?php

/**
 * @category    Modules
 *
 * @author      Jerry Price
 *
 * @link        https://github.com/jjpmann
 */
class Subscribe_mcp
{
    public $base;           // the base url for this module
    public $form_base;      // base url for forms
    public $module_name = 'subscribe';

    public $settings = [];

    public $settings_exist = 'y';

    public function __construct($switch = true)
    {
        // Make a local reference to the ExpressionEngine super object

        $this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
        $this->form_base = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module='.$this->module_name;
        ee()->cp->set_right_nav([
                    'settings'               => $this->base.AMP.'method=settings',
                    'subscribe_list_manage'  => $this->base.AMP.'method=subscribe_lists',
                   // 'subscribe_stats'    => $this->base.AMP.'method=subscribe_stats',
                ]);
        ee()->load->model('subscribe_model');
        // uncomment this if you want navigation buttons at the top
/*      ee()->cp->set_right_nav(array(
                'home'          => $this->base,
                'some_language_key' => $this->base.AMP.'method=some_method_here',
            ));
*/
    }

    public function index()
    {
        return $this->settings();
    }

    public function settings()
    {
        //$this->_permissions_check();
        ee()->load->library('table');

        $vars = ['action_url' => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=save_settings',
        ];

        ee()->view->cp_page_title = lang('subscribe_settings');
        //ee()->cp->set_variable('cp_page_title', lang('subscribe_settings'));

        ee()->view->cp_breadcrumbs = [$this->base => lang('Subscribe')];

        //ee()->cp->set_variable('cp_breadcrumbs', array(
        //  $this->base => lang('Subscribe')));


        $vars['realmagnet_username'] = env('REALMAGNET_USERNAME', ee()->config->item('realmagnet_username'));
        $vars['realmagnet_password'] = env('REALMAGNET_PASSWORD', ee()->config->item('realmagnet_password'));



        $vars['subscribe_api_key'] = (ee()->config->item('subscribe_api_key'));
        $vars['subscribe_username'] = (ee()->config->item('subscribe_username'));
        $vars['subscribe_password'] = (ee()->config->item('subscribe_password'));
        $vars['fv_url'] = $this->base.AMP.'method=subscribe_settings_validation';

        return ee()->load->view('settings', $vars, true);
    }

    public function subscribe_settings_validation()
    {
        ee()->load->library('form_validation');
        ee()->form_validation->set_rules('subscribe_api_key', 'Subscribe Api Key', 'required');
        ee()->form_validation->set_rules('subscribe_username', 'Subscribe Username', 'required');
        ee()->form_validation->set_rules('subscribe_password', 'Subscribe Password', 'required');
        $valid_form = ee()->form_validation->run();
        if ($valid_form) {
            echo 1;
            exit();
        } else {
            echo json_encode(ee()->form_validation->_error_array);
            exit();
        }
    }

    public function save_settings()
    {
        $insert['subscribe_api_key'] = ee()->input->post('subscribe_api_key');
        $insert['subscribe_username'] = ee()->input->post('subscribe_username');
        $insert['subscribe_password'] = ee()->input->post('subscribe_password');

        ee()->config->_update_config($insert);


        ee()->session->set_flashdata('message_success', lang('settings_updated'));

        ee()->functions->redirect($this->base.AMP.'method=settings');
    }

    public function subscribe_lists()
    {
        ee()->load->library('table');
        $rows = $groups = [];

        if (!ee()->subscribe_model->check()) {
            return ee()->load->view('error', [], true);
        }

        $groups = ee()->subscribe_model->lists();

        $i = 1;
        if ($groups) {
            foreach ($groups as $group) {
                $first_day = date('Y-m').'-01';
                $first_day_time = strtotime($first_day); //first day of month

            $attr = [
                'onclick' => "return confirm('Are you sure to Delete this Group?')",
            ];
                $actions = [
              anchor($this->base.AMP.'method=subscribe_add_edit_group_form&id='.$group->id, lang('Rename')),
              anchor($this->base.AMP.'method=subscribe_group_delete_submit&id='.$group->id.'&group_id='.$group->id, lang('Delete'), $attr),
              ee()->config->item('subscribe_default_group') != $group->id ? anchor($this->base.AMP.'method=subscribe_default_group_submit&id='.$group->id, lang('Set as default')) : '',
            ];
                $rows[] = [
              $i,
              anchor($this->base.AMP.'method=subscribe_group_details&id='.$group->id, $group->name),
              //$group->active_count+$group->optout_count+$group->error_count,
              //$group->active_count,
              //$group->optout_count,
             // $new_subscriber_count,
             // '',//($group->group_type=='g')?lang('Regular'):lang('Test'),
              '', //implode(' | ', $actions),
            ];
                $i++;
            }
        }
        $header = [
            lang('No:'),
            lang('Name'),
        //    lang('Total Users'),
        //    lang('Active'),
        //    lang('Opt out'),
        //    lang('Type'),
            lang('Actions'),
        ];
        ee()->view->cp_page_title = lang('Lists');
        //ee()->cp->set_variable('cp_page_title', lang('Subscribe Lists'));

        ee()->view->cp_breadcrumbs = [$this->base => lang('Subscribe')];
        //ee()->cp->set_variable('cp_breadcrumbs', array(
        //  $this->base => lang('Subscribe')));

        $add_group_anchor = anchor($this->base.AMP.'method=subscribe_add_edit_group_form', lang('Add a New Group'));
        $vars = ['rows'                  => $rows,
                      'header'           => $header,
                      'add_group_anchor' => $add_group_anchor,
        ];

        return ee()->load->view('lists', $vars, true);
    }

    public function subscribe_add_edit_group_form()
    {
        ee()->load->library('table');
        if ($group_id = ee()->input->get('id')) {
            $title = lang('Edit Group');
            $group_info = ee()->subscribe_model->getSubscribeGroupInfo($group_id);
            $vars = [
                    'action_url'           => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=subscribe_edit_group_form_submit&id='.$group_id,
                    'subscribe_group_name' => $group_info->group_name,
                    'edit'                 => 1,
            ];
        } else {
            $title = lang('Add Group');
            $vars = [
                    'action_url'           => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=subscribe_add_group_form_submit',
                    'subscribe_group_name' => '',
                    'edit'                 => 0,
            ];
        }
        ee()->view->cp_page_title = $title;
        //ee()->cp->set_variable('cp_page_title', $title);

        ee()->view->cp_breadcrumbs = [
            $this->base                              => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_lists' => lang('Subscribe List'),
        ];
        // ee()->cp->set_variable('cp_breadcrumbs', array(
        //  $this->base => lang('Subscribe'),
  //           $this->base.AMP.'method=subscribe_lists'=>lang('Subscribe List'),
  //           ));
        $vars['fv_url'] = $this->base.AMP.'method=subscribe_add_edit_group_form_validation';

        return ee()->load->view('add_edit_group_form', $vars, true);
    }

    public function subscribe_add_edit_group_form_validation()
    {
        ee()->load->library('form_validation');
        ee()->form_validation->set_rules('subscribe_group_name', 'Group Name', 'required');
        $valid_form = ee()->form_validation->run();
        if ($valid_form) {
            echo 1;
            exit();
        } else {
            echo json_encode(ee()->form_validation->_error_array);
            exit();
        }
    }

    public function subscribe_add_group_form_submit()
    {
        $group = ['groups' => [[
                                            'group_name' => ee()->input->post('subscribe_group_name'),
                                            'group_type' => (ee()->input->post('test_group')) ? 't' : 'g',
                                            ]]];
        $response = ee()->subscribe_model->createSubscribeGroup($group);
        if (isset($response[0]->member_group_id)) {
            ee()->session->set_flashdata('message_success', lang('New Group Created'));
            ee()->functions->redirect($this->base.AMP.'method=subscribe_add_edit_group_form');
        } else {
            ee()->session->set_flashdata('message_failure', lang('Ooops...Something went wrong'));
        }
    }

    public function subscribe_edit_group_form_submit()
    {
        $response = ee()->subscribe_model->editSubscribeGroup(intval(ee()->input->get('id')), ee()->input->post('subscribe_group_name'));
        if ($response && !isset($response->error)) {
            ee()->session->set_flashdata('message_success', lang('Update Successfull !!!'));
        } else {
            ee()->session->set_flashdata('message_failure', lang('Ooops...Something went wrong'));
        }
        ee()->functions->redirect($this->base.AMP.'method=subscribe_add_edit_group_form&id='.ee()->input->get('id'));
    }

    public function subscribe_group_delete_submit()
    {
        $response = ee()->subscribe_model->deleteSubscribeGroup(intval(ee()->input->get('id')));
        if ($response && !isset($response->error)) {
            ee()->session->set_flashdata('message_success', lang('Update Successfull !!!'));
        } else {
            ee()->session->set_flashdata('message_failure', lang('Ooops...Something went wrong'));
        }
        ee()->functions->redirect($this->base.AMP.'method=subscribe_lists');
    }

    public function subscribe_default_group_submit()
    {
        $insert['subscribe_default_group'] = intval(ee()->input->get('id'));
        ee()->config->_update_config($insert);
        ee()->functions->redirect($this->base.AMP.'method=subscribe_lists');
    }

    public function subscribe_group_details()
    {
        ee()->load->library('table');
        $group = $_GET['id'];
        $members = ee()->subscribe_model->group($group);

        echo '<pre>'.__FILE__.'<br>'.__METHOD__.' : '.__LINE__.'<br><br>';
        var_dump($members);
        exit;

        $rows = [];
        $i = 1;
        foreach ($members as $member) {
            $time_date = str_replace('@D:', '', $member->member_since);
            //list($date,$time)=explode('T',$time_date);
            $time_date = str_replace('T', ' ', $time_date);
            $member_since = date('l jS \of F Y h:i:s A', strtotime($time_date));
            $attr = [
            'onclick' => "return confirm('Are you sure to Delete this User?')",
            ];
            $actions = [
              anchor($this->base.AMP.'method=subscribe_edit_user_status_form&id='.$member->member_id.'&group_id='.$group, lang('Edit Status')),
              anchor($this->base.AMP.'method=subscribe_add_edit_user_form&id='.$member->member_id.'&group_id='.$group, lang('Edit Details')),
              anchor($this->base.AMP.'method=subscribe_user_delete_submit&id='.$member->member_id.'&group_id='.$group, lang('Delete'), $attr),
            ];
            $rows[$member->member_id] = [
              $i,
              ((isset($member->fields->first_name)) ? $member->fields->first_name : '').' '.((isset($member->fields->last_name)) ? $member->fields->last_name : ''),
              $member->email,
              $member_since,
              ucfirst($member->status),
              implode(' | ', $actions),
            ];
            $i++;
        }
        $add_group_anchor = anchor($this->base.AMP.'method=subscribe_add_edit_user_form&group_id='.$group, lang('Add a New User'));
        $header = [lang('No:'), lang('Name'), lang('Email'), lang('Member Since'), lang('Status'), lang('Actions')];

        ee()->view->cp_page_title = lang('Subscribe Group Members');
        //ee()->cp->set_variable('cp_page_title', lang('Subscribe Group Members'));

        ee()->view->cp_breadcrumbs = [
            $this->base                              => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_lists' => lang('Subscribe Lists'),
        ];
        // ee()->cp->set_variable('cp_breadcrumbs',
  //               array(
  //                   $this->base => lang('Subscribe'),
  //                   $this->base.AMP.'method=subscribe_lists' => lang('Subscribe Lists'),
  //                   )
  //           );
        $vars = ['rows'                  => $rows,
                      'header'           => $header,
                      'add_group_anchor' => $add_group_anchor,
        ];

        return ee()->load->view('group_details', $vars, true);
    }

    public function subscribe_add_edit_user_form()
    {
        ee()->load->library('table');
        $member_details = [];
        $member_group_ids = [];
        if (isset($_GET['id'])) {
            $member_details = ee()->subscribe_model->get_member_detail($_GET['id']);

            if (isset($member_details->error)) {
                ee()->session->set_flashdata('message_failure', lang($member_details->error));
                ee()->functions->redirect($this->base.AMP);
            }


            $action_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=subscribe_add_user_form_submit&id='.$_GET['id'].'&group_id='.$_GET['group_id'];
            $fv_url = $this->base.AMP.'method=subscribe_add_user_form_validation&id='.$_GET['id'];
            $title = 'Edit a Member';
            $member_groups = (ee()->subscribe_model->getMemberGroups($_GET['id']));
            foreach ($member_groups as $group) {
                $member_group_ids[] = $group->member_group_id;
            }
        } else {
            $action_url = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=subscribe_add_user_form_submit&group_id='.$_GET['group_id'];
            $fv_url = $this->base.AMP.'method=subscribe_add_user_form_validation';
            $title = 'Add a new Member';
        }

        ee()->view->cp_breadcrumbs = [$this->base => lang('Subscribe')];
        // ee()->cp->set_variable('cp_breadcrumbs', array(
        //  $this->base => lang('Subscribe')));

        $fields = ee()->subscribe_model->getFields();
        $groups = ee()->subscribe_model->getGroups();
        foreach ($groups as $group) {
            $groups_list[$group->member_group_id] = $group->group_name;
        }

        ee()->view->cp_page_title = lang($title);
        // ee()->cp->set_variable('cp_page_title', lang($title));

        ee()->view->cp_breadcrumbs = [
            $this->base                                                            => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_lists'                               => lang('Subscribe Lists'),
            $this->base.AMP.'method=subscribe_group_details&id='.$_GET['group_id'] => lang('Subscribe Group Members'),
        ];
        // ee()->cp->set_variable('cp_breadcrumbs',
  //               array(
  //                   $this->base => lang('Subscribe'),
  //                   $this->base.AMP.'method=subscribe_lists' => lang('Subscribe Lists'),
  //                   $this->base.AMP.'method=subscribe_group_details&id='.$_GET['group_id'] => lang('Subscribe Group Members'),
  //                   )
  //           );
        $vars = [
                        'fields'            => $fields,
                        'groups_list'       => $groups_list,
                        'member_details'    => $member_details,
                        'action_url'        => $action_url,
                        'fv_url'            => $fv_url,
                        'member_group_ids'  => $member_group_ids,
        ];

        return ee()->load->view('add_edit_user_form', $vars, true);
    }

    public function subscribe_edit_user_status_form()
    {
        ee()->load->library('table');
        $member_details = ee()->subscribe_model->get_member_detail($_GET['id']);
        $user_status = substr($member_details->status, 0, 1);

        ee()->view->cp_page_title = lang('Subscribe Member Status');
        //ee()->cp->set_variable('cp_page_title', lang('Subscribe Member Status'));

        ee()->view->cp_breadcrumbs = [
            $this->base                                                            => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_lists'                               => lang('Subscribe Lists'),
            $this->base.AMP.'method=subscribe_group_details&id='.$_GET['group_id'] => lang('Subscribe Group Members'),
        ];
        // ee()->cp->set_variable('cp_breadcrumbs',
  //               array(
  //                   $this->base => lang('Subscribe'),
  //                   $this->base.AMP.'method=subscribe_lists' => lang('Subscribe Lists'),
  //                   $this->base.AMP.'method=subscribe_group_details&id='.$_GET['group_id'] => lang('Subscribe Group Members'),
  //                   )
  //           );
        $vars = [
                        'user_status' => $user_status,
                        'action_url'  => 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=emma'.AMP.'method=subscribe_edit_user_status_form_submit&id='.$_GET['id'].'&current_status='.$user_status.'&group_id='.$_GET['group_id'],
        ];

        return ee()->load->view('edit_user_status_form', $vars, true);
    }

    public function subscribe_add_user_form_validation()
    {
        ee()->load->library('form_validation');
        ee()->form_validation->set_rules('subscribe_email', 'Subscribe Email', 'trim|required|valid_email');
        $valid_form = ee()->form_validation->run();
        if ($valid_form) {
            echo 1;
            exit();
        } else {
            echo json_encode(ee()->form_validation->_error_array);
            exit();
        }
    }

    public function subscribe_add_user_form_submit()
    {
        $field_data = [];
        $form_values['values'] = $_POST;
        if (!isset($_GET['id'])) {
            $response = ee()->subscribe_model->get_member_detail_by_email($form_values['values']['subscribe_email']);
            if (isset($response->member_id)) {
                ee()->session->set_flashdata('message_failure', lang('The email already exists in your audience. You can update the information below and save your changes. '));
                ee()->functions->redirect($this->base.AMP.'method=subscribe_add_edit_user_form&id='.$response->member_id.'&group_id='.$_GET['group_id']);
            }
        }
        $form_values_data = [];
        $fields = ee()->subscribe_model->getFields();
        foreach ($fields as $field) {
            if ($field->widget_type == 'check_multiple' || $field->widget_type == 'select multiple') {
                if (isset($form_values['values'][$field->shortcut_name]) && $form_values['values'][$field->shortcut_name]) {
                    foreach ($form_values['values'][$field->shortcut_name] as $key => $val) {
                        if ($val) {
                            $form_values_data['values'][$field->shortcut_name][] = $val;
                        }
                    }
                } else {
                    $form_values_data['values'][$field->shortcut_name] = [];
                }
                if (isset($form_values_data['values'][$field->shortcut_name]) && $form_values_data['values'][$field->shortcut_name]) {
                    $field_data[$field->shortcut_name] = $form_values_data['values'][$field->shortcut_name];
                }
            } elseif ($field->widget_type == 'date') {
                if (isset($form_values['values'][$field->shortcut_name]) && $form_values['values'][$field->shortcut_name] && $form_values['values'][$field->shortcut_name]['year'] && $form_values['values'][$field->shortcut_name]['day'] && $form_values['values'][$field->shortcut_name]['year']) {
                    $field_data[$field->shortcut_name] = '@D:'.$form_values['values'][$field->shortcut_name]['year'].'-'.$form_values['values'][$field->shortcut_name]['month'].'-'.$form_values['values'][$field->shortcut_name]['day'].'T'.date('h:i:s');
                }
            } else {
                $field_data[$field->shortcut_name] = isset($form_values['values'][$field->shortcut_name]) ? $form_values['values'][$field->shortcut_name] : '';
            }
        }
        $groups = [];
        if (isset($form_values['values']['group_list'])) {
            foreach ($form_values['values']['group_list'] as $val) {
                if ($val) {
                    $groups[] = intval($val);
                }
            }
        }
        $success_message = '';
        if (isset($_GET['id'])) {
            ee()->subscribe_model->removeMemberFromAllGroups($_GET['id']);
            $response = ee()->subscribe_model->updateMember($form_values['values']['member_id'], $form_values['values']['subscribe_email'], $form_values['values']['member_status'], $field_data);
            $redirect_url = $this->base.AMP.'method=subscribe_add_edit_user_form&id='.$_GET['id'].'&group_id='.$_GET['group_id'];
            if ($response == 1 && !(isset($response->error))) {
                $add_to_group_response = ee()->subscribe_model->addMemberToGroups($form_values['values']['member_id'], $groups);
                if (!(isset($add_to_group_response->error))) {
                    $success_message = lang('User Successfully Updated');
                }
            }
        } else {
            $redirect_url = $this->base.AMP.'method=subscribe_add_edit_user_form&group_id='.$_GET['group_id'];
            $response = ee()->subscribe_model->createSubscribeUser($form_values['values']['subscribe_email'], $field_data, $groups);
            if (isset($response->member_id)) {
                $success_message = lang('New User Created');
            }
        }
        if ($success_message) {
            ee()->session->set_flashdata('message_success', $success_message);
            ee()->functions->redirect($redirect_url);
        } else {
            ee()->session->set_flashdata('message_failure', lang('Ooops...Something went wrong'));
            ee()->functions->redirect($redirect_url);
        }
    }

    public function subscribe_user_delete_submit()
    {
        $member_ids = [intval(ee()->input->get('id'))];
        if ($member_ids) {
            $response = ee()->subscribe_model->deleteSubscribeUsers($member_ids);
            if (isset($response->member_ids) || $response == 1) {
                ee()->session->set_flashdata('message_success', lang('Delete Successfull'));
            }
        } else {
            ee()->session->set_flashdata('message_failure', lang('Please select a member'));
        }
        ee()->functions->redirect($this->base.AMP.'method=subscribe_group_details&id='.ee()->input->get('group_id'));
    }

    public function subscribe_edit_user_status_form_submit()
    {
        $member_ids = [intval(ee()->input->get('id'))];
        if (ee()->input->get('current_status') != ee()->input->post('subscribe_user_status')) {
            $response = ee()->subscribe_model->updateMembersStatus($member_ids, ee()->input->post('subscribe_user_status'));
            if (!isset($response->error) && $response == 1) {
                ee()->session->set_flashdata('message_success', lang('Status Changed Successfully !!!'));
            } else {
                ee()->session->set_flashdata('message_failure', lang('Sorry, Users who have opted out of your list are uneditable. !!'));
            }
        } else {
            ee()->session->set_flashdata('message_success', lang('Status Changed Successfully !!!'));
        }
        ee()->functions->redirect($this->base.AMP.'method=subscribe_edit_user_status_form&id='.ee()->input->get('id').'&group_id='.$_GET['group_id']);
    }

/*
Code for Subscribe Stats
*/
    public function subscribe_stats()
    {
        if (!ee()->subscribe_model->check()) {
            return ee()->load->view('error', [], true);
        }

        ee()->load->library('table');

        ee()->view->cp_page_title = lang('Mailings');
        // ee()->cp->set_variable('cp_page_title', lang('Mailings'));

        ee()->view->cp_breadcrumbs = [$this->base => lang('Subscribe')];
        // ee()->cp->set_variable('cp_breadcrumbs', array(
        //  $this->base => lang('Subscribe')));
        $mailings = ee()->subscribe_model->getMailingLists();
        $i = 1;
        if ($mailings) {
            foreach ($mailings as $mailing) {
                if ($mailing->send_started) {
                    if ($mailing->send_finished) {
                        $time_date = str_replace('@D:', '', $mailing->send_started);
                    //list($date,$time)=explode('T',$time_date);
                    $time_date = str_replace('T', ' ', $time_date);
                        $send_details = date('l jS \of F Y h:i:s A', strtotime($time_date));
                    //echo $date.'<br>';
                    //echo $time.'<br>';
                    } else {
                        $send_details = lang('In Progress');
                    }
                }
                switch ($mailing->mailing_status) {
                case 'p':$mailing_status = lang('Pending'); break;
                case 'a':$mailing_status = lang('Paused'); break;
                case 's':$mailing_status = lang('In Progress'); break;
                case 'x':$mailing_status = lang('Canceled'); break;
                case 'c':$mailing_status = lang('Complete'); break;
                case 'f':$mailing_status = lang('Failed'); break;
                default:$mailing_status = '--'; break;
            }

                switch ($mailing->mailing_type) {
                case 'm':$mailing_type = lang('Regular'); break;
                case 't':$mailing_type = lang('Test'); break;
                case 'r':$mailing_type = lang('Trigger'); break;
                default:$mailing_type = '--'; break;
            }
                $rows[] = [
              $i,
              $mailing->name,
              $mailing->subject,
              $mailing_status,
              $mailing_type,
              $mailing->recipient_count,
              $send_details,
              anchor($this->base.AMP.'method=mailing_report&id='.$mailing->mailing_id, 'View Details', 'title="Details"'),
            ];
                $i++;
            }
        }
        $vars = ['rows'        => $rows,
                      'header' => [lang('No:'), lang('name'), lang('Mailing Subject'), lang('Status'), lang('Type'), lang('Recipients'), lang('Send At'), lang('Actions')],
        ];
        //var_dump($vars);
        return ee()->load->view('stats', $vars, true);
    }

    public function mailing_report()
    {
        ee()->load->library('table');
       // ee()->load->model('subscribe_model');
        $mailing_id = $_GET['id'];
        $response = ee()->subscribe_model->getMailingDetails($_GET['id']);
        $rows[] = [
          "<span style='float: left;width: 100px;'>".lang('Total Opens')."</span><span style='margin-right:300px'>:&nbsp;&nbsp;&nbsp;".(($response->opened) ? anchor($this->base.AMP.'method=type_details&id='.$mailing_id.'&type=opens', $response->opened, 'title="Details"') : '0').'<span>',
        ];
        $rows[] = [
          "<span style='float: left;width: 100px;'>".lang('Total Clicks')."</span><span style='margin-right:300px'>:&nbsp;&nbsp;&nbsp;".(($response->clicked) ? anchor($this->base.AMP.'method=type_details&id='.$mailing_id.'&type=clicks', $response->clicked, 'title="Details"') : '0').'<span>',
        ];
        $rows[] = [
          "<span style='float: left;width: 100px;'>".lang('Total Shares')."</span><span style='margin-right:300px'>:&nbsp;&nbsp;&nbsp;".(($response->shared) ? anchor($this->base.AMP.'method=type_details&id='.$mailing_id.'&type=shares', $response->shared, 'title="Details"') : '0').'<span>',
        ];
        $rows[] = [
          "<span style='float: left;width: 100px;'>".lang('Total Opt-outs')."</span><span style='margin-right:300px'>:&nbsp;&nbsp;&nbsp;".(($response->opted_out) ? anchor($this->base.AMP.'method=type_details&id='.$mailing_id.'&type=optouts', $response->opted_out, 'title="Details"') : '0').'<span>',
        ];
        $rows[] = [
          "<span style='float: left;width: 100px;'>".lang('Total Sign-Ups')."</span><span style='margin-right:300px'>:&nbsp;&nbsp;&nbsp;".(($response->signed_up) ? anchor($this->base.AMP.'method=type_details&id='.$mailing_id.'&type=signups', $response->signed_up, 'title="Details"') : '0').'<span>',
        ];
        $rows2[] = [
          "<span style='float: left;width: 140px;'>".lang('Total Emails Sent').'</span>:&nbsp;&nbsp;&nbsp;'.$response->sent,
        ];
        $rows2[] = [
          "<span style='float: left;width: 140px;'>".lang('Total Emails Bounced').'</span>:&nbsp;&nbsp;&nbsp;'.$response->bounced,
        ];
        $rows2[] = [
          "<span style='float: left;width: 140px;'>".lang('Total Emails received').'</span>:&nbsp;&nbsp;&nbsp;'.$response->recipient_count,
        ];
        $rows2[] = [
          "<span style='float: left;width: 140px;'>".lang('Total Emails forwarded').'</span>:&nbsp;&nbsp;&nbsp;'.$response->forwarded,
        ];

        $vars = [
            'rows1' => $rows,
            'rows2' => $rows2,
        ];
        ee()->view->cp_page_title = $response->name.' '.lang('Report');
        //ee()->cp->set_variable('cp_page_title', $response->name." ".lang('Report'));
        // ee()->session->set_userdata( array('subscribe_stats_group_name',  $response->name." ".lang('Report')) );

        ee()->view->cp_breadcrumbs = [
            $this->base                              => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_stats' => lang('Mailings'),
        ];
   //      ee()->cp->set_variable('cp_breadcrumbs', array(
            // $this->base => lang('Subscribe'),
   //          $this->base.AMP.'method=subscribe_stats'=> lang('Mailings'),
   //          ));
        return ee()->load->view('mailing_report', $vars, true);
    }

    public function type_details()
    {
        ee()->load->library('table');
        $mailing_id = $_GET['id'];
        $type = $_GET['type'];
        $response = ee()->subscribe_model->getTypeDetails($mailing_id, $type);
        $extra_info = [];
        $rows = [];
        switch ($type) {
            case 'signups':
            case 'optouts':
            case 'opens':
                        $title_page = $title = ucfirst($type).' Report';
                        $header = [lang('No:'), lang('Name'), lang('Email'), lang('When')];
                        $i = 1;
                        foreach ($response as $info) {
                            $timestamp = str_replace('@D:', '', $info->timestamp);
                            $timestamp = str_replace('T', ' ', $timestamp);
                            $timestamp = date('l jS \of F Y h:i:s A', strtotime($timestamp));
                            $user_details = ee()->subscribe_model->get_member_detail($info->member_id);
                            $rows[] = [
                              $i,
                              ((isset($user_details->fields->first_name)) ? $user_details->fields->first_name : '').' '.((isset($user_details->fields->last_name)) ? $user_details->fields->last_name : ''),
                              $info->email,
                              $timestamp,
                            ];
                            $i++;
                        }
                        break;
            case 'clicks':
                        $title_page = lang('Clicks Report');
                        $title = lang('Links Details');
                        $header = [lang('No:'), lang('Name'), lang('Target'), lang('Unique Clicks'), lang('Total Clicks')];
                        $links = ee()->subscribe_model->get_links($mailing_id);
                        $links_row = [];
                        foreach ($links as $link) {
                            $i = 1;
                            $links_row[$link->link_id] = [
                              $i,
                              'link_name'   => $link->link_name,
                              'link_target' => $link->link_target,
                              $link->unique_clicks,
                              $link->total_clicks,
                            ];
                            $i++;
                        }
                        $extra_info['title'] = 'Clicks Report';
                        $extra_info['header'] = [lang('No:'), lang('Name'), lang('Email'), lang('When'), lang('Url Name'), lang('Url Target')];
                        $i = 1;
                        foreach ($response as $info) {
                            $timestamp = str_replace('@D:', '', $info->timestamp);
                            $timestamp = str_replace('T', ' ', $timestamp);
                            $timestamp = date('l jS \of F Y h:i:s A', strtotime($timestamp));
                            $user_details = ee()->subscribe_model->get_member_detail($info->member_id);
                            $rows[] = [
                              $i,
                              ((isset($user_details->fields->first_name)) ? $user_details->fields->first_name : '').' '.((isset($user_details->fields->last_name)) ? $user_details->fields->last_name : ''),
                              $info->email,
                              $timestamp,
                              $links_row[$info->link_id]['link_name'],
                              $links_row[$info->link_id]['link_target'],
                            ];
                            $i++;
                        }
                        $extra_info['rows'] = $rows;
                        $rows = [];
                        $rows = $links_row;
                        break;

            case 'shares':
                        $title_page = $title = lang('Shares Report');
                        $header = [lang('No:'), lang('Name'), lang('Email'), lang('When'), lang('Network'), lang('Visits from Network')];
                        $i = 1;
                        foreach ($response as $info) {
                            $timestamp = str_replace('@D:', '', $info->timestamp);
                            $timestamp = str_replace('T', ' ', $timestamp);
                            $timestamp = date('l jS \of F Y h:i:s A', strtotime($timestamp));
                            $user_details = ee()->subscribe_model->get_member_detail($info->member_id);
                            $rows[] = [
                              $i,
                              ((isset($user_details->fields->first_name)) ? $user_details->fields->first_name : '').' '.((isset($user_details->fields->last_name)) ? $user_details->fields->last_name : ''),
                              $info->email,
                              $timestamp,
                              ucfirst($info->network),
                              $info->share_clicks,
                            ];
                            $i++;
                        }
                        break;

        }
        $vars = [
            'rows'       => $rows,
            'header'     => $header,
            'title'      => $title,
            'extra_info' => $extra_info,
        ];

        ee()->view->cp_page_title = $title_page;
        //ee()->cp->set_variable('cp_page_title', $title_page);

        ee()->view->cp_breadcrumbs = [
            $this->base                                             => lang('Subscribe'),
            $this->base.AMP.'method=subscribe_stats'                => lang('Mailings'),
            $this->base.AMP.'method=mailing_report&id='.$mailing_id => lang('Mailing Report'),
        ];
   //      ee()->cp->set_variable('cp_breadcrumbs', array(
            // $this->base => lang('Subscribe'),
   //          $this->base.AMP.'method=subscribe_stats'=> lang('Mailings'),
   //          $this->base.AMP.'method=mailing_report&id='.$mailing_id=> lang('Mailing Report'),
   //          ));
        return ee()->load->view('type_details', $vars, true);
    }
}

/* End of file mcp.emma.php */
/* Location: ./system/expressionengine/third_party/emma/mcp.emma.php */
/* Generated by DevKit for EE - develop addons faster! */
