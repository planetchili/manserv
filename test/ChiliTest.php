<?php
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class ChiliDatabaseTest extends TestCase
{
    use TestCaseTrait;

    /** @var string */
    protected const USER = 'testuser';
    /** @var string */
    protected const SCHEMA = 'testschema';
    /** @var string */
    protected const PASSWORD = 'password';
    /** @var ChiliSql */
    static protected $pdo = null;
    /** @var PHPUnit_Extensions_Database_DB_IDatabaseConnection */
    private $conn = null;

    final public function getConnection()
    {
        if( $this->conn === null )
        {
            if( self::$pdo == null )
            {
                self::$pdo = new ChiliSql( self::SCHEMA,self::USER,self::PASSWORD );
            }
            $this->conn = $this->createDefaultDBConnection( self::$pdo,self::SCHEMA );
        }

        return $this->conn;
    }
}
?>