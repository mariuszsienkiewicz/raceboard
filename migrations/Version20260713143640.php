<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260713143640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE race_catalog_races ADD average_rating DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE user_profile_users ALTER display_name DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE race_catalog_races DROP average_rating');
        $this->addSql('ALTER TABLE user_profile_users ALTER display_name SET DEFAULT \'\'');
    }
}
