<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class UrlType extends Type
{
    public function getName()
    {
        return 'url';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value !== null ? (string) $value : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return $value !== null ? (string) $value : null;
    }

    public function getMappedDatabaseTypes(AbstractPlatform $platform)
    {
        return array('url');
    }
}