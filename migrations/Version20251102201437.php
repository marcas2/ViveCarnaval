<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251102201437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE interaccion_recuerdo (id SERIAL NOT NULL, recuerdo_id INT DEFAULT NULL, tipo TEXT NOT NULL, contenido_comentario TEXT DEFAULT NULL, fecha TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_993BE69372CA520D ON interaccion_recuerdo (recuerdo_id)');
        $this->addSql('CREATE TABLE interaccion_recuerdo_usuarios (interaccion_recuerdo_id INT NOT NULL, usuarios_id INT NOT NULL, PRIMARY KEY(interaccion_recuerdo_id, usuarios_id))');
        $this->addSql('CREATE INDEX IDX_69C26AC203B7E4E ON interaccion_recuerdo_usuarios (interaccion_recuerdo_id)');
        $this->addSql('CREATE INDEX IDX_69C26ACF01D3B25 ON interaccion_recuerdo_usuarios (usuarios_id)');
        $this->addSql('ALTER TABLE interaccion_recuerdo ADD CONSTRAINT FK_993BE69372CA520D FOREIGN KEY (recuerdo_id) REFERENCES gallery.recuerdo_multimedia (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios ADD CONSTRAINT FK_69C26AC203B7E4E FOREIGN KEY (interaccion_recuerdo_id) REFERENCES interaccion_recuerdo (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios ADD CONSTRAINT FK_69C26ACF01D3B25 FOREIGN KEY (usuarios_id) REFERENCES login.usuarios (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE interaccion_recuerdo DROP CONSTRAINT FK_993BE69372CA520D');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios DROP CONSTRAINT FK_69C26AC203B7E4E');
        $this->addSql('ALTER TABLE interaccion_recuerdo_usuarios DROP CONSTRAINT FK_69C26ACF01D3B25');
        $this->addSql('DROP TABLE interaccion_recuerdo');
        $this->addSql('DROP TABLE interaccion_recuerdo_usuarios');
    }
}
