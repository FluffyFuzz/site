<?php

namespace model;

use JsonSerializable;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/Member.php';
require_once __DIR__ . '/File.php';

class News extends BaseModel implements JsonSerializable
{
    public static function create(string $nom, string $description, string $date, int $id_membre, File | null $image): News
    {
        $DB = new \DB();
        $imageFileName = $image ? $image->getFileName() : null;
        $id = $DB->query("INSERT INTO ACTUALITE (titre_actualite, contenu_actualite, date_actualite, id_membre, image_actualite) VALUES (?, ?, ?, ?, ?)", "sssis", [$nom, $description, $date, $id_membre, $imageFileName]);
        return News::getInstance($id);
    }

    public function update(string $nom, string $description, string $date, int $id_membre): News
    {
        $this->DB->query("UPDATE ACTUALITE SET titre_actualite = ?, contenu_actualite = ?, date_actualite = ?, id_membre = ? WHERE id_actualite = ?", "sssii", [$nom, $description, $date, $id_membre, $this->id]);

        return $this;
    }

    public function updateImage(File $image): News
    {
        $this->DB->query("UPDATE ACTUALITE SET image_actualite = ? WHERE id_actualite = ?", "si", [$image->getFileName(), $this->id]);

        return $this;
    }

    public function delete(): void
    {
        $this->DB->query("DELETE FROM ACTUALITE WHERE id_actualite = ?", "i", [$this->id]);
    }

    public static function getInstance(int $id): News | null
    {
        $DB = new \DB();
        $result = $DB->select("SELECT * FROM ACTUALITE WHERE id_actualite = ?", "i", [$id]);

        if (count($result) == 0) {
            return null;
        }

        return new News($id);
    }

    public static function bulkFetch(): array
    {
        $DB = new \DB();
        return $DB->select("SELECT * FROM ACTUALITE");
    }


    public function jsonSerialize(): array
    {
        $data = $this->DB->select("SELECT A.*, M.prenom_membre, M.prenom_membre FROM ACTUALITE as A INNER JOIN MEMBRE M on A.id_membre = M.id_membre WHERE id_actualite = ?", "i", [$this->id]);

        return $data[0];
    }

    public function __toString(): string
    {
        return json_encode($this);
    }
}
