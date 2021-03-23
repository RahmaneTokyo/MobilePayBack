<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210322113141 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE depot (id INT AUTO_INCREMENT NOT NULL, montant_depot VARCHAR(255) DEFAULT NULL, date_depot DATE DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depot_caissier (depot_id INT NOT NULL, caissier_id INT NOT NULL, INDEX IDX_98180C5C8510D4DE (depot_id), INDEX IDX_98180C5CB514973B (caissier_id), PRIMARY KEY(depot_id, caissier_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depot_compte (depot_id INT NOT NULL, compte_id INT NOT NULL, INDEX IDX_872295788510D4DE (depot_id), INDEX IDX_87229578F2C56620 (compte_id), PRIMARY KEY(depot_id, compte_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE depot_caissier ADD CONSTRAINT FK_98180C5C8510D4DE FOREIGN KEY (depot_id) REFERENCES depot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE depot_caissier ADD CONSTRAINT FK_98180C5CB514973B FOREIGN KEY (caissier_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE depot_compte ADD CONSTRAINT FK_872295788510D4DE FOREIGN KEY (depot_id) REFERENCES depot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE depot_compte ADD CONSTRAINT FK_87229578F2C56620 FOREIGN KEY (compte_id) REFERENCES compte (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE depot_caissier DROP FOREIGN KEY FK_98180C5C8510D4DE');
        $this->addSql('ALTER TABLE depot_compte DROP FOREIGN KEY FK_872295788510D4DE');
        $this->addSql('DROP TABLE depot');
        $this->addSql('DROP TABLE depot_caissier');
        $this->addSql('DROP TABLE depot_compte');
    }
}
