<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240829085748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE profesional (id INT AUTO_INCREMENT NOT NULL, nif VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', nombre VARCHAR(255) NOT NULL, ape1 VARCHAR(255) NOT NULL, ape2 VARCHAR(255) DEFAULT NULL, sexo VARCHAR(50) DEFAULT NULL, numero_colegiado VARCHAR(10) DEFAULT NULL, fecha_nacimiento DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', telefono VARCHAR(15) DEFAULT NULL, telefono2 VARCHAR(15) DEFAULT NULL, telefono3 VARCHAR(15) DEFAULT NULL, telefono4 VARCHAR(15) DEFAULT NULL, direccion VARCHAR(255) DEFAULT NULL, poblacion VARCHAR(255) DEFAULT NULL, cp VARCHAR(8) DEFAULT NULL, provincia VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, localidad_nacimiento VARCHAR(255) DEFAULT NULL, nss VARCHAR(25) DEFAULT NULL, nombre_padre VARCHAR(255) DEFAULT NULL, nombre_madre VARCHAR(255) DEFAULT NULL, nacionalidad VARCHAR(255) DEFAULT NULL, comunidad_autonoma VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_NIF (nif), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE profesional');
    }
}
