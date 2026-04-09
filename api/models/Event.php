<?php

namespace model;

use JsonSerializable;

require_once __DIR__ . '/File.php';
require_once __DIR__ . '/BaseModel.php';

class Event extends BaseModel implements JsonSerializable
{

    public function delete() : void
    {
        $this->DB->query("UPDATE EVENEMENT SET deleted=true WHERE id_evenement = ?", "i", [$this->id]);
    }

    public function update(string $nom, string $description, int $xp, int $places, bool $reductions, float $prix, string $lieu, string $date) : Event
    {
        $this->DB->query("UPDATE EVENEMENT SET nom_evenement = ?, xp_evenement = ?, places_evenement = ?, reductions_evenement = ?, prix_evenement = ?, lieu_evenement = ?, date_evenement = ?, description_evenement = ? WHERE id_evenement = ?", "siiidsssi", [$nom, $xp, $places, (int)$reductions, $prix, $lieu, $date, $description, $this->id]);

        return $this;
    }

    public function getImage() : File | null
    {
        $image = $this->DB->select("SELECT image_evenement FROM EVENEMENT WHERE id_evenement = ?", "i", [$this->id])[0]['image_evenement'];
        return File::getFile($image);
    }

    public function updateImage(File $image) : Event
    {
        $this->DB->query("UPDATE EVENEMENT SET image_evenement = ? WHERE id_evenement = ?", "si", [$image->getFileName(), $this->id]);

        return $this;
    }

    public static function getInstance(int $id): ?Event
    {
        $DB = new \DB();
        $sql = "SELECT * FROM EVENEMENT WHERE id_evenement = ? AND deleted=false";
        $event = $DB->select($sql, "i", [$id]);

        if (count($event) == 0) {
            return null;
        }

        return new Event($id);
    }

    public static function create(string $nom, string $description, int $xp, int $places, bool $reductions, float $prix, string $lieu, string $date) : Event
    {
        $DB = new \DB();
        $id = $DB->query("INSERT INTO EVENEMENT (nom_evenement, xp_evenement, places_evenement, reductions_evenement, prix_evenement, lieu_evenement, date_evenement, description_evenement)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)", "siiidsss", [$nom, $xp, $places, (int)$reductions, $prix, $lieu, $date, $description]);

        return new Event($id);
    }


    public static function bulkFetch() : array
    {
        $DB = new \DB();
        $sql = "SELECT * FROM EVENEMENT WHERE deleted=false";
        return $DB->select($sql);
    }

    public function jsonSerialize(): array
    {
        return $this->DB->select("SELECT * FROM EVENEMENT WHERE id_evenement = ?", "i", [$this->id])[0];

    }

    public function __toString() : string
    {
        return json_encode($this);
    }
}