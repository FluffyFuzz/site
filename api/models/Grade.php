<?php

namespace model;

use JsonSerializable;

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/File.php';


class Grade extends BaseModel implements JsonSerializable
{
    public static function create(string $name, string $description, float $price, File | null $image, float $reduction): Grade
    {
        $DB = new \DB();

        $id = $DB->query("INSERT INTO GRADE (nom_grade, description_grade, prix_grade, image_grade, reduction_grade)
                    VALUES (?, ?, ?, ?, ?)", "ssdsd", [$name, $description, $price, $image, $reduction]);

        return new Grade($id);
    }

    public function update(string $name, string $description, float $price, float $reduction): Grade
    {
        $this->DB->query("UPDATE GRADE SET nom_grade = ?, description_grade = ?, prix_grade = ?, reduction_grade = ? WHERE id_grade = ?", "ssdsi", [$name, $description, $price, $reduction, $this->id]);

        return $this;
    }

    public function getImage(): File | null
    {
        $image = $this->DB->select("SELECT image_grade FROM GRADE WHERE id_grade = ?", "i", [$this->id])[0]['image_grade'];
        return File::getFile($image);
    }

    public function updateImage(File $image): Grade
    {
        $this->DB->query("UPDATE GRADE SET image_grade = ? WHERE id_grade = ?", "si", [$image->getFileName(), $this->id]);

        return $this;
    }

    public function delete(): void
    {
        $this->getImage()?->deleteFile();
        $this->DB->query("UPDATE GRADE SET deleted=true WHERE id_grade = ?", "i", [$this->id]);
    }

    public static function getInstance($id): Grade | null
    {
        $DB = new \DB();
        $result = $DB->select("SELECT * FROM GRADE WHERE id_grade = ? AND deleted=false", "i", [$id]);

        if (count($result) == 0) {
            return null;
        }

        return new Grade($id);
    }

    public static function bulkFetch(): array
    {
        $DB = new \DB();
        return $DB->select("SELECT * FROM GRADE WHERE deleted=false");
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    public function jsonSerialize(): array
    {
        return $this->DB->select("SELECT * FROM GRADE WHERE id_grade = ?", "i", [$this->id])[0];
    }
}
