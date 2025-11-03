<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251102210057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios DROP CONSTRAINT fk_69c26ac203b7e4e');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios DROP CONSTRAINT fk_69c26acf01d3b25');
        $this->addSql('DROP TABLE interaccion_recuerdo_usuarios');
        $this->addSql('ALTER TABLE interaccion_recuerdo ADD usuario_id INT NOT NULL');
        $this->addSql('ALTER TABLE interaccion_recuerdo ADD CONSTRAINT FK_993BE693DB38439E FOREIGN KEY (usuario_id) REFERENCES login.usuarios (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_993BE693DB38439E ON interaccion_recuerdo (usuario_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE interaccion_recuerdo_usuarios (interaccion_recuerdo_id INT NOT NULL, usuarios_id INT NOT NULL, PRIMARY KEY(interaccion_recuerdo_id, usuarios_id))');
        $this->addSql('CREATE INDEX idx_69c26ac203b7e4e ON interaccion_recuerdo_usuarios (interaccion_recuerdo_id)');
        $this->addSql('CREATE INDEX idx_69c26acf01d3b25 ON interaccion_recuerdo_usuarios (usuarios_id)');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios ADD CONSTRAINT fk_69c26ac203b7e4e FOREIGN KEY (interaccion_recuerdo_id) REFERENCES interaccion_recuerdo (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios ADD CONSTRAINT fk_69c26acf01d3b25 FOREIGN KEY (usuarios_id) REFERENCES login.usuarios (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interaccion_recuerdo DROP CONSTRAINT FK_993BE693DB38439E');
        $this->addSql('DROP INDEX IDX_993BE693DB38439E');
        $this->addSql('ALTER TABLE interaccion_recuerdo DROP usuario_id');
    }
}
