<?php

class ChiliSql extends PDO
{
    public function __construct( string $schema,string $user,string $password )
    {
        parent::__construct(
            'mysql:host=localhost;dbname='.$schema.';charset=utf8',
            $user,
            $password
        );
        $this->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );
    }
    public function qfetch( string $sql ) : array
    {
        return $this->query( $sql )->fetchAll();
    }
}
?>