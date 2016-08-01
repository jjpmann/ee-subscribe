<?php

$this->table->set_template($cp_pad_table_template);

$this->table->set_heading($header);

foreach ($rows as $row) {
    $this->table->add_row($row);
}


echo $this->table->generate();
/*   */

 /*lang('No:'),
    lang('name'),
    lang('mailing_subject'),
    lang('status'),
    lang('type'),
    lang('recipients'),
    lang('send'),
    lang('actions'),*/
