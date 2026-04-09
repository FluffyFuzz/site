<?php

namespace model;

class BaseModel
{
    protected int $id;
    protected \DB $DB;


    public function getId(): int
    {
        return $this->id;
    }

    protected function __construct($id)
    {
        $this->id = $id;
        $this->DB = new \DB();
    }
}
