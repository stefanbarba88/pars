<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230320110004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cities (id INT AUTO_INCREMENT NOT NULL, drzava_id INT DEFAULT NULL, edit_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, ptt INT NOT NULL, region VARCHAR(255) NOT NULL, municipality VARCHAR(255) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_D95DB16BEE4B985A (drzava_id), INDEX IDX_D95DB16B93555579 (edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clients (id INT AUTO_INCREMENT NOT NULL, grad_id INT DEFAULT NULL, kontakt_id INT DEFAULT NULL, edit_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, adresa VARCHAR(255) DEFAULT NULL, telefon1 VARCHAR(255) DEFAULT NULL, telefon2 VARCHAR(255) DEFAULT NULL, pib VARCHAR(9) NOT NULL, is_suspended TINYINT(1) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C82E741C812354 (grad_id), UNIQUE INDEX UNIQ_C82E74C4062E7F (kontakt_id), INDEX IDX_C82E7493555579 (edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE countries (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, title_en VARCHAR(255) DEFAULT NULL, short2 VARCHAR(2) DEFAULT NULL, short3 VARCHAR(3) DEFAULT NULL, num VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, client_id INT DEFAULT NULL, original LONGTEXT DEFAULT NULL, thumbnail_100x100 LONGTEXT DEFAULT NULL, thumbnail_500x500 LONGTEXT DEFAULT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E01FBE6AA76ED395 (user_id), INDEX IDX_E01FBE6A19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE positions (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, title_short VARCHAR(255) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, grad_id INT DEFAULT NULL, pozicija_id INT DEFAULT NULL, edit_by_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, ime VARCHAR(255) NOT NULL, prezime VARCHAR(255) NOT NULL, jmbg VARCHAR(13) DEFAULT NULL, adresa VARCHAR(255) DEFAULT NULL, telefon1 VARCHAR(255) DEFAULT NULL, telefon2 VARCHAR(255) DEFAULT NULL, pol SMALLINT NOT NULL, vrsta_zaposlenja INT DEFAULT NULL, datum_rodjenja DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', is_suspended TINYINT(1) NOT NULL, user_type SMALLINT NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, created DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E91C812354 (grad_id), INDEX IDX_1483A5E943DDA57B (pozicija_id), INDEX IDX_1483A5E993555579 (edit_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cities ADD CONSTRAINT FK_D95DB16BEE4B985A FOREIGN KEY (drzava_id) REFERENCES countries (id)');
        $this->addSql('ALTER TABLE cities ADD CONSTRAINT FK_D95DB16B93555579 FOREIGN KEY (edit_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E741C812354 FOREIGN KEY (grad_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E74C4062E7F FOREIGN KEY (kontakt_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE clients ADD CONSTRAINT FK_C82E7493555579 FOREIGN KEY (edit_by_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A19EB6921 FOREIGN KEY (client_id) REFERENCES clients (id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E91C812354 FOREIGN KEY (grad_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E943DDA57B FOREIGN KEY (pozicija_id) REFERENCES positions (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E993555579 FOREIGN KEY (edit_by_id) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16BEE4B985A');
        $this->addSql('ALTER TABLE cities DROP FOREIGN KEY FK_D95DB16B93555579');
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E741C812354');
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E74C4062E7F');
        $this->addSql('ALTER TABLE clients DROP FOREIGN KEY FK_C82E7493555579');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AA76ED395');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A19EB6921');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E91C812354');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E943DDA57B');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E993555579');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE countries');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE positions');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE users');
    }
}
