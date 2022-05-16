<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220516191834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE utilisateur_id_seq CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ALTER user_id TYPE UUID');
        $this->addSql('ALTER TABLE reset_password_request ALTER user_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN reset_password_request.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE utilisateur ADD prenom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD nom VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD avatar VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE utilisateur ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN utilisateur.id IS \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE utilisateur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('ALTER TABLE utilisateur DROP prenom');
        $this->addSql('ALTER TABLE utilisateur DROP nom');
        $this->addSql('ALTER TABLE utilisateur DROP avatar');
        $this->addSql('ALTER TABLE utilisateur ALTER id TYPE INT');
        $this->addSql('ALTER TABLE utilisateur ALTER id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN utilisateur.id IS NULL');
        $this->addSql('ALTER TABLE reset_password_request ALTER user_id TYPE INT');
        $this->addSql('ALTER TABLE reset_password_request ALTER user_id DROP DEFAULT');
        $this->addSql('COMMENT ON COLUMN reset_password_request.user_id IS NULL');
    }
}
