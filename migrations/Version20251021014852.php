<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251021014852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios DROP CONSTRAINT fk_5d899b41537b6a5e');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios DROP CONSTRAINT fk_5d899b41f01d3b25');
        $this->addSql('DROP TABLE recuerdo_multimedia_usuarios');
        $this->addSql('ALTER TABLE gallery.recuerdo_multimedia ADD usuario_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE gallery.recuerdo_multimedia ADD CONSTRAINT FK_167F90DB38439E FOREIGN KEY (usuario_id) REFERENCES login.usuarios (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_167F90DB38439E ON gallery.recuerdo_multimedia (usuario_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE recuerdo_multimedia_usuarios (recuerdo_multimedia_id INT NOT NULL, usuarios_id INT NOT NULL, PRIMARY KEY(recuerdo_multimedia_id, usuarios_id))');
        $this->addSql('CREATE INDEX idx_5d899b41537b6a5e ON recuerdo_multimedia_usuarios (recuerdo_multimedia_id)');
        $this->addSql('CREATE INDEX idx_5d899b41f01d3b25 ON recuerdo_multimedia_usuarios (usuarios_id)');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios ADD CONSTRAINT fk_5d899b41537b6a5e FOREIGN KEY (recuerdo_multimedia_id) REFERENCES gallery.recuerdo_multimedia (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recuerdo_multimedia_usuarios ADD CONSTRAINT fk_5d899b41f01d3b25 FOREIGN KEY (usuarios_id) REFERENCES login.usuarios (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gallery.recuerdo_multimedia DROP CONSTRAINT FK_167F90DB38439E');
        $this->addSql('DROP INDEX gallery.IDX_167F90DB38439E');
        $this->addSql('ALTER TABLE gallery.recuerdo_multimedia DROP usuario_id');
    }
}
