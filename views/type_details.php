<?php

$this->table->set_template($cp_pad_table_template);
if ($extra_info) {
    ?>
<h3><?php echo $title?></h3>
<?php

}
$this->table->set_heading($header);

foreach ($rows as $row) {
    $this->table->add_row($row);
}

echo $this->table->generate();

if ($extra_info) {
    echo '<h3>'.$extra_info['title'].'</h3>';
    $this->table->set_heading($extra_info['header']);

    foreach ($extra_info['rows'] as $row) {
        $this->table->add_row($row);
    }
    echo $this->table->generate();
}
