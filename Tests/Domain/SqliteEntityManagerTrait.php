<?php
namespace Tests\Domain;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;

trait SqliteEntityManagerTrait
{
    private function createEntityManager(): EntityManager
    {
        if (!extension_loaded("pdo_sqlite")) {
            $this->markTestSkipped("pdo_sqlite is not available.");
        }

        $oConfig = ORMSetup::createAttributeMetadataConfiguration(
            [
                dirname(__DIR__, 2) . "/Domain/Sequence/Entity",
                dirname(__DIR__, 2) . "/Domain/Database/Entity",
            ],
            true
        );
        $oConfig->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER, true));
        $oConnection = DriverManager::getConnection(["driver" => "pdo_sqlite", "memory" => true], $oConfig);

        return new EntityManager($oConnection, $oConfig);
    }

    private function createEntityManagerWithSchema(): EntityManager
    {
        $oEm = $this->createEntityManager();
        (new SchemaTool($oEm))->createSchema($oEm->getMetadataFactory()->getAllMetadata());

        return $oEm;
    }
}
