<?php

function generateUUID()
{
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return bin2hex($data);
}

function saveFile(): string | null
{
        // Retourne le nom du fichier si l'enregistrement a réussi, faux sinon.

        $name = generateUUID() . '.' . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

    if (move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/api/files/' . $name)) {
        return $name;
    }

        return null;
}

function saveImage(): string | null
{
        // Vérification des données de l'image, puis enregistrement.
        // Retourne Faux si l'image n'en est pas une, ou si elle n'a pas pu être enregistrée.

    if (!isset($_FILES['file']) || $_FILES['file']['tmp_name'] === '') {
        return null;
    }

        // Vérifie le type MIME avec finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['file']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
    if (!in_array($mimeType, $allowedTypes)) {
        return null;
    }

        // On s'assure que l'extension du fichier ne causerait pas de problèmes
        return saveFile();
}

function deleteFile(string $fileName): bool
{
    if (file_exists(__DIR__ . "/api/files/" . $fileName)) {
        unlink(__DIR__ . "/api/files/" . $fileName);
        return true;
    }

        return false;
}
