<h3><?php echo $add_group_anchor; ?></h3>
<?php
if ($rows) {
    $this->table->set_template($cp_pad_table_template);

    $this->table->set_heading($header);

    foreach ($rows as $row) {
        $this->table->add_row($row);
    }


    echo $this->table->generate();
} else {
    ?>
    <strong> No results found </strong>    
<?php
} ?>