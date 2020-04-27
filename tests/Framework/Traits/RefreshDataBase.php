<?php

namespace App\Tests\Framework\Traits;

use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;

trait RefreshDataBase
{
    /**
     * vide la base et crée les tables avec les metadatas
     * @throws ToolsException
     */
    public function refreshDataBase(): void
    {
        static $metadata = null;

        if (is_null($metadata)) {
            $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();

        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }
}