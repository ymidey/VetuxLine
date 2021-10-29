<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211025150215 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customers (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, gender VARCHAR(10) NOT NULL, title VARCHAR(10) NOT NULL, surname VARCHAR(50) NOT NULL, given_name VARCHAR(50) NOT NULL, email_address LONGTEXT NOT NULL, birthday VARCHAR(50) NOT NULL, telephone_number VARCHAR(50) NOT NULL, cc_type VARCHAR(30) NOT NULL, cc_number VARCHAR(100) NOT NULL, cvv2 INT NOT NULL, cc_expires VARCHAR(50) NOT NULL, street_address LONGTEXT NOT NULL, city VARCHAR(255) NOT NULL, zip_code INT NOT NULL, country_full VARCHAR(50) NOT NULL, centimeters DOUBLE PRECISION NOT NULL, kilograms DOUBLE PRECISION NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, INDEX IDX_62534E21545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE marks (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicles (id INT AUTO_INCREMENT NOT NULL, mark_id INT DEFAULT NULL, model VARCHAR(255) NOT NULL, year INT NOT NULL, INDEX IDX_1FCE69FA4290F12B (mark_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE customers ADD CONSTRAINT FK_62534E21545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE vehicles ADD CONSTRAINT FK_1FCE69FA4290F12B FOREIGN KEY (mark_id) REFERENCES marks (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vehicles DROP FOREIGN KEY FK_1FCE69FA4290F12B');
        $this->addSql('ALTER TABLE customers DROP FOREIGN KEY FK_62534E21545317D1');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE marks');
        $this->addSql('DROP TABLE vehicles');
    }
}