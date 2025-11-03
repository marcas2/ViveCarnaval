<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251020003916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA gallery');
        $this->addSql('CREATE TABLE gallery.recuerdo_multimedia (id SERIAL NOT NULL, tipo TEXT DEFAULT NULL, titulo TEXT DEFAULT NULL, descripcion TEXT DEFAULT NULL, estado TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE recuerdo_multimedia_usuarios (recuerdo_multimedia_id INT NOT NULL, usuarios_id INT NOT NULL, PRIMARY KEY(recuerdo_multimedia_id, usuarios_id))');
        $this->addSql('CREATE INDEX IDX_5D899B41537B6A5E ON recuerdo_multimedia_usuarios (recuerdo_multimedia_id)');
        $this->addSql('CREATE INDEX IDX_5D899B41F01D3B25 ON recuerdo_multimedia_usuarios (usuarios_id)');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios ADD CONSTRAINT FK_5D899B41537B6A5E FOREIGN KEY (recuerdo_multimedia_id) REFERENCES gallery.recuerdo_multimedia (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios ADD CONSTRAINT FK_5D899B41F01D3B25 FOREIGN KEY (usuarios_id) REFERENCES login.usuarios (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios DROP CONSTRAINT FK_5D899B41537B6A5E');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios DROP CONSTRAINT FK_5D899B41F01D3B25');
        $this->addSql('DROP TABLE gallery.recuerdo_multimedia');
        $this->addSql('DROP TABLE recuerdo_multimedia_usuarios');
    }
}
