<?php
$this->table->set_template($cp_pad_table_template);

$this->table->set_heading(array('Details'));

foreach($rows1 as $row1)
{
    $this->table->add_row($row1);
}

echo $this->table->generate();

$this->table->set_heading(array('The Send Off'));

foreach($rows2 as $row2)
{
    $this->table->add_row($row2);
}
echo $this->table->generate();

