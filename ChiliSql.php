<?php

class ChiliSql extends PDO
{
    public function __construct()
    {
        parent::__construct(
            'mysql:host=localhost;dbname=mancala;charset=utf8',
            'mancalauser',
            'password69'
        );
        $this->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }
    public function query( string $sql ) : array
    {
        return parent::query( $sql )->fetchAll();
    }
}

?>