<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908223722 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA login');
        $this->addSql('CREATE TABLE login.roles (id SERIAL NOT NULL, rol TEXT DEFAULT NULL, descripcion TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE login.usuarios (id SERIAL NOT NULL, rol_id INT NOT NULL, nombre TEXT NOT NULL, correo TEXT NOT NULL, contrasena TEXT NOT NULL, foto TEXT DEFAULT NULL, biografia TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C78A70FB4BAB96C ON login.usuarios (rol_id)');
        $this->addSql('ALTER TABLE login.usuarios ADD CONSTRAINT FK_C78A70FB4BAB96C FOREIGN KEY (rol_id) REFERENCES login.roles (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE login.usuarios DROP CONSTRAINT FK_C78A70FB4BAB96C');
        $this->addSql('DROP TABLE login.roles');
        $this->addSql('DROP TABLE login.usuarios');
    }
}
